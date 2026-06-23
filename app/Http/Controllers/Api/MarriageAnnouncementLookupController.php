<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarriageAnnouncementLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarriageAnnouncementLookupController extends Controller
{
    public function __construct(private MarriageAnnouncementLookupService $lookup) {}

    public function priests(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchPriests(
                $request->query('search'),
                (int) $request->query('limit', 20)
            )
        );
    }

    public function dioceses(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchDioceses($request->query('search'))
        );
    }

    public function deaneries(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchDeaneries(
                $request->integer('diocese_id') ?: null,
                $request->query('search')
            )
        );
    }

    public function parishManagements(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchParishes(
                $request->integer('deanery_id') ?: null,
                $request->query('search')
            )
        );
    }

    public function legacyParishes(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchParishGroups(
                $request->integer('parish_id') ?: $request->integer('parish_management_id') ?: null,
                $request->query('search')
            )
        );
    }

    public function parishes(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchParishes(
                $request->integer('deanery_id') ?: null,
                $request->query('search')
            )
        );
    }

    public function parishGroups(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchParishGroups(
                $request->integer('parish_id') ?: null,
                $request->query('search')
            )
        );
    }

    public function parishioners(Request $request): JsonResponse
    {
        return response()->json(
            $this->lookup->searchParishioners(
                $request->integer('parish_id') ?: null,
                $request->query('gender'),
                $request->query('search')
            )
        );
    }
}
