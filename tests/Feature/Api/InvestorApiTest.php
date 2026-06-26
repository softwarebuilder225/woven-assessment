<?php

namespace Tests\Feature\Api;

use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InvestorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_endpoint(): void
    {
        $csv = implode("\n", [
            'investor_id,name,age,investment_amount,investment_date',
            '1001,Daniel Nelson,28,328085.43,13-11-2024',
        ]);

        $response = $this->post('/api/import', [
            'file' => UploadedFile::fake()->createWithContent('investors.csv', $csv),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.investors_processed', 1)
            ->assertJsonPath('data.investments_processed', 1);
    }

    public function test_aggregate_endpoints(): void
    {
        $investor = Investor::factory()->create(['age' => 50]);
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 200.00,
            'investment_date' => '2024-01-01',
        ]);

        $this->getJson('/api/aggregates/average-age')
            ->assertOk()
            ->assertJsonPath('average_age', 50);

        $this->getJson('/api/aggregates/average-investment-amount')
            ->assertOk()
            ->assertJsonPath('average_investment_amount', 200);

        $this->getJson('/api/aggregates/total-investments')
            ->assertOk()
            ->assertJsonPath('total_investments', 1);
    }

    public function test_investor_listing(): void
    {
        $investor = Investor::factory()->create([
            'external_id' => 1001,
            'name' => 'Daniel Nelson',
            'age' => 28,
        ]);
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 328085.43,
            'investment_date' => '2024-11-13',
        ]);

        $this->getJson('/api/investors')
            ->assertOk()
            ->assertJsonPath('data.0.investor_id', 1001)
            ->assertJsonPath('data.0.investment_amount', '328085.43');
    }
}
