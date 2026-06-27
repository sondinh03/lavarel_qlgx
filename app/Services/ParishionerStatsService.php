<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Parishioner;
use App\Models\ParishionerRegistrationRequest;
use Illuminate\Support\Facades\DB;

class ParishionerStatsService
{
    public function buildSummary(int $parishId): array
    {
        $parishioners = Parishioner::where('parish_id', $parishId)
            ->active()
            ->alive()
            ->count();

        $families = Family::where('parish_id', $parishId)
            ->active()
            ->count();

        $newConverts = Parishioner::where('parish_id', $parishId)
            ->active()
            ->alive()
            ->newConvert()
            ->count();

        $deceased = Parishioner::where('parish_id', $parishId)
            ->active()
            ->deceased()
            ->count();

        $parishGroups = (int) DB::table('parish_groups')
            ->where('parish_id', $parishId)
            ->count('id');

        $holyNames = (int) DB::table('holymanagements')->count('id');

        $utilityGroups = 0;
        try {
            $utilityGroups = (int) DB::table('groups')
                ->where('parish_id', $parishId)
                ->whereIn('type', [3, 4, 5, 6])
                ->count('id');
        } catch (\Throwable) {
            // Bảng groups có thể chưa có trên một số môi trường
        }

        $pendingRegistrations = ParishionerRegistrationRequest::query()
            ->where('parish_id', $parishId)
            ->where('status', ParishionerRegistrationRequest::STATUS_PENDING)
            ->count();

        return [
            'parishioners'           => (int) $parishioners,
            'families'               => (int) $families,
            'parish_groups'          => $parishGroups,
            'holy_names'             => $holyNames,
            'new_converts'           => (int) $newConverts,
            'deceased'               => (int) $deceased,
            'utility_groups'         => $utilityGroups,
            'pending_registrations'  => (int) $pendingRegistrations,
        ];
    }

    public function buildGenderStats(int $parishId): array
    {
        $row = Parishioner::where('parish_id', $parishId)
            ->active()
            ->alive()
            ->selectRaw('
                SUM(CASE WHEN gender = "male" THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = "female" THEN 1 ELSE 0 END) as female
            ')
            ->first();

        $male = (int) ($row->male ?? 0);
        $female = (int) ($row->female ?? 0);
        $total = $male + $female;

        return [
            'male'        => $male,
            'female'      => $female,
            'total'       => $total,
            'male_rate'   => $total > 0 ? round($male / $total * 100, 1) : 0,
            'female_rate' => $total > 0 ? round($female / $total * 100, 1) : 0,
        ];
    }

    public function buildGenderChart(int $parishId): array
    {
        $stats = $this->buildGenderStats($parishId);
        $total = $stats['total'];
        $other = max(0, $total - $stats['male'] - $stats['female']);
        $pct = fn (int $n) => $total > 0 ? round($n / $total * 100, 1) : 0;

        return [
            ['label' => 'Nam',           'count' => $stats['male'],   'color' => '#3b82f6', 'percentage' => $pct($stats['male'])],
            ['label' => 'Nữ',            'count' => $stats['female'], 'color' => '#ec4899', 'percentage' => $pct($stats['female'])],
            ['label' => 'Chưa xác định', 'count' => $other,           'color' => '#cbd5e1', 'percentage' => $pct($other)],
        ];
    }

    public function buildAgeGroups(int $parishId): array
    {
        $now = now()->toDateString();

        $rows = DB::table('parishioners_new')
            ->where('parish_id', $parishId)
            ->where('status', true)
            ->whereNull('death_date')
            ->whereNotNull('birthday')
            ->selectRaw("
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, '{$now}') <= 12 THEN 1 ELSE 0 END) as g1,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, '{$now}') BETWEEN 13 AND 18 THEN 1 ELSE 0 END) as g2,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, '{$now}') BETWEEN 19 AND 35 THEN 1 ELSE 0 END) as g3,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, '{$now}') BETWEEN 36 AND 60 THEN 1 ELSE 0 END) as g4,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, '{$now}') >= 61 THEN 1 ELSE 0 END) as g5
            ")
            ->first();

        $data = [
            ['label' => 'Thiếu nhi (0-12)', 'count' => (int) ($rows->g1 ?? 0)],
            ['label' => 'Thiếu niên (13-18)', 'count' => (int) ($rows->g2 ?? 0)],
            ['label' => 'Thanh niên (19-35)', 'count' => (int) ($rows->g3 ?? 0)],
            ['label' => 'Trung niên (36-60)', 'count' => (int) ($rows->g4 ?? 0)],
            ['label' => 'Cao niên (60+)', 'count' => (int) ($rows->g5 ?? 0)],
        ];

        $max = collect($data)->max('count') ?: 1;

        return array_map(fn ($item) => [...$item, 'max' => $max], $data);
    }

    public function buildParishGroupChart(int $parishId): array
    {
        $counts = DB::table('parishioners_new as p')
            ->join('parish_groups as pg', 'pg.id', '=', 'p.parish_area_id')
            ->where('p.parish_id', $parishId)
            ->where('p.status', true)
            ->whereNull('p.death_date')
            ->where('pg.parish_id', $parishId)
            ->selectRaw('pg.id, pg.name, COUNT(p.id) as parishioner_count')
            ->groupBy('pg.id', 'pg.name')
            ->orderByDesc('parishioner_count')
            ->orderBy('pg.name')
            ->get();

        return $counts->map(fn ($row) => [
            'label' => $row->name,
            'count' => (int) $row->parishioner_count,
        ])->values()->all();
    }

    public function buildAssociationChart(int $parishId): array
    {
        $counts = DB::table('parishioners_new as p')
            ->join('associations as a', 'a.id', '=', 'p.association_id')
            ->where('p.parish_id', $parishId)
            ->where('p.status', true)
            ->whereNull('p.death_date')
            ->where('a.pid', $parishId)
            ->selectRaw('a.id, a.name, COUNT(p.id) as parishioner_count')
            ->groupBy('a.id', 'a.name')
            ->orderByDesc('parishioner_count')
            ->orderBy('a.name')
            ->limit(15)
            ->get();

        return $counts->map(fn ($row) => [
            'label' => $row->name,
            'count' => (int) $row->parishioner_count,
        ])->values()->all();
    }

    public function buildRecentParishioners(int $parishId, int $limit = 10): array
    {
        return Parishioner::where('parish_id', $parishId)
            ->active()
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'gender', 'phone'])
            ->map(fn (Parishioner $p) => [
                'id'     => $p->id,
                'name'   => $p->full_name,
                'gender' => $p->gender_name,
                'phone'  => $p->phone,
                'url'    => route('parishioners.show', $p->id),
            ])
            ->toArray();
    }

    public function buildFamilyRoleStats(int $parishId): array
    {
        $rows = Parishioner::where('parish_id', $parishId)
            ->active()
            ->alive()
            ->whereNotNull('family_role')
            ->selectRaw('family_role, COUNT(*) as total')
            ->groupBy('family_role')
            ->pluck('total', 'family_role');

        $labels = [
            'husband' => 'Chồng',
            'wife'    => 'Vợ',
            'child'   => 'Con',
            'other'   => 'Khác',
        ];

        return collect($labels)->map(fn ($label, $role) => [
            'label' => $label,
            'count' => (int) ($rows[$role] ?? 0),
        ])->values()->all();
    }
}
