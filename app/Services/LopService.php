<?php

namespace App\Services;

use App\Models\Lop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

class LopService
{
    /**
     * Build query and return paginated lops with enriched data (slug_url, teacher info)
     *
     * @param int $namHocId
     * @param int|null $khoiId
     * @param int $perPage
     * @param string $search
     * @return LengthAwarePaginator
     */
    public function paginateLops(int $namHocId, $khoiId = null, int $perPage = 15, string $search = '', ?int $page = null): LengthAwarePaginator
    {
        $query = Lop::query()
            ->where('schoolyear', $namHocId)
            ->where('status', 1)
            ->with([
                'blockRelation',
                'slug',
                'classTeachers' => function ($q) use ($namHocId) {
                    $q->where('namhoc_id', $namHocId)
                        ->where('status', 1)
                        ->with('teacher')
                        ->orderByRaw('FIELD(role, 1, 2, 3)');
                }
            ])
            ->withCount('students');

        if (!empty($khoiId)) {
            $query->where('block', $khoiId);
        }

        // Sanitize and limit search input to avoid wildcard injection and excessive length
        $search = is_string($search) ? trim($search) : '';
        $search = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', $search), 100);
        // remove LIKE wildcards to avoid accidental broad matching
        $search = str_replace(['%', '_'], ['',''], $search);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $like = "%{$search}%";
                $q->where('name', 'like', $like)
                    ->orWhere('symbol', 'like', $like);
            });
        }

        // Resolve page
        $page = $page ?: Paginator::resolveCurrentPage() ?: 1;

        // Cache paginated result for a short TTL to reduce DB load for frequent reads
        // include a version token for simple invalidation when data changes
        $version = Cache::get("lops:version:namhoc:{$namHocId}", 1);

        $cacheKey = sprintf(
            'lops:paginated:ver:%s:namhoc:%s:khoi:%s:search:%s:page:%s:per:%s',
            $version,
            $namHocId,
            $khoiId ?? 'all',
            md5($search),
            $page,
            $perPage
        );

        $ttl = 120; // seconds (2 phút)

        $paginator = Cache::remember($cacheKey, $ttl, function () use ($query, $perPage, $page) {
            return $query->orderBy('name', 'asc')->paginate($perPage, ['*'], 'page', $page);
        });

        return $paginator;
    }
}
