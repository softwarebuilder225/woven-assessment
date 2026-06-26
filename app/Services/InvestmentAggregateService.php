<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Support\Facades\DB;

class InvestmentAggregateService
{
    public function averageAge(): float
    {
        return (float) Investor::query()->avg('age');
    }

    public function averageInvestmentAmount(): float
    {
        // Average each investor's total, then mean — matches "across all investors"
        $totals = Investment::query()
            ->select('investor_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('investor_id');

        return (float) DB::query()->fromSub($totals, 'investor_totals')->avg('total');
    }

    public function totalInvestments(): int
    {
        return Investment::query()->count();
    }
}
