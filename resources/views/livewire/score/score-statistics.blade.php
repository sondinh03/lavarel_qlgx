@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ',    'url' => route('dashboard')],
        ['label' => 'Quản lý điểm', 'url' => route('scores.index')],
        ['label' => 'Thống kê điểm'],
    ]" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ===================== HEADER ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Title row --}}
            <div class="px-6 py-5 flex items-center justify-between gap-4 flex-wrap border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Thống kê điểm</h1>
                        <p class="text-sm text-slate-500">Phạm vi tự động theo bộ lọc lớp / khối</p>
                    </div>
                </div>

                <a href="{{ route('scores.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300
                          text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Quay lại bảng điểm
                </a>
            </div>

            {{-- Filters --}}
            <div class="px-6 py-4 bg-slate-50/60 border-b border-slate-100">
                <livewire:filters.filter-bar
                    :parish-id="$parishId"
                    :show-nam-hoc="true"
                    :show-khoi="true"
                    :show-lop="true"
                    :show-ky="true"
                    :selected-nam-hoc="$selectedNamHoc"
                    :selected-khoi="$selectedKhoi"
                    :selected-lop="$selectedLop"
                    :selected-ky="$selectedSemester" />
            </div>
        </div>

        {{-- ===================== EMPTY STATE ===================== --}}
        @if(empty($ratingChartData) || $totalStudentsWithScore === 0)
        <x-stats.page-empty
            tone="primary"
            :title="!$selectedNamHoc ? 'Vui lòng chọn năm học' : ('Chưa có dữ liệu điểm (' . $scopeLabel . ', ' . $semesterLabel . ')')"
            description="Hãy nhập điểm tại trang quản lý điểm trước">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </x-slot>

            <a href="{{ route('scores.index') }}" class="text-primary-500 underline hover:text-primary-700">trang quản lý điểm</a>
        </x-stats.page-empty>

        @else

        {{-- ===================== SUMMARY CARDS ===================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

            {{-- Điểm trung bình --}}
            <x-stats.stat-card
                label="Điểm trung bình chung"
                :value="number_format($summary['avg'] ?? 0, 2)"
                subline="/ 10.00"
                :value-class="(($summary['avg'] ?? 0) >= 8 ? 'text-emerald-600' : ((($summary['avg'] ?? 0) >= 5) ? 'text-primary-600' : 'text-red-500'))" />

            {{-- Số học sinh có điểm --}}
            <x-stats.stat-card
                label="Học sinh có điểm"
                :value="$totalStudentsWithScore"
                value-class="text-slate-800"
                :subline="($totalStudents > 0 ? round($totalStudentsWithScore / $totalStudents * 100, 1) : 0) . '% đã nhập điểm'">
                <div class="text-lg text-slate-400 font-normal -mt-8 ml-24">
                    / {{ $totalStudents }}
                </div>
            </x-stats.stat-card>

            {{-- Tỉ lệ đạt --}}
            <x-stats.stat-card
                label="Tỉ lệ đạt (≥5)"
                :value="($totalStudentsWithScore > 0 ? round(($summary['pass'] ?? 0) / $totalStudentsWithScore * 100, 1) : 0)"
                suffix="%"
                :subline="($summary['pass'] ?? 0) . ' / ' . $totalStudentsWithScore . ' học sinh'"
                value-class="text-emerald-600" />

            {{-- Cao nhất / Thấp nhất --}}
            <x-stats.stat-card label="Cao nhất / Thấp nhất" :value="null" value-class="text-slate-800" subline="Điểm trung bình cá nhân">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-600">{{ number_format($summary['max'] ?? 0, 1) }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-2xl font-extrabold text-red-500">{{ number_format($summary['min'] ?? 0, 1) }}</span>
                </div>
            </x-stats.stat-card>
        </div>

        {{-- ===================== CHARTS ===================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- DONUT: Phân bố xếp loại --}}
            <x-stats.chart-card title="Phân bố xếp loại" :right="$totalStudentsWithScore . ' học sinh'">
                    <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center gap-6">
                        {{-- Donut --}}
                        <div class="relative flex-shrink-0 w-48 h-48">
                            <canvas id="ratingDonutChart" width="192" height="192"></canvas>
                            {{-- Center --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-extrabold text-slate-800">
                                    {{ number_format($summary['avg'] ?? 0, 1) }}
                                </span>
                                <span class="text-xs text-slate-400">Điểm trung bình</span>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex-1 space-y-3 w-full">
                            @foreach($ratingChartData as $item)
                            @php
                                $dotClass = match ($item['color'] ?? '') {
                                    '#10b981' => 'bg-emerald-500',
                                    '#3b82f6' => 'bg-blue-500',
                                    '#f59e0b' => 'bg-amber-400',
                                    '#eab308' => 'bg-yellow-500',
                                    '#f97316' => 'bg-orange-500',
                                    '#ef4444' => 'bg-red-500',
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

            {{-- BAR CHART: Phân phối điểm --}}
            <x-stats.chart-card title="Phân phối điểm trung bình" right="Phân bố theo khoảng điểm">
                <canvas id="distributionBarChart" height="192"></canvas>
            </x-stats.chart-card>
        </div>

        {{-- ===================== CLASS COMPARISON (nhiều lớp) ===================== --}}
        @if($effectiveScope !== 'class' && !empty($classComparisonData))
        <x-stats.chart-card
            title="So sánh điểm trung bình giữa các lớp ({{ $scopeLabel }}, {{ $semesterLabel }})"
            :right="count($classComparisonData) . ' lớp'">
            <canvas id="classComparisonChart"></canvas>
        </x-stats.chart-card>
        @endif

        {{-- ===================== NOTES ===================== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Ghi chú:</span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    Xuất sắc (9.5-10)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    Giỏi (8-9.5)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                    Khá (6.5-8)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
                    Trung bình (5-6.5)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span>
                    Yếu (3.5-5)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                    Kém (0-3.5)
                </span>
            </div>
        </div>

        @endif {{-- end empty check --}}

        @if($selectedNamHoc)
        <div id="score-stats-chart-payload" class="hidden" aria-hidden="true">@json(['rating' => $ratingChartData, 'distribution' => $distributionChartData, 'comparison' => $classComparisonData])</div>
        @endif

    </div>
</div>

@include('livewire.partials.chart-livewire-bridge')

@push('scripts')
<script>
(function () {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color       = '#64748b';

    window.qlgxChartBridge.register('score-stats', {
        payloadId: 'score-stats-chart-payload',
        canvasIds: ['ratingDonutChart', 'distributionBarChart', 'classComparisonChart'],
        render(payload) {
            const ratingData       = payload.rating || [];
            const distributionData = payload.distribution || [];
            const comparisonData   = payload.comparison || [];

            const donutCanvas = document.getElementById('ratingDonutChart');
            if (donutCanvas && ratingData.length) {
                const filtered = ratingData.filter(d => d.count > 0);
                if (filtered.length) {
                    new Chart(donutCanvas, {
                        type: 'doughnut',
                        data: {
                            labels:   filtered.map(d => d.label),
                            datasets: [{
                                data:            filtered.map(d => d.count),
                                backgroundColor: filtered.map(d => d.color),
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
                                        label: ctx => ` ${ctx.label}: ${ctx.raw} học sinh (${filtered[ctx.dataIndex]?.percentage ?? 0}%)`
                                    }
                                }
                            },
                            animation: { duration: 800, easing: 'easeOutQuart' }
                        }
                    });
                }
            }

            const barCanvas = document.getElementById('distributionBarChart');
            if (barCanvas && distributionData.length) {
                new Chart(barCanvas, {
                    type: 'bar',
                    data: {
                        labels:   distributionData.map(d => d.label),
                        datasets: [{
                            label:           'Số học sinh',
                            data:            distributionData.map(d => d.count),
                            backgroundColor: distributionData.map(d => d.color + 'cc'),
                            borderColor:     distributionData.map(d => d.color),
                            borderWidth:     2,
                            borderRadius:    6,
                            borderSkipped:   false,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: ctx => `Điểm ${ctx[0].label}`,
                                    label: ctx => ` ${ctx.raw} học sinh`,
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } },
                                title: {
                                    display: true,
                                    text: 'Khoảng điểm trung bình',
                                    font: { size: 11 },
                                    color: '#94a3b8',
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 11 } },
                                grid: { color: '#f1f5f9' },
                                title: {
                                    display: true,
                                    text: 'Số học sinh',
                                    font: { size: 11 },
                                    color: '#94a3b8',
                                }
                            }
                        },
                        animation: { duration: 700, easing: 'easeOutQuart' }
                    }
                });
            }

            const compareCanvas = document.getElementById('classComparisonChart');
            if (compareCanvas && comparisonData.length) {
                const colors = comparisonData.map(d =>
                    d.avg >= 8   ? '#10b981' :
                    d.avg >= 6.5 ? '#3b82f6' :
                    d.avg >= 5   ? '#f59e0b' : '#ef4444'
                );

                new Chart(compareCanvas, {
                    type: 'bar',
                    data: {
                        labels:   comparisonData.map(d => d.class_name),
                        datasets: [{
                            label:           'Điểm trung bình',
                            data:            comparisonData.map(d => d.avg),
                            backgroundColor: colors.map(c => c + 'bb'),
                            borderColor:     colors,
                            borderWidth:     2,
                            borderRadius:    6,
                            borderSkipped:   false,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` Điểm trung bình: ${ctx.raw}  |  ${comparisonData[ctx.dataIndex]?.count} học sinh`,
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } },
                            },
                            y: {
                                min: 0,
                                max: 10,
                                ticks: { stepSize: 2, font: { size: 11 } },
                                grid: { color: '#f1f5f9' },
                                title: {
                                    display: true,
                                    text: 'Điểm trung bình',
                                    font: { size: 11 },
                                    color: '#94a3b8',
                                }
                            }
                        },
                        animation: { duration: 700, easing: 'easeOutQuart' }
                    }
                });
            }
        },
    });
})();
</script>
@endpush
