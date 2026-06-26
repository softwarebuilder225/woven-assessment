<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvestorQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestorController extends Controller
{
    public function __construct(private InvestorQueryService $investors)
    {
    }

    public function index(Request $request): JsonResponse|StreamedResponse
    {
        if ($request->query('format') === 'csv') {
            return $this->investors->exportCsv();
        }

        $perPage = min((int) $request->query('per_page', 100), 500);
        $paginator = $this->investors->paginated($perPage);

        return response()->json([
            'data' => $this->investors->toArray($paginator->getCollection()),
            'meta' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
            ],
        ]);
    }
}
