<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvestmentAggregateService;
use Illuminate\Http\JsonResponse;

class AggregateController extends Controller
{
    public function __construct(private InvestmentAggregateService $aggregates)
    {
    }

    public function averageAge(): JsonResponse
    {
        return response()->json([
            'average_age' => round($this->aggregates->averageAge(), 2),
        ]);
    }

    public function averageInvestmentAmount(): JsonResponse
    {
        return response()->json([
            'average_investment_amount' => round($this->aggregates->averageInvestmentAmount(), 2),
        ]);
    }

    public function totalInvestments(): JsonResponse
    {
        return response()->json([
            'total_investments' => $this->aggregates->totalInvestments(),
        ]);
    }
}
