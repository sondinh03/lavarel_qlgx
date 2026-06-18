@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ',   'url' => route('parish-admin.dashboard')],
        ['label' => 'Học sinh',    'url' => route('students.index')],
        ['label' => 'Thống kê học sinh'],
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
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Thống kê học sinh</h1>
                        <p class="text-sm text-slate-500">Thống kê toàn xứ — số lượng và giới tính theo từng lớp</p>
                    </div>
                </div>

                <a href="{{ route('students.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300
                          text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Quay lại danh sách
                </a>
            </div>

            {{-- Filter: năm học --}}
            <div class="px-6 py-4 bg-slate-50/60 border-b border-slate-100">
                <livewire:filters.filter-bar
                    :parish-id="$parishId"
                    :show-nam-hoc="true"
                    :show-khoi="false"
                    :show-lop="false"
                    :show-ky="false"
                    :selected-nam-hoc="$selectedNamHoc" />
            </div>
        </div>

        {{-- ===================== EMPTY STATE ===================== --}}
        @if(empty($classChartData))
        <x-stats.page-empty
            tone="primary"
            :title="!$selectedNamHoc ? 'Vui lòng chọn năm học' : 'Chưa có lớp hoặc học sinh trong năm học này'"
            description="Hãy thêm học sinh vào lớp tại trang quản lý học sinh">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </x-slot>

            <a href="{{ route('students.index') }}" class="text-primary-500 underline hover:text-primary-700">
                trang quản lý học sinh
            </a>
        </x-stats.page-empty>

        @else

        {{-- ===================== SUMMARY CARDS ===================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

            {{-- Tỷ lệ nam --}}
            <x-stats.stat-card
                label="Tỷ lệ nam"
                :value="($summary['male_rate'] ?? 0)"
                suffix="%"
                :subline="($summary['male_count'] ?? 0) . ' nam / ' . ($summary['female_count'] ?? 0) . ' nữ'"
                value-class="text-primary-600" />

            {{-- Tổng học sinh --}}
            <x-stats.stat-card
                :label="($summary['classes_count'] ?? 0) > 1 ? (($summary['classes_count'] ?? 0) . ' lớp') : 'Học sinh'"
                :value="($summary['total_students'] ?? 0)"
                subline="đang học"
                value-class="text-slate-800" />

            {{-- TB học sinh / lớp --}}
            <x-stats.stat-card
                label="TB học sinh / lớp"
                :value="($summary['avg_per_class'] ?? 0)"
                :subline="'trên ' . ($summary['classes_count'] ?? 0) . ' lớp'"
                value-class="text-primary-600" />

            {{-- Lớp đông / ít nhất --}}
            <x-stats.stat-card label="Lớp đông / ít nhất" :value="null" value-class="text-slate-800">
                @if(($summary['max_class'] ?? null) && ($summary['min_class'] ?? null))
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-600">{{ $summary['max_class']['count'] }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-2xl font-extrabold text-amber-500">{{ $summary['min_class']['count'] }}</span>
                </div>
                <div class="text-xs text-slate-400 truncate" title="{{ $summary['max_class']['label'] }} / {{ $summary['min_class']['label'] }}">
                    {{ $summary['max_class']['label'] }} / {{ $summary['min_class']['label'] }}
                </div>
                @else
                <div class="text-3xl font-extrabold text-slate-300">—</div>
                @endif
            </x-stats.stat-card>
        </div>

        {{-- ===================== BIỂU ĐỒ ===================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ===== DONUT: PHÂN BỐ GIỚI TÍNH (2 cột) ===== --}}
            <x-stats.chart-card class="lg:col-span-2" title="Phân bố giới tính" :right="($summary['total_students'] ?? 0) . ' học sinh'">
                    @if(empty($statusChartData))
                    <div class="flex items-center justify-center h-44 text-slate-400 text-sm">
                        Chưa có học sinh trong năm học này
                    </div>
                    @else
                    <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center gap-6">
                        <div class="relative flex-shrink-0 w-44 h-44">
                            <canvas id="statusDonutChart" width="176" height="176"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-extrabold text-slate-800">
                                    {{ $summary['male_rate'] ?? 0 }}%
                                </span>
                                <span class="text-xs text-slate-400">nam</span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-3 w-full">
                            @foreach($statusChartData as $item)
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
                                    <span class="text-sm font-bold text-slate-800">{{ $item['count'] }}</span>
                                    <span class="text-xs text-slate-400 w-10 text-right">{{ $item['percentage'] }}%</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
            </x-stats.chart-card>

            {{-- ===== BAR CHART: HỌC SINH THEO LỚP (3 cột) ===== --}}
            <x-stats.chart-card class="lg:col-span-3" title="Số học sinh theo lớp" :right="count($classChartData) . ' lớp'">
                    @if(empty($classChartData))
                    <div class="flex items-center justify-center h-48 text-slate-400 text-sm">
                        Chưa có dữ liệu học sinh theo lớp
                    </div>
                    @else
                    <canvas id="classBarChart"></canvas>
                    @endif
            </x-stats.chart-card>
        </div>

        {{-- ===================== CHÚ THÍCH ===================== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Ghi chú:</span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    Nam
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-pink-500 inline-block"></span>
                    Nữ
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-slate-300 inline-block"></span>
                    Chưa xác định giới tính
                </span>
                <span class="text-slate-400">
                    Thống kê toàn xứ, chỉ tính học sinh đang học
                </span>
            </div>
        </div>

        @endif

        @if($selectedNamHoc)
        <div id="student-stats-chart-payload" class="hidden" aria-hidden="true">@json(['status' => $statusChartData, 'classes' => $classChartData])</div>
        @endif
    </div>
</div>

@include('livewire.partials.chart-livewire-bridge')

@push('scripts')
<script>
(function () {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color       = '#64748b';

    window.qlgxChartBridge.register('student-stats', {
        payloadId: 'student-stats-chart-payload',
        canvasIds: ['statusDonutChart', 'classBarChart'],
        render(payload) {
            const statusData = payload.status || [];
            const classData  = payload.classes || [];

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
                                            return ` ${d.label}: ${d.count} học sinh (${d.percentage}%)`;
                                        }
                                    }
                                }
                            },
                            animation: { duration: 800, easing: 'easeOutQuart' }
                        }
                    });
                }
            }

            const barCanvas = document.getElementById('classBarChart');
            if (!barCanvas || !classData.length) return;

            const maxCount = Math.max(...classData.map(d => d.count), 1);
            const barColors = classData.map(d => {
                const ratio = d.count / maxCount;
                return ratio >= 0.8 ? '#34C759' :
                       ratio >= 0.5 ? '#57C37F' : '#94a3b8';
            });

            new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: classData.map(d => d.label),
                    datasets: [{
                        label:           'Học sinh',
                        data:            classData.map(d => d.count),
                        backgroundColor: barColors.map(c => c + 'cc'),
                        borderColor:     barColors,
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
                                label: ctx => ` ${ctx.raw} học sinh`,
                            }
                        },
                    },
                    scales: {
                        x: {
                            grid:  { display: false },
                            ticks: {
                                font:          { size: 11 },
                                maxRotation:   45,
                                maxTicksLimit: 20,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font:     { size: 11 },
                                precision: 0,
                            },
                            grid: { color: '#f1f5f9' },
                            title: {
                                display: true,
                                text:    'Số học sinh',
                                font:    { size: 11 },
                                color:   '#94a3b8',
                            }
                        }
                    },
                    animation: { duration: 700, easing: 'easeOutQuart' }
                }
            });
        },
    });
})();
</script>
@endpush
