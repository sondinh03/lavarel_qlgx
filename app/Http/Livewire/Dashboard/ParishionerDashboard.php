<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Family;
use App\Models\Parishioner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ParishionerDashboard extends BaseComponent
{
    protected $usePagination = false;

    const CACHE_TTL = 600;

    public $stats = [
        'parishioners' => 0,
        'families' => 0,
        'parish_groups' => 0,
        'holy_names' => 0,
        'new_converts' => 0,
        'deceased' => 0,
        'utility_groups' => 0,
    ];

    public $genderStats = ['male' => 0, 'female' => 0];
    public $ageGroups = [];
    public $recentParishioners = [];
    public $todayLabel = '';

    public function mount(): void
    {
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->todayLabel = $this->buildTodayLabel();
        $this->loadDashboard();
    }

    public function refresh(): void
    {
        Cache::forget($this->cacheKey());
        $this->loadDashboard();
        session()->flash('message', 'Đã làm mới dữ liệu');
    }

    private function loadDashboard(): void
    {
        $parishId = $this->parishId;

        $data = Cache::remember($this->cacheKey(), self::CACHE_TTL, function () use ($parishId) {
            return [
                'stats' => $this->buildStats($parishId),
                'genderStats' => $this->buildGenderStats($parishId),
                'ageGroups' => $this->buildAgeGroups($parishId),
                'recentParishioners' => $this->buildRecentParishioners($parishId),
            ];
        });

        $this->stats = $data['stats'] ?? $this->stats;
        $this->genderStats = $data['genderStats'] ?? $this->genderStats;
        $this->ageGroups = $data['ageGroups'] ?? [];
        $this->recentParishioners = $data['recentParishioners'] ?? [];
    }

    private function buildStats(int $parishId): array
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

        $parishGroups = (int) DB::table('parish_group')
            ->where('parish_id', $parishId)
            ->count('id');

        $holyNames = (int) DB::table('holymanagements')
            ->count('id');

        $utilityGroups = (int) DB::table('groups')
            ->where('parish_id', $parishId)
            ->whereIn('type', [3, 4, 5, 6])
            ->count('id');

        return [
            'parishioners' => (int) $parishioners,
            'families' => (int) $families,
            'parish_groups' => $parishGroups,
            'holy_names' => $holyNames,
            'new_converts' => (int) $newConverts,
            'deceased' => (int) $deceased,
            'utility_groups' => $utilityGroups,
        ];
    }

    private function buildGenderStats(int $parishId): array
    {
        $row = Parishioner::where('parish_id', $parishId)
            ->active()
            ->alive()
            ->selectRaw('
                SUM(CASE WHEN gender = "male" THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = "female" THEN 1 ELSE 0 END) as female
            ')
            ->first();

        return [
            'male' => (int) ($row->male ?? 0),
            'female' => (int) ($row->female ?? 0),
        ];
    }

    private function buildAgeGroups(int $parishId): array
    {
        $now = now()->toDateString();

        // group theo tuổi: 0-12, 13-18, 19-35, 36-60, 60+
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

        return array_map(fn($i) => [
            ...$i,
            'max' => $max,
        ], $data);
    }

    private function buildRecentParishioners(int $parishId): array
    {
        $rows = Parishioner::where('parish_id', $parishId)
            ->active()
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'gender', 'phone']);

        return $rows->map(fn(Parishioner $p) => [
            'id' => $p->id,
            'name' => $p->full_name,
            'gender' => $p->gender_name,
            'phone' => $p->phone,
            'url' => route('parishioners.show', $p->id),
        ])->toArray();
    }

    private function buildTodayLabel(): string
    {
        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy',
        ];

        return $days[now()->dayOfWeek] . ', ' . now()->format('d/m/Y');
    }

    private function cacheKey(): string
    {
        return "parishioner_dashboard_v1_{$this->parishId}";
    }

    public function render()
    {
        return view('livewire.dashboard.parishioner-dashboard')
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}

