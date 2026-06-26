<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidCsvException;
use App\Models\Investment;
use App\Models\Investor;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private CsvImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CsvImportService;
    }

    public function test_imports_valid_rows(): void
    {
        $csv = implode("\n", [
            'investor_id,name,age,investment_amount,investment_date',
            '1001,Daniel Nelson,28,328085.43,13-11-2024',
            '1002,Henry Gill,38,185907.73,07-02-2024',
        ]);

        $file = UploadedFile::fake()->createWithContent('investors.csv', $csv);
        $result = $this->service->import($file);

        $this->assertSame(2, $result['investors_processed']);
        $this->assertSame(2, $result['investments_processed']);
        $this->assertDatabaseHas('investors', [
            'external_id' => 1001,
            'name' => 'Daniel Nelson',
            'age' => 28,
        ]);
        $this->assertDatabaseHas('investments', [
            'amount' => '328085.43',
            'investment_date' => '2024-11-13',
        ]);
    }

    public function test_upserts_duplicates(): void
    {
        $csv = implode("\n", [
            'investor_id,name,age,investment_amount,investment_date',
            '1001,Daniel Nelson,28,100.00,13-11-2024',
            '1001,Daniel Nelson Updated,29,200.00,13-11-2024',
        ]);

        $file = UploadedFile::fake()->createWithContent('investors.csv', $csv);
        $this->service->import($file);

        $this->assertDatabaseCount('investors', 1);
        $this->assertDatabaseCount('investments', 1);
        $this->assertDatabaseHas('investors', [
            'external_id' => 1001,
            'name' => 'Daniel Nelson Updated',
            'age' => 29,
        ]);
        $this->assertDatabaseHas('investments', [
            'amount' => '200.00',
            'investment_date' => '2024-11-13',
        ]);
    }

    public function test_rejects_invalid_headers(): void
    {
        $csv = "id,name,age,amount,date\n1,Test,30,100,01-01-2024\n";
        $file = UploadedFile::fake()->createWithContent('investors.csv', $csv);

        $this->expectException(InvalidCsvException::class);
        $this->service->import($file);
    }

    public function test_processes_rows_in_chunks(): void
    {
        $lines = ['investor_id,name,age,investment_amount,investment_date'];

        for ($i = 1; $i <= 501; $i++) {
            $lines[] = sprintf(
                '%d,Investor %d,30,1000.00,01-01-2024',
                1000 + $i,
                $i
            );
        }

        $file = UploadedFile::fake()->createWithContent('investors.csv', implode("\n", $lines));
        $result = $this->service->import($file);

        $this->assertSame(501, $result['investors_processed']);
        $this->assertSame(501, $result['investments_processed']);
        $this->assertDatabaseCount('investors', 501);
        $this->assertDatabaseCount('investments', 501);
    }
}
