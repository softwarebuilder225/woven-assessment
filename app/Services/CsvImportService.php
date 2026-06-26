<?php

namespace App\Services;

use App\Exceptions\InvalidCsvException;
use App\Models\Investment;
use App\Models\Investor;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CsvImportService
{
    private const CHUNK_SIZE = 500;

    public function import(UploadedFile $file): array
    {
        DB::connection()->disableQueryLog();

        $handle = fopen($file->path(), 'r');

        if ($handle === false) {
            throw new InvalidCsvException('Could not open the uploaded file.');
        }

        try {
            $headers = $this->readHeaders($handle);

            $investorsProcessed = 0;
            $investmentsProcessed = 0;
            $chunk = [];

            while (($row = fgetcsv($handle)) !== false) {
                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $chunk[] = $this->mapRow($headers, $row);

                if (count($chunk) >= self::CHUNK_SIZE) {
                    [$investorCount, $investmentCount] = $this->storeChunk($chunk);
                    $investorsProcessed += $investorCount;
                    $investmentsProcessed += $investmentCount;
                    unset($chunk);
                    $chunk = [];
                }
            }

            if ($chunk !== []) {
                [$investorCount, $investmentCount] = $this->storeChunk($chunk);
                $investorsProcessed += $investorCount;
                $investmentsProcessed += $investmentCount;
            }

            return [
                'investors_processed' => $investorsProcessed,
                'investments_processed' => $investmentsProcessed,
            ];
        } finally {
            fclose($handle);
        }
    }

    private function readHeaders($handle): array
    {
        $headers = fgetcsv($handle);

        if ($headers === false) {
            throw new InvalidCsvException('The CSV file is empty.');
        }

        $headers = array_map('trim', $headers);

        $expected = ['investor_id', 'name', 'age', 'investment_amount', 'investment_date'];

        if ($headers !== $expected) {
            throw new InvalidCsvException('Unexpected CSV headers.');
        }

        return $headers;
    }

    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function mapRow(array $headers, array $row): array
    {
        if (count($row) !== count($headers)) {
            throw new InvalidCsvException('A row has the wrong number of columns.');
        }

        $data = array_combine($headers, array_map('trim', $row));

        $date = Carbon::createFromFormat('d-m-Y', $data['investment_date']);

        if (
            ! ctype_digit((string) $data['investor_id'])
            || ! ctype_digit((string) $data['age'])
            || ! is_numeric($data['investment_amount'])
            || $data['name'] === ''
            || $date === false
        ) {
            throw new InvalidCsvException("Invalid row for investor {$data['investor_id']}.");
        }

        return [
            'external_id' => (int) $data['investor_id'],
            'name' => $data['name'],
            'age' => (int) $data['age'],
            'amount' => round((float) $data['investment_amount'], 2),
            'investment_date' => $date->toDateString(),
        ];
    }

    private function storeChunk(array $rows): array
    {
        return DB::transaction(function () use ($rows) {
            $now = now();

            $investors = [];

            foreach ($rows as $row) {
                $investors[$row['external_id']] = [
                    'external_id' => $row['external_id'],
                    'name' => $row['name'],
                    'age' => $row['age'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Investor::upsert(
                array_values($investors),
                ['external_id'],
                ['name', 'age', 'updated_at']
            );

            $investorIds = Investor::query()
                ->whereIn('external_id', array_keys($investors))
                ->pluck('id', 'external_id');

            $investments = [];

            foreach ($rows as $row) {
                $investments[] = [
                    'investor_id' => $investorIds[$row['external_id']],
                    'amount' => $row['amount'],
                    'investment_date' => $row['investment_date'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Investment::upsert(
                $investments,
                ['investor_id', 'investment_date'],
                ['amount', 'updated_at']
            );

            return [count($investors), count($investments)];
        });
    }
}
