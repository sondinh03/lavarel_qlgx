<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Services\ParishionerStatsService;
use Illuminate\Support\Facades\Cache;

class ParishionerDashboard extends BaseComponent
{
    protected $usePagination = false;

    const CACHE_TTL = 600;

    public $stats = [
        'parishioners'          => 0,
        'families'              => 0,
        'parish_groups'         => 0,
        'holy_names'            => 0,
        'new_converts'          => 0,
        'deceased'              => 0,
        'utility_groups'        => 0,
        'pending_registrations' => 0,
    ];

    public $genderStats = ['male' => 0, 'female' => 0, 'total' => 0, 'male_rate' => 0, 'female_rate' => 0];
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
            $service = app(ParishionerStatsService::class);

            return [
                'stats'              => $service->buildSummary($parishId),
                'genderStats'        => $service->buildGenderStats($parishId),
                'recentParishioners' => $service->buildRecentParishioners($parishId, 5),
            ];
        });

        $this->stats = $data['stats'] ?? $this->stats;
        $this->genderStats = $data['genderStats'] ?? $this->genderStats;
        $this->recentParishioners = $data['recentParishioners'] ?? [];
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
        return "parishioner_dashboard_v2_{$this->parishId}";
    }

    public function render()
    {
        return view('livewire.dashboard.parishioner-dashboard')
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
