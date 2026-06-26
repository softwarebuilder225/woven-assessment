<?php

namespace Tests\Unit\Services;

use App\Models\Investment;
use App\Models\Investor;
use App\Services\InvestmentAggregateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentAggregateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_aggregate_metrics(): void
    {
        $investorA = Investor::factory()->create(['age' => 40]);
        $investorB = Investor::factory()->create(['age' => 60]);

        Investment::factory()->create([
            'investor_id' => $investorA->id,
            'amount' => 100.00,
            'investment_date' => '2024-01-01',
        ]);
        Investment::factory()->create([
            'investor_id' => $investorB->id,
            'amount' => 300.00,
            'investment_date' => '2024-02-01',
        ]);

        $service = new InvestmentAggregateService;

        $this->assertSame(50.0, $service->averageAge());
        $this->assertSame(200.0, $service->averageInvestmentAmount());
        $this->assertSame(2, $service->totalInvestments());
    }
}
