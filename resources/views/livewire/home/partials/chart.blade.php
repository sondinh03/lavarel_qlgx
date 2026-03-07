{{--
    resources/views/livewire/home/partials/charts.blade.php

    2 biểu đồ dashboard:
      1. Bar chart  — Học sinh theo khối
      2. Line chart — Tiến độ điểm danh theo tuần

    Yêu cầu:
      - Chart.js (CDN) + Alpine.js (có sẵn trong stack Livewire)
      - Livewire truyền dữ liệu qua $studentsByGrade và $attendanceByWeek
      - Tự destroy/re-init chart khi Livewire re-render (tránh "canvas already in use")

    Data shape mong đợi từ Home.php:
      $studentsByGrade   = [['grade' => 'Chiên Con', 'count' => 24], ...]
      $weeklyAttendance  = [
          ['week' => 'T1', 'rate' => 85],
          ['week' => 'T2', 'rate' => 90],
          ...
      ]
--}}

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- ================================================================
         CARD 1: Học sinh theo khối (Bar chart)
    ================================================================ --}}
    <div
        class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        x-data="studentsByGradeChart(@js($studentsByGrade))"
        x-init="init()"
        wire:key="chart-students-by-grade"
        {{-- Re-init khi Livewire cập nhật dữ liệu --}}
        wire:hook.after-update="refresh(@js($studentsByGrade))">

        {{-- Header --}}
        <div class="px-6 pt-5 pb-4 flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                    Học sinh theo khối
                </h3>
                <p class="mt-0.5 text-2xl font-bold text-slate-900">
                    {{ array_sum(array_column($studentsByGrade, 'count')) }}
                    <span class="text-sm font-normal text-slate-400 ml-1">tổng</span>
                </p>
            </div>

            {{-- Icon --}}
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>

        {{-- Chart --}}
        <div class="px-4 pb-5">
            @if(count($studentsByGrade) > 0)
            <div class="relative h-52">
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>
            @else
            <div class="h-52 flex flex-col items-center justify-center text-slate-400 gap-2">
                <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm">Chưa có dữ liệu</span>
            </div>
            @endif
        </div>

        {{-- Legend (mini) --}}
        @if(count($studentsByGrade) > 0)
        <div class="px-6 pb-5 flex flex-wrap gap-x-4 gap-y-1.5 border-t border-slate-100 pt-4">
            @foreach($studentsByGrade as $item)
            <div class="flex items-center gap-1.5 text-xs text-slate-600">
                <span class="inline-block w-2.5 h-2.5 rounded-sm bg-violet-500 opacity-90"
                    style="opacity: {{ 0.4 + (0.6 * ($loop->index / max(count($studentsByGrade) - 1, 1))) }}"></span>
                <span class="font-medium">{{ $item['grade'] }}</span>
                <span class="text-slate-400">({{ $item['count'] }})</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ================================================================
         CARD 2: Tiến độ điểm danh theo tuần (Line chart)
    ================================================================ --}}
    <div
        class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        x-data="attendanceLineChart(@js($weeklyAttendance ?? []))"
        x-init="init()"
        wire:key="chart-attendance-weekly"
        wire:hook.after-update="refresh(@js($weeklyAttendance ?? []))">

        {{-- Header --}}
        <div class="px-6 pt-5 pb-4 flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                    Điểm danh theo tuần
                </h3>
                @php
                $latestRate = !empty($weeklyAttendance)
                ? end($weeklyAttendance)['rate']
                : null;
                @endphp
                <p class="mt-0.5 text-2xl font-bold text-slate-900">
                    @if($latestRate !== null)
                    {{ number_format($latestRate, 1) }}<span class="text-sm font-normal text-slate-400 ml-0.5">%</span>
                    <span class="text-sm font-normal text-slate-400 ml-1">tuần gần nhất</span>
                    @else
                    <span class="text-lg font-semibold text-slate-400">Chưa có dữ liệu</span>
                    @endif
                </p>
            </div>

            {{-- Trend badge --}}
            @if(!empty($weeklyAttendance) && count($weeklyAttendance) >= 2)
            @php
            $last = $weeklyAttendance[count($weeklyAttendance) - 1]['rate'];
            $prev = $weeklyAttendance[count($weeklyAttendance) - 2]['rate'];
            $delta = $last - $prev;
            @endphp
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $delta >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                @if($delta >= 0)
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                </svg>
                +{{ number_format($delta, 1) }}%
                @else
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
                {{ number_format($delta, 1) }}%
                @endif
            </div>
            @else
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            @endif
        </div>

        {{-- Chart --}}
        <div class="px-4 pb-5">
            @if(!empty($weeklyAttendance))
            <div class="relative h-52">
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>
            @else
            <div class="h-52 flex flex-col items-center justify-center text-slate-400 gap-2">
                <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="text-sm">Chưa có dữ liệu điểm danh</span>
                <span class="text-xs text-slate-300">Dữ liệu sẽ hiển thị khi có điểm danh</span>
            </div>
            @endif
        </div>

        {{-- Footer summary --}}
        @if(!empty($weeklyAttendance))
        @php
        $rates = array_column($weeklyAttendance, 'rate');
        $avgRate = count($rates) > 0 ? array_sum($rates) / count($rates) : 0;
        $maxRate = count($rates) > 0 ? max($rates) : 0;
        @endphp
        <div class="px-6 pb-5 border-t border-slate-100 pt-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-slate-400 font-medium">Trung bình</p>
                <p class="text-sm font-bold text-slate-700">{{ number_format($avgRate, 1) }}%</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Cao nhất</p>
                <p class="text-sm font-bold text-emerald-600">{{ number_format($maxRate, 1) }}%</p>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- ================================================================
     Chart.js CDN — load 1 lần, guard để không load lại
================================================================ --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // ---------------------------------------------------------------
    // Shared helper: destroy chart instance an toàn
    // ---------------------------------------------------------------
    function destroyChart(ref) {
        const existing = Chart.getChart(ref);
        if (existing) existing.destroy();
    }

    // ---------------------------------------------------------------
    // Chart 1: Học sinh theo khối (Bar)
    // ---------------------------------------------------------------
    function studentsByGradeChart(data) {
        return {
            chartInstance: null,

            init() {
                this.$nextTick(() => this.render(data));
            },

            refresh(newData) {
                this.render(newData);
            },

            render(data) {
                if (!data || data.length === 0) return;

                destroyChart(this.$refs.canvas);

                const labels = data.map(d => d.grade);
                const counts = data.map(d => d.count);
                const total = counts.reduce((a, b) => a + b, 0);

                // Gradient violet → indigo theo từng bar
                const colors = data.map((_, i) => {
                    const t = data.length <= 1 ? 1 : i / (data.length - 1);
                    const r = Math.round(139 + (99 - 139) * t); // violet-500 → indigo-500
                    const g = Math.round(92 + (102 - 92) * t);
                    const b = Math.round(246 + (241 - 246) * t);
                    return `rgba(${r},${g},${b},0.85)`;
                });

                this.chartInstance = new Chart(this.$refs.canvas, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Học sinh',
                            data: counts,
                            backgroundColor: colors,
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.65,
                            categoryPercentage: 0.8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#94a3b8',
                                bodyColor: '#f1f5f9',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: ctx => {
                                        const pct = total > 0 ?
                                            ((ctx.parsed.y / total) * 100).toFixed(1) :
                                            0;
                                        return ` ${ctx.parsed.y} học sinh (${pct}%)`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    },
                                    maxRotation: 30,
                                },
                            },
                            y: {
                                grid: {
                                    color: '#f1f5f9',
                                    drawBorder: false,
                                },
                                border: {
                                    display: false,
                                    dash: [4, 4]
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        size: 11
                                    },
                                    stepSize: 1,
                                    precision: 0,
                                },
                                beginAtZero: true,
                            },
                        },
                        animation: {
                            duration: 600,
                            easing: 'easeOutQuart',
                        },
                    },
                });
            },
        };
    }

    // ---------------------------------------------------------------
    // Chart 2: Điểm danh theo tuần (Line)
    // ---------------------------------------------------------------
    function attendanceLineChart(data) {
        return {
            chartInstance: null,

            init() {
                this.$nextTick(() => this.render(data));
            },

            refresh(newData) {
                this.render(newData);
            },

            render(data) {
                if (!data || data.length === 0) return;

                destroyChart(this.$refs.canvas);

                const labels = data.map(d => d.week);
                const rates = data.map(d => d.rate);

                this.chartInstance = new Chart(this.$refs.canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Tỷ lệ điểm danh (%)',
                            data: rates,
                            borderColor: '#10b981', // emerald-500
                            backgroundColor: ctx => {
                                const chart = ctx.chart;
                                const {
                                    ctx: c,
                                    chartArea
                                } = chart;
                                if (!chartArea) return 'transparent';

                                const gradient = c.createLinearGradient(
                                    0, chartArea.top, 0, chartArea.bottom
                                );
                                gradient.addColorStop(0, 'rgba(16,185,129,0.25)');
                                gradient.addColorStop(0.6, 'rgba(16,185,129,0.06)');
                                gradient.addColorStop(1, 'rgba(16,185,129,0)');
                                return gradient;
                            },
                            borderWidth: 2.5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            fill: true,
                            tension: 0.4,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#94a3b8',
                                bodyColor: '#f1f5f9',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: ctx => ` ${ctx.parsed.y}% điểm danh`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    },
                                },
                            },
                            y: {
                                min: 0,
                                max: 100,
                                grid: {
                                    color: '#f1f5f9'
                                },
                                border: {
                                    display: false,
                                    dash: [4, 4]
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        size: 11
                                    },
                                    stepSize: 20,
                                    callback: v => `${v}%`,
                                },
                            },
                        },
                        animation: {
                            duration: 700,
                            easing: 'easeOutCubic',
                        },
                    },
                });
            },
        };
    }
</script>
@endpush
@endonce