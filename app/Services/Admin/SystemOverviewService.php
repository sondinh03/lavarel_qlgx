<?php

namespace App\Services\Admin;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\ParishAdminRegistrationRequest;
use App\Models\ParishNew;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemOverviewService
{
    public const CACHE_TTL = 300;

    public function get(): array
    {
        return Cache::remember('backpack.system_overview.v1', self::CACHE_TTL, function () {
            return [
                'overview'     => $this->buildOverview(),
                'roles'        => $this->buildRoleCounts(),
                'registrations'=> $this->buildRegistrationFunnel(),
                'pending'      => $this->buildPendingRegistrations(),
                'generated_at' => now(),
            ];
        });
    }

    public function forget(): void
    {
        Cache::forget('backpack.system_overview.v1');
    }

    protected function buildOverview(): array
    {
        $parishesTotal = ParishNew::query()->count();
        $parishesActive = ParishNew::query()->where('status', true)->count();

        $parishesWithAdmin = User::query()
            ->whereNotNull('parish_id')
            ->role(['parish_admin', 'parishioner_admin', 'catechism_admin'])
            ->pluck('parish_id')
            ->unique()
            ->count();

        return [
            'parishes_total'       => $parishesTotal,
            'parishes_active'      => $parishesActive,
            'parishes_inactive'    => max(0, $parishesTotal - $parishesActive),
            'parishes_with_admin'  => $parishesWithAdmin,
            'parishes_without_admin'=> max(0, $parishesActive - $parishesWithAdmin),
            'dioceses'             => Diocese::query()->count(),
            'deaneries'            => Deanery::query()->count(),
            'users_total'          => User::query()->count(),
            'users_with_parish'    => User::query()->whereNotNull('parish_id')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function buildRoleCounts(): array
    {
        $roles = [
            'super_admin',
            'parish_admin',
            'parishioner_admin',
            'catechism_admin',
            'catechist',
        ];

        $counts = [];
        foreach ($roles as $role) {
            $counts[$role] = User::role($role)->count();
        }

        return $counts;
    }

    protected function buildRegistrationFunnel(): array
    {
        $byStatus = ParishAdminRegistrationRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_PENDING] ?? 0);
        $approved = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_APPROVED] ?? 0);
        $rejected = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_REJECTED] ?? 0);
        $total = $pending + $approved + $rejected;
        $decided = $approved + $rejected;

        $weeks = $this->buildWeeklyTrend(8);

        return [
            'pending'       => $pending,
            'approved'      => $approved,
            'rejected'      => $rejected,
            'total'         => $total,
            'approval_rate' => $decided > 0 ? round($approved / $decided * 100) : null,
            'weeks'         => $weeks,
            'this_week'     => $weeks->last(),
        ];
    }

    /**
     * @return Collection<int, array{label: string, start: string, submitted: int, approved: int, rejected: int}>
     */
    protected function buildWeeklyTrend(int $weeks): Collection
    {
        $start = Carbon::now()->startOfWeek()->subWeeks($weeks - 1);
        $end = Carbon::now()->endOfWeek();

        $rows = ParishAdminRegistrationRequest::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->get(['status', 'created_at', 'reviewed_at']);

        $buckets = [];
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = (clone $start)->addWeeks($i);
            $weekEnd = (clone $weekStart)->endOfWeek();
            $key = $weekStart->format('Y-W');

            $buckets[$key] = [
                'label'     => $weekStart->format('d/m') . '–' . $weekEnd->format('d/m'),
                'start'     => $weekStart->toDateString(),
                'submitted' => 0,
                'approved'  => 0,
                'rejected'  => 0,
            ];
        }

        foreach ($rows as $row) {
            $createdKey = $row->created_at?->copy()->startOfWeek()->format('Y-W');
            if ($createdKey && isset($buckets[$createdKey])) {
                $buckets[$createdKey]['submitted']++;
            }

            if ($row->reviewed_at && in_array($row->status, [
                ParishAdminRegistrationRequest::STATUS_APPROVED,
                ParishAdminRegistrationRequest::STATUS_REJECTED,
            ], true)) {
                $reviewedKey = $row->reviewed_at->copy()->startOfWeek()->format('Y-W');
                if ($reviewedKey && isset($buckets[$reviewedKey])) {
                    if ($row->status === ParishAdminRegistrationRequest::STATUS_APPROVED) {
                        $buckets[$reviewedKey]['approved']++;
                    } else {
                        $buckets[$reviewedKey]['rejected']++;
                    }
                }
            }
        }

        return collect(array_values($buckets));
    }

    protected function buildPendingRegistrations(): Collection
    {
        return ParishAdminRegistrationRequest::query()
            ->with(['parish:id,name', 'diocese:id,name', 'deanery:id,name'])
            ->where('status', ParishAdminRegistrationRequest::STATUS_PENDING)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (ParishAdminRegistrationRequest $r) => [
                'id'             => $r->id,
                'reference_code' => $r->reference_code,
                'name'           => $r->name ?: $r->email,
                'email'          => $r->email,
                'parish'         => $r->parishDisplayName(),
                'roles'          => implode(', ', $r->requestedRoleLabels()) ?: '—',
                'created_at'     => $r->created_at,
                'url'            => backpack_url('parish-admin-registration/' . $r->id . '/show'),
            ]);
    }
}
