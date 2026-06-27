<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parishioner;
use App\Services\ParishionerStatsService;
use Illuminate\Support\Facades\Cache;

class ParishionerStatistics extends BaseComponent
{
    protected $usePagination = false;

    const CACHE_TTL = 600;

    public array $stats = [];
    public array $genderStats = [];
    public array $genderChartData = [];
    public array $ageGroups = [];
    public array $parishGroupChart = [];
    public array $associationChart = [];
    public array $familyRoleStats = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Parishioner::class);
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadStatistics();
    }

    public function refresh(): void
    {
        Cache::forget($this->cacheKey());
        $this->loadStatistics();
        session()->flash('message', 'Đã làm mới dữ liệu thống kê');
    }

    private function loadStatistics(): void
    {
        $parishId = $this->parishId;

        $data = Cache::remember($this->cacheKey(), self::CACHE_TTL, function () use ($parishId) {
            $service = app(ParishionerStatsService::class);

            return [
                'stats'              => $service->buildSummary($parishId),
                'genderStats'        => $service->buildGenderStats($parishId),
                'genderChartData'    => $service->buildGenderChart($parishId),
                'ageGroups'          => $service->buildAgeGroups($parishId),
                'parishGroupChart'   => $service->buildParishGroupChart($parishId),
                'associationChart'   => $service->buildAssociationChart($parishId),
                'familyRoleStats'    => $service->buildFamilyRoleStats($parishId),
            ];
        });

        $this->stats = $data['stats'] ?? [];
        $this->genderStats = $data['genderStats'] ?? [];
        $this->genderChartData = $data['genderChartData'] ?? [];
        $this->ageGroups = $data['ageGroups'] ?? [];
        $this->parishGroupChart = $data['parishGroupChart'] ?? [];
        $this->associationChart = $data['associationChart'] ?? [];
        $this->familyRoleStats = $data['familyRoleStats'] ?? [];
    }

    private function cacheKey(): string
    {
        return "parishioner_statistics_v1_{$this->parishId}";
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-statistics')
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
