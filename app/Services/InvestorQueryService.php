<?php

namespace App\Services;

use App\Models\Investor;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestorQueryService
{
    public function paginated(int $perPage = 100): CursorPaginator
    {
        return Investor::query()
            ->select(['id', 'external_id', 'name', 'age'])
            ->withSum('investments', 'amount')
            ->orderBy('id')
            ->cursorPaginate($perPage);
    }

    public function toArray(Collection $investors): array
    {
        return $investors->map(fn (Investor $investor) => [
            'investor_id' => $investor->external_id,
            'name' => $investor->name,
            'age' => $investor->age,
            'investment_amount' => number_format((float) $investor->investments_sum_amount, 2, '.', ''),
        ])->values()->all();
    }

    public function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['investor_id', 'name', 'age', 'investment_amount']);

            Investor::query()
                ->select(['id', 'external_id', 'name', 'age'])
                ->withSum('investments', 'amount')
                ->orderBy('id')
                ->chunkById(500, function (Collection $investors) use ($handle) {
                    foreach ($investors as $investor) {
                        fputcsv($handle, [
                            $investor->external_id,
                            $investor->name,
                            $investor->age,
                            number_format((float) $investor->investments_sum_amount, 2, '.', ''),
                        ]);
                    }
                });

            fclose($handle);
        }, 'investors.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
