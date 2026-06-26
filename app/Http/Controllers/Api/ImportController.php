<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportCsvRequest;
use App\Services\CsvImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(private CsvImportService $csvImportService)
    {
    }

    public function store(ImportCsvRequest $request): JsonResponse
    {
        $result = $this->csvImportService->import($request->file('file'));

        return response()->json([
            'message' => 'CSV imported successfully.',
            'data' => $result,
        ]);
    }
}
