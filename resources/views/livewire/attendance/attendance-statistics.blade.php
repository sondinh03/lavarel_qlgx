@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ',   'url' => route('dashboard')],
        ['label' => 'Điểm danh',   'url' => route('attendance.show')],
        ['label' => 'Thống kê điểm danh'],
    ]" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ===================== HEADER ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Title row --}}
            <div class="px-6 py-5 flex items-center justify-between gap-4 flex-wrap border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Thống kê điểm danh</h1>
                        <p class="text-sm text-slate-500">Phân tích tỷ lệ chuyên cần theo lớp, khối và toàn xứ</p>
                    </div>
                </div>

                <a href="{{ route('attendance.show') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300
                          text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Quay lại điểm danh
                </a>
            </div>

            {{-- Filter + Controls --}}
            <div class="px-6 py-4 bg-slate-50/60 border-b border-slate-100">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <livewire:filters.filter-bar
                        :parish-id="$parishId"
                        :show-nam-hoc="true"
                        :show-khoi="true"
                        :show-lop="true"
                        :show-ky="true"
                        :selected-nam-hoc="$selectedNamHoc"
                        :selected-khoi="$selectedKhoi"
                        :selected-lop="$selectedClassId"
                        :selected-ky="$selectedKy" />

                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- Type toggle: Đi học / Đi lễ --}}
                        <div class="flex gap-1 bg-slate-200 p-1 rounded-xl">
                            <button wire:click="setType(1)"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $attendanceType == 1
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Đi học
                            </button>
                            <button wire:click="setType(2)"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $attendanceType == 2
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Đi lễ
                            </button>
                        </div>

                        {{-- Group by: ngày / tuần / tháng / khoảng thời gian --}}
                        <div class="flex gap-1 bg-slate-200 p-1 rounded-xl">
                            <button wire:click="$set('groupBy', 'day')"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $groupBy === 'day'
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Ngày
                            </button>
                            <button wire:click="$set('groupBy', 'week')"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $groupBy === 'week'
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Tuần
                            </button>
                            <button wire:click="$set('groupBy', 'month')"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $groupBy === 'month'
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Tháng
                            </button>
                            <button wire:click="$set('groupBy', 'range')"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                       {{ $groupBy === 'range'
                                           ? 'bg-white text-emerald-600 shadow-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Khoảng
                            </button>
                        </div>

                        @if($groupBy === 'range')
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="sr-only" for="attendanceFrom">Từ ngày</label>
                                <input id="attendanceFrom" type="date" wire:model.lazy="dateFrom"
                                    class="px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm text-slate-700
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <span class="text-xs text-slate-400 font-semibold">→</span>
                            <div>
                                <label class="sr-only" for="attendanceTo">Đến ngày</label>
                                <input id="attendanceTo" type="date" wire:model.lazy="dateTo"
                                    class="px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm text-slate-700
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== EMPTY STATE ===================== --}}
        @if(empty($statusChartData))
        <x-stats.page-empty
            tone="emerald"
            :title="!$selectedNamHoc ? 'Vui lòng chọn năm học' : 'Chưa có dữ liệu điểm danh cho bộ lọc này'"
            description="Hãy điểm danh tại trang điểm danh trước">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </x-slot>

            <a href="{{ route('attendance.show') }}" class="text-emerald-500 underline hover:text-emerald-700">
                trang điểm danh
            </a>
        </x-stats.page-empty>

        @else

        {{-- ===================== SUMMARY CARDS ===================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

            {{-- Tỉ lệ có mặt TB --}}
            <x-stats.stat-card
                label="Tỷ lệ có mặt TB"
                :value="($summary['avg_rate'] ?? 0)"
                suffix="%"
                :subline="'trên ' . ($summary['total_sessions'] ?? 0) . ' buổi'"
                :value-class="(($summary['avg_rate'] ?? 0) >= 80 ? 'text-emerald-600' : ((($summary['avg_rate'] ?? 0) >= 60) ? 'text-amber-500' : 'text-red-500'))" />

            {{-- Tổng học sinh --}}
            <x-stats.stat-card
                :label="($summary['classes_count'] ?? 0) > 1 ? (($summary['classes_count'] ?? 0) . ' lớp') : 'Học sinh'"
                :value="($summary['total_students'] ?? 0)"
                subline="học sinh đang học"
                value-class="text-slate-800" />

            {{-- Buổi tốt nhất --}}
            <x-stats.stat-card
                label="Buổi tốt nhất"
                :value="data_get($summary, 'max_session.rate', '—')"
                :suffix="data_get($summary, 'max_session.rate') !== null ? '%' : null"
                :subline="data_get($summary, 'max_session.label') ? ('ngày ' . data_get($summary, 'max_session.label')) : null"
                :value-class="data_get($summary, 'max_session.rate') !== null ? 'text-emerald-600' : 'text-slate-300'" />

            {{-- Buổi thấp nhất --}}
            <x-stats.stat-card
                label="Buổi thấp nhất"
                :value="data_get($summary, 'min_session.rate', '—')"
                :suffix="data_get($summary, 'min_session.rate') !== null ? '%' : null"
                :subline="data_get($summary, 'min_session.label') ? ('ngày ' . data_get($summary, 'min_session.label')) : null"
                :value-class="data_get($summary, 'min_session.rate') !== null ? 'text-red-500' : 'text-slate-300'" />
        </div>

        {{-- ===================== BIỂU ĐỒ ===================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ===== DONUT: PHÂN BỐ TRẠNG THÁI (2 cột) ===== --}}
            <x-stats.chart-card class="lg:col-span-2" title="Phân bố trạng thái" :right="($summary['total_slots'] ?? 0) . ' lượt'">
                    <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center gap-6">
                        {{-- Donut --}}
                        <div class="relative flex-shrink-0 w-44 h-44">
                            <canvas id="statusDonutChart" width="176" height="176"></canvas>
                            {{-- Center --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-extrabold text-slate-800">
                                    {{ $summary['avg_rate'] ?? 0 }}%
                                </span>
                                <span class="text-xs text-slate-400">có mặt</span>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex-1 space-y-3 w-full">
                            @foreach($statusChartData as $item)
                            @php
                                $dotClass = match ($item['color'] ?? '') {
                                    '#10b981' => 'bg-emerald-500',
                                    '#f59e0b' => 'bg-amber-400',
                                    '#ef4444' => 'bg-red-500',
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
                                    <span class="text-sm font-bold text-slate-800">{{ $item['count'] }}</span>
                                    <span class="text-xs text-slate-400 w-10 text-right">{{ $item['percentage'] }}%</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
            </x-stats.chart-card>

            {{-- ===== LINE CHART: XU HƯỚNG (3 cột) ===== --}}
            <x-stats.chart-card
                class="lg:col-span-3"
                :title="sprintf('Tỷ lệ có mặt theo %s', $groupBy === 'week' ? 'tuần' : ($groupBy === 'month' ? 'tháng' : 'ngày'))"
                :right="count($trendChartData) . ' mốc'">
                    @if(count($trendChartData) < 2)
                    <div class="flex items-center justify-center h-48 text-slate-400 text-sm">
                        Cần ít nhất 2 mốc để hiển thị xu hướng
                    </div>
                    @else
                    <canvas id="trendLineChart"></canvas>
                    @endif
            </x-stats.chart-card>
        </div>

        {{-- ===================== CHÚ THÍCH ===================== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Ghi chú:</span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    Có mặt = đúng giờ / có điểm danh status 1
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                    Vắng có phép = status 2 (có ghi lý do)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                    Vắng không phép = status 3
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-slate-300 inline-block"></span>
                    Chưa điểm danh = chưa có bản ghi
                </span>
            </div>
        </div>

        @endif {{-- end empty check --}}

        @if($selectedNamHoc)
        <div id="attendance-stats-chart-payload" class="hidden" aria-hidden="true">@json(['status' => $statusChartData, 'trend' => $trendChartData])</div>
        @endif
    </div>
</div>

@include('livewire.partials.chart-livewire-bridge')

@push('scripts')
<script>
(function () {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color       = '#64748b';

    window.qlgxChartBridge.register('attendance-stats', {
        payloadId: 'attendance-stats-chart-payload',
        canvasIds: ['statusDonutChart', 'trendLineChart'],
        render(payload) {
            const statusData = payload.status || [];
            const trendData  = payload.trend || [];

            const donutCanvas = document.getElementById('statusDonutChart');
            if (donutCanvas && statusData.length) {
                const nonZero = statusData.filter(d => d.count > 0);
                if (nonZero.length) {
                    new Chart(donutCanvas, {
                        type: 'doughnut',
                        data: {
                            labels:   nonZero.map(d => d.label),
                            datasets: [{
                                data:            nonZero.map(d => d.count),
                                backgroundColor: nonZero.map(d => d.color),
                                borderColor:     '#ffffff',
                                borderWidth:     3,
                                hoverOffset:     6,
                            }]
                        },
                        options: {
                            cutout: '68%',
                            responsive: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => {
                                            const d = nonZero[ctx.dataIndex];
                                            return ` ${d.label}: ${d.count} lượt (${d.percentage}%)`;
                                        }
                                    }
                                }
                            },
                            animation: { duration: 800, easing: 'easeOutQuart' }
                        }
                    });
                }
            }

            const lineCanvas = document.getElementById('trendLineChart');
            if (!lineCanvas || trendData.length < 2) return;

            const ctx = lineCanvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0,   'rgba(16, 185, 129, 0.25)');
            gradient.addColorStop(0.7, 'rgba(16, 185, 129, 0.04)');
            gradient.addColorStop(1,   'rgba(16, 185, 129, 0)');

            const pointColors = trendData.map(d =>
                d.rate >= 80 ? '#10b981' :
                d.rate >= 60 ? '#f59e0b' : '#ef4444'
            );

            new Chart(lineCanvas, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.label),
                    datasets: [{
                        label:                'Tỷ lệ có mặt',
                        data:                 trendData.map(d => d.rate),
                        borderColor:          '#10b981',
                        borderWidth:          2.5,
                        backgroundColor:      gradient,
                        fill:                 true,
                        tension:              0.35,
                        pointBackgroundColor: pointColors,
                        pointBorderColor:     '#ffffff',
                        pointBorderWidth:     2,
                        pointRadius:          trendData.length <= 20 ? 5 : 3,
                        pointHoverRadius:     7,
                    }]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` Có mặt: ${ctx.raw}%`,
                                afterLabel: ctx => {
                                    const rate = ctx.raw;
                                    return rate >= 80 ? '  ✓ Tốt' :
                                           rate >= 60 ? '  ⚠ Trung bình' : '  ✕ Thấp';
                                }
                            }
                        },
                    },
                    scales: {
                        x: {
                            grid:  { display: false },
                            ticks: { font: { size: 11 }, maxRotation: 45, maxTicksLimit: 20 },
                        },
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                font:     { size: 11 },
                                callback: v => v + '%',
                            },
                            grid: { color: '#f1f5f9' },
                            title: {
                                display: true,
                                text:    'Tỷ lệ có mặt (%)',
                                font:    { size: 11 },
                                color:   '#94a3b8',
                            }
                        }
                    },
                    animation: { duration: 800, easing: 'easeOutQuart' }
                }
            });
        },
    });
})();
</script>
@endpush