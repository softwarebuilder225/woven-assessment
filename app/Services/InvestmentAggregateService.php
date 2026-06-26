<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Investor;

class InvestmentAggregateService
{
    public function averageAge(): float
    {
        return (float) Investor::query()->avg('age');
    }

    public function averageInvestmentAmount(): float
    {
        return (float) Investment::query()->avg('amount');
    }

    public function totalInvestments(): int
    {
        return Investment::query()->count();
    }
}
