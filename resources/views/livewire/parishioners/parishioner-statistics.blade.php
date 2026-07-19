@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Thống kê giáo dân'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl space-y-5">

        <x-mac-panel>
            <div class="px-4 lg:px-6 py-5 flex items-center justify-between gap-4 flex-wrap mac-hairline-b">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50/90 ring-1 ring-primary-100/80 flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Thống kê giáo dân</h1>
                        <p class="text-sm text-slate-500">Tổng hợp số liệu giáo dân đang sinh hoạt trong giáo xứ</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('parishioners.dashboard') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-black/[0.08]
                               text-sm font-semibold text-slate-600 hover:bg-black/[0.03] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Dashboard
                    </a>
                    <x-button wire:click="refresh" variant="subtle" wire:loading.attr="disabled" wire:target="refresh">
                        <x-icon name="refresh" wire:loading.class="animate-spin" wire:target="refresh" />
                        Làm mới
                    </x-button>
                </div>
            </div>
        </x-mac-panel>

        @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3000">{{ session('message') }}</x-toast-notification>
        @endif

        @if(($stats['parishioners'] ?? 0) === 0)
        <x-stats.page-empty
            tone="primary"
            title="Chưa có giáo dân trong hệ thống"
            description="Thêm giáo dân hoặc duyệt đăng ký để xem thống kê">
            <a href="{{ route('parishioners.index') }}" class="text-primary-500 underline hover:text-primary-700">
                danh sách giáo dân
            </a>
        </x-stats.page-empty>
        @else

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <x-stats.stat-card label="Giáo dân" :value="number_format($stats['parishioners'] ?? 0)" subline="Đang sinh hoạt" />
            <x-stats.stat-card label="Gia đình" :value="number_format($stats['families'] ?? 0)" subline="Đang hoạt động" />
            <x-stats.stat-card
                label="Tỷ lệ nam"
                :value="($genderStats['male_rate'] ?? 0)"
                suffix="%"
                :subline="number_format($genderStats['male'] ?? 0) . ' nam / ' . number_format($genderStats['female'] ?? 0) . ' nữ'"
                value-class="text-primary-600" />
            <x-stats.stat-card label="Tân tòng" :value="number_format($stats['new_converts'] ?? 0)" subline="Đang sinh hoạt" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <x-stats.chart-card class="lg:col-span-2" title="Phân bố giới tính" :right="number_format($genderStats['total'] ?? 0) . ' giáo dân'">
                @if(empty($genderChartData) || ($genderStats['total'] ?? 0) === 0)
                <div class="flex items-center justify-center h-44 text-slate-400 text-sm">Chưa có dữ liệu</div>
                @else
                <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center gap-6">
                    <div class="relative flex-shrink-0 w-44 h-44">
                        <canvas id="genderDonutChart" width="176" height="176"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-extrabold text-slate-800">{{ $genderStats['male_rate'] ?? 0 }}%</span>
                            <span class="text-xs text-slate-400">nam</span>
                        </div>
                    </div>
                    <div class="flex-1 space-y-3 w-full">
                        @foreach($genderChartData as $item)
                        @php
                            $dotClass = match ($item['color'] ?? '') {
                                '#3b82f6' => 'bg-blue-500',
                                '#ec4899' => 'bg-pink-500',
                                '#cbd5e1' => 'bg-slate-300',
                                default   => 'bg-slate-300',
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full flex-shrink-0 {{ $dotClass }}"></span>
                                <span class="text-sm text-slate-600 truncate">{{ $item['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold text-slate-800">{{ number_format($item['count']) }}</span>
                                <span class="text-xs text-slate-400 w-10 text-right">{{ $item['percentage'] }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </x-stats.chart-card>

            <x-stats.chart-card class="lg:col-span-3" title="Cơ cấu độ tuổi" right="Theo ngày sinh">
                @if(empty($ageGroups))
                <div class="flex items-center justify-center h-48 text-slate-400 text-sm">Chưa có dữ liệu ngày sinh</div>
                @else
                <div class="space-y-4">
                    @foreach($ageGroups as $g)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-slate-700">{{ $g['label'] }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ number_format($g['count']) }}</span>
                        </div>
                        <progress
                            value="{{ $g['count'] }}"
                            max="{{ $g['max'] }}"
                            class="w-full h-2 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-slate-100 [&::-webkit-progress-value]:bg-primary-500 [&::-moz-progress-bar]:bg-primary-500">
                        </progress>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-stats.chart-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <x-stats.chart-card title="Giáo dân theo giáo họ" :right="count($parishGroupChart) . ' giáo họ'">
                @if(empty($parishGroupChart))
                <div class="flex items-center justify-center h-48 text-slate-400 text-sm">Chưa có giáo dân gắn giáo họ</div>
                @else
                <canvas id="parishGroupBarChart"></canvas>
                @endif
            </x-stats.chart-card>

            <x-stats.chart-card title="Giáo dân theo hội đoàn" :right="count($associationChart) . ' hội'">
                @if(empty($associationChart))
                <div class="flex items-center justify-center h-48 text-slate-400 text-sm">Chưa có giáo dân gắn hội đoàn</div>
                @else
                <canvas id="associationBarChart"></canvas>
                @endif
            </x-stats.chart-card>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($familyRoleStats as $role)
            <x-stats.stat-card :label="$role['label']" :value="number_format($role['count'])" subline="Vai trò trong hộ" />
            @endforeach
            <x-stats.stat-card label="Qua đời" :value="number_format($stats['deceased'] ?? 0)" subline="Đã ghi nhận" />
            <x-stats.stat-card label="Giáo họ" :value="number_format($stats['parish_groups'] ?? 0)" subline="Đơn vị" />
        </div>

        <div class="rounded-2xl border border-black/[0.06] bg-white/50 backdrop-blur-sm shadow-mac-sm px-4 lg:px-6 py-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Ghi chú:</span>
                <span>Chỉ tính giáo dân trạng thái hoạt động, chưa ghi nhận qua đời</span>
                <span class="text-slate-400">Dữ liệu được cache 10 phút — nhấn Làm mới để cập nhật</span>
            </div>
        </div>

        @php
            $chartPayload = [
                'gender' => $genderChartData,
                'parishGroups' => $parishGroupChart,
                'associations' => $associationChart,
            ];
        @endphp
        <div id="parishioner-stats-chart-payload" class="hidden" aria-hidden="true">{!! json_encode($chartPayload) !!}</div>

        @endif
    </div>
</div>

@include('livewire.partials.chart-livewire-bridge')

@push('scripts')
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    function buildBarChart(canvasId, items, color) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !items.length) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: items.map(d => d.label),
                datasets: [{
                    data: items.map(d => d.count),
                    backgroundColor: color,
                    borderRadius: 8,
                    maxBarThickness: 36,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: items.length > 6 ? 1.4 : 2,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { maxRotation: 45, minRotation: 0, font: { size: 11 } },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                    },
                },
            },
        });
    }

    window.qlgxChartBridge.register('parishioner-stats', {
        payloadId: 'parishioner-stats-chart-payload',
        canvasIds: ['genderDonutChart', 'parishGroupBarChart', 'associationBarChart'],
        render(payload) {
            const genderData = payload.gender || [];
            const parishGroups = payload.parishGroups || [];
            const associations = payload.associations || [];

            const donutCanvas = document.getElementById('genderDonutChart');
            if (donutCanvas && genderData.length) {
                const nonZero = genderData.filter(d => d.count > 0);
                if (nonZero.length) {
                    new Chart(donutCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: nonZero.map(d => d.label),
                            datasets: [{
                                data: nonZero.map(d => d.count),
                                backgroundColor: nonZero.map(d => d.color),
                                borderColor: '#ffffff',
                                borderWidth: 3,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            cutout: '68%',
                            plugins: { legend: { display: false } },
                        },
                    });
                }
            }

            buildBarChart('parishGroupBarChart', parishGroups, '#6366f1');
            buildBarChart('associationBarChart', associations, '#10b981');
        },
    });
})();
</script>
@endpush
