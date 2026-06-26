<?php

use App\Http\Controllers\Api\AggregateController;
use App\Http\Controllers\Api\ImportController;
use Illuminate\Support\Facades\Route;

Route::post('/import', [ImportController::class, 'store']);

Route::prefix('aggregates')->group(function () {
    Route::get('/average-age', [AggregateController::class, 'averageAge']);
    Route::get('/average-investment-amount', [AggregateController::class, 'averageInvestmentAmount']);
    Route::get('/total-investments', [AggregateController::class, 'totalInvestments']);
});
