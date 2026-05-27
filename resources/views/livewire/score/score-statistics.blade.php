@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ',    'url' => route('dashboard')],
        ['label' => 'Quản lý điểm', 'url' => route('scores.index')],
        ['label' => 'Thống kê điểm'],
    ]" />
@endsection

@push('styles')
<style>
    /* ===== SCOPE SELECTOR ===== */
    .scope-btn {
        @apply px-5 py-2 rounded-xl text-sm font-semibold transition-all border;
    }
    .scope-btn.active {
        @apply bg-primary-600 text-white border-primary-600 shadow-sm;
    }
    .scope-btn:not(.active) {
        @apply bg-white text-slate-600 border-slate-300 hover:border-primary-400 hover:text-primary-600;
    }

    /* ===== SUMMARY CARDS ===== */
    .stat-card {
        @apply bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-1;
    }

    /* ===== CHART CARD ===== */
    .chart-card {
        @apply bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden;
    }
    .chart-card-header {
        @apply px-6 py-4 border-b border-slate-100 flex items-center justify-between;
    }
    .chart-card-title {
        @apply text-base font-bold text-slate-800;
    }
    .chart-card-body {
        @apply p-6;
    }

    /* ===== COMPARISON TABLE ===== */
    .compare-row {
        @apply flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0;
    }
    .compare-bar {
        @apply h-3 rounded-full transition-all duration-700;
    }
</style>
@endpush

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ===================== HEADER ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between gap-4 flex-wrap border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Thống kê điểm</h1>
                        <p class="text-sm text-slate-500">Phân tích kết quả học tập theo lớp, khối và toàn xứ</p>
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

            {{-- Filter + Scope --}}
            <div class="px-6 py-4 bg-slate-50/60 flex flex-wrap items-center justify-between gap-4">
                {{-- Filter Bar --}}
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

                {{-- Scope Switcher --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide mr-1">Phạm vi:</span>
                    @if($selectedLop)
                    <button wire:click="setScope('class')"
                        class="scope-btn {{ $scope === 'class' ? 'active' : '' }}">
                        Lớp
                    </button>
                    @endif
                    @if($selectedKhoi || $selectedLop)
                    <button wire:click="setScope('grade')"
                        class="scope-btn {{ $scope === 'grade' ? 'active' : '' }}">
                        Khối
                    </button>
                    @endif
                    <button wire:click="setScope('parish')"
                        class="scope-btn {{ $scope === 'parish' ? 'active' : '' }}">
                        Toàn xứ
                    </button>
                </div>
            </div>
        </div>

        {{-- ===================== EMPTY STATE ===================== --}}
        @if(empty($ratingChartData) || $totalStudentsWithScore === 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-16 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="mt-4 text-lg font-semibold text-slate-400">
                @if(!$selectedNamHoc)
                    Vui lòng chọn năm học
                @elseif($scope === 'class' && !$selectedLop)
                    Vui lòng chọn lớp để xem thống kê
                @elseif($scope === 'grade' && !$selectedKhoi)
                    Vui lòng chọn khối để xem thống kê
                @else
                    Chưa có dữ liệu điểm cho phạm vi này
                @endif
            </p>
            <p class="mt-1 text-sm text-slate-400">
                Hãy nhập điểm tại
                <a href="{{ route('scores.index') }}" class="text-primary-500 underline hover:text-primary-700">trang quản lý điểm</a>
                trước
            </p>
        </div>

        @else

        {{-- ===================== SUMMARY CARDS ===================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

            {{-- Điểm TB --}}
            <div class="stat-card">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Điểm TB chung</div>
                <div class="text-3xl font-extrabold
                    {{ ($summary['avg'] ?? 0) >= 8 ? 'text-emerald-600' : (($summary['avg'] ?? 0) >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                    {{ number_format($summary['avg'] ?? 0, 2) }}
                </div>
                <div class="text-xs text-slate-400">/ 10.00</div>
            </div>

            {{-- Số học sinh có điểm --}}
            <div class="stat-card">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Học sinh có điểm</div>
                <div class="text-3xl font-extrabold text-slate-800">
                    {{ $totalStudentsWithScore }}
                    <span class="text-lg text-slate-400 font-normal">/ {{ $totalStudents }}</span>
                </div>
                <div class="text-xs text-slate-400">
                    {{ $totalStudents > 0 ? round($totalStudentsWithScore / $totalStudents * 100, 1) : 0 }}% đã nhập điểm
                </div>
            </div>

            {{-- Tỉ lệ đậu --}}
            <div class="stat-card">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Tỉ lệ đạt (≥5)</div>
                <div class="text-3xl font-extrabold text-emerald-600">
                    {{ $totalStudentsWithScore > 0
                        ? round(($summary['pass'] ?? 0) / $totalStudentsWithScore * 100, 1)
                        : 0 }}%
                </div>
                <div class="text-xs text-slate-400">{{ $summary['pass'] ?? 0 }} / {{ $totalStudentsWithScore }} học sinh</div>
            </div>

            {{-- Cao nhất / Thấp nhất --}}
            <div class="stat-card">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Cao nhất / Thấp nhất</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-600">{{ number_format($summary['max'] ?? 0, 1) }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-2xl font-extrabold text-red-500">{{ number_format($summary['min'] ?? 0, 1) }}</span>
                </div>
                <div class="text-xs text-slate-400">Điểm TB cá nhân</div>
            </div>
        </div>

        {{-- ===================== BIỂU ĐỒ CHÍNH ===================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ===== BIỂU ĐỒ TRÒN XẾP LOẠI ===== --}}
            <div class="chart-card">
                <div class="chart-card-header">
                    <h2 class="chart-card-title">Phân bố xếp loại</h2>
                    <span class="text-xs text-slate-400">{{ $totalStudentsWithScore }} học sinh</span>
                </div>
                <div class="chart-card-body">
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        {{-- Donut canvas --}}
                        <div class="relative flex-shrink-0 w-48 h-48">
                            <canvas id="ratingDonutChart" width="192" height="192"></canvas>
                            {{-- Center label --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-extrabold text-slate-800">
                                    {{ number_format($summary['avg'] ?? 0, 1) }}
                                </span>
                                <span class="text-xs text-slate-400">Điểm TB</span>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex-1 space-y-2 w-full">
                            @foreach($ratingChartData as $item)
                            @if($item['count'] > 0)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0"
                                          style="background:{{ $item['color'] }}"></span>
                                    <span class="text-sm text-slate-700 font-medium truncate">{{ $item['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-bold text-slate-800">{{ $item['count'] }}</span>
                                    <span class="text-xs text-slate-400 w-12 text-right">{{ $item['percentage'] }}%</span>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== BIỂU ĐỒ CỘT PHÂN PHỐI ĐIỂM ===== --}}
            <div class="chart-card">
                <div class="chart-card-header">
                    <h2 class="chart-card-title">Phân phối điểm trung bình</h2>
                    <span class="text-xs text-slate-400">Phân bố theo khoảng điểm</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="distributionBarChart" height="192"></canvas>
                </div>
            </div>
        </div>

        {{-- ===================== SO SÁNH GIỮA CÁC LỚP (scope ≠ class) ===================== --}}
        @if($scope !== 'class' && !empty($classComparisonData))
        <div class="chart-card">
            <div class="chart-card-header">
                <h2 class="chart-card-title">
                    So sánh điểm TB giữa các lớp
                    <span class="text-xs font-normal text-slate-400 ml-2">
                        ({{ $scope === 'grade' ? 'theo khối' : 'toàn xứ' }}, học kỳ {{ $selectedSemester }})
                    </span>
                </h2>
                <span class="text-xs text-slate-400">{{ count($classComparisonData) }} lớp</span>
            </div>
            <div class="chart-card-body">
                {{-- Horizontal bar chart bằng CSS --}}
                <div class="space-y-1">
                    @php
                        $maxAvg = collect($classComparisonData)->max('avg') ?: 10;
                    @endphp
                    @foreach($classComparisonData as $classData)
                    @php
                        $barWidth = $maxAvg > 0 ? round(($classData['avg'] / 10) * 100, 1) : 0;
                        $barColor = $classData['avg'] >= 8 ? '#10b981'
                            : ($classData['avg'] >= 6.5 ? '#3b82f6'
                            : ($classData['avg'] >= 5 ? '#f59e0b' : '#ef4444'));
                    @endphp
                    <div class="compare-row">
                        {{-- Tên lớp --}}
                        <div class="w-24 flex-shrink-0 text-sm font-semibold text-slate-700 text-right pr-2 truncate"
                             title="{{ $classData['class_name'] }}">
                            {{ $classData['class_name'] }}
                        </div>

                        {{-- Bar --}}
                        <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                            <div class="compare-bar h-4 rounded-full"
                                 style="width: {{ $barWidth }}%; background: {{ $barColor }};">
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex-shrink-0 flex items-center gap-3 text-right">
                            <span class="text-sm font-bold text-slate-800 w-10">
                                {{ number_format($classData['avg'], 2) }}
                            </span>
                            <span class="text-xs text-slate-400 hidden sm:inline w-16">
                                {{ $classData['count'] }} hs
                            </span>
                            <span class="text-xs font-semibold w-14 hidden sm:inline
                                {{ $classData['pass_rate'] >= 80 ? 'text-emerald-600' : ($classData['pass_rate'] >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $classData['pass_rate'] }}% đạt
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Canvas chart phụ --}}
                <div class="mt-6">
                    <canvas id="classComparisonChart" height="80"></canvas>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- end empty check --}}

    </div>
</div>

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script> --}}
<script>
    // ============================================================
    //  DỮ LIỆU TỪ LIVEWIRE
    // ============================================================
    const ratingData        = @json($ratingChartData);
    const distributionData  = @json($distributionChartData);
    const comparisonData    = @json($classComparisonData);

    // Palette
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color       = '#64748b';

    // ============================================================
    //  1. DONUT CHART — XẾP LOẠI
    // ============================================================
    (function() {
        const canvas = document.getElementById('ratingDonutChart');
        if (!canvas || !ratingData.length) return;

        const filtered = ratingData.filter(d => d.count > 0);
        if (!filtered.length) return;

        new Chart(canvas, {
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
                cutout:     '68%',
                responsive: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} học sinh (${filtered[ctx.dataIndex]?.percentage ?? 0}%)`
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 800,
                    easing: 'easeOutQuart',
                }
            }
        });
    })();

    // ============================================================
    //  2. BAR CHART — PHÂN PHỐI ĐIỂM
    // ============================================================
    (function() {
        const canvas = document.getElementById('distributionBarChart');
        if (!canvas || !distributionData.length) return;

        new Chart(canvas, {
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
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                        },
                        grid: { color: '#f1f5f9' },
                        title: {
                            display: true,
                            text: 'Số học sinh',
                            font: { size: 11 },
                            color: '#94a3b8',
                        }
                    }
                },
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                }
            }
        });
    })();

    // ============================================================
    //  3. BAR CHART — SO SÁNH CÁC LỚP
    // ============================================================
    (function() {
        const canvas = document.getElementById('classComparisonChart');
        if (!canvas || !comparisonData.length) return;

        const colors = comparisonData.map(d =>
            d.avg >= 8    ? '#10b981' :
            d.avg >= 6.5  ? '#3b82f6' :
            d.avg >= 5    ? '#f59e0b' : '#ef4444'
        );

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels:   comparisonData.map(d => d.class_name),
                datasets: [{
                    label:           'Điểm TB',
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
                            label: ctx => ` Điểm TB: ${ctx.raw}  |  ${comparisonData[ctx.dataIndex]?.count} học sinh`,
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
                        ticks: {
                            stepSize: 2,
                            font: { size: 11 },
                        },
                        grid: { color: '#f1f5f9' },
                        title: {
                            display: true,
                            text: 'Điểm TB',
                            font: { size: 11 },
                            color: '#94a3b8',
                        }
                    }
                },
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                }
            }
        });
    })();

    // ============================================================
    //  RE-RENDER CHARTS SAU KHI LIVEWIRE UPDATE
    // ============================================================
    document.addEventListener('livewire:update', () => {
        // Chart.js instances sẽ bị destroy và tạo lại bởi Livewire re-render
        // Cần delay nhỏ để DOM kịp cập nhật
        setTimeout(() => {
            ['ratingDonutChart', 'distributionBarChart', 'classComparisonChart'].forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) {
                    const existing = Chart.getChart(canvas);
                    if (existing) existing.destroy();
                }
            });
        }, 10);
    });
</script>
@endpush