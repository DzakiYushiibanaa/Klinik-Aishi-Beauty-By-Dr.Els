<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Auth guard (Filament uses default web guard)
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = (string) $request->input('q', '');
        $limit = (int) $request->input('limit', GlobalSearchService::DEFAULT_LIMIT);
        $limit = max(3, min(15, $limit));

        $service = new GlobalSearchService($query, $limit);
        return response()->json($service->search());
    }
}
