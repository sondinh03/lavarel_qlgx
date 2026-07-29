{{-- Bảng điểm đầy đủ cho ban giáo lý --}}
<div class="px-4 lg:px-6 py-2.5 mac-hairline-b bg-white/20 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500">
    <span class="font-semibold text-slate-600">Điểm trung bình học kỳ =</span>
    <span>{{ $this->averageFormula() }}</span>
    @if($canManageScoreConfig)
    <button type="button" wire:click="switchTab('weights')"
        class="font-semibold text-primary-600 hover:underline">
        Sửa cách tính
    </button>
    @endif
</div>

<div class="max-h-[70vh] overflow-y-auto">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50/50 sticky top-0 z-10 mac-hairline-b">
                <tr>
                    <x-table-header>STT</x-table-header>
                    <x-table-header>Tên thánh</x-table-header>
                    <x-table-header>Họ & tên đệm</x-table-header>
                    <x-table-header
                        :sortable="true" sort-field="first_name"
                        :current-sort="$sortField" :sort-direction="$sortDirection">
                        Tên
                    </x-table-header>

                    @foreach($activeScoreTypes as $type)
                    <x-table-header align="center" class="min-w-[90px]">
                        <x-tooltip content="Hệ số: {{ $type->coefficient }}" class="w-full justify-center">
                            <span class="block text-center">{{ $type->name }}</span>
                        </x-tooltip>
                    </x-table-header>
                    @endforeach

                    @if($columns['academic'])
                    <x-table-header align="center" class="min-w-[90px] bg-sky-50/60 text-sky-700">
                        <x-tooltip content="Trung bình có hệ số của các cột điểm" class="w-full justify-center">
                            <span class="block text-center leading-tight">Trung bình<br>học tập</span>
                        </x-tooltip>
                    </x-table-header>
                    @endif

                    @if($columns['class_attendance'])
                    <x-table-header align="center" class="min-w-[90px] bg-sky-50/60 text-sky-700">
                        <x-tooltip content="Điểm chuyên cần đi học, tính từ điểm danh buổi học" class="w-full justify-center">
                            <span class="block text-center leading-tight">Chuyên cần<br>học</span>
                        </x-tooltip>
                    </x-table-header>
                    @endif

                    @if($columns['mass_attendance'])
                    <x-table-header align="center" class="min-w-[90px] bg-sky-50/60 text-sky-700">
                        <x-tooltip content="Điểm chuyên cần đi lễ, tính từ điểm danh thánh lễ" class="w-full justify-center">
                            <span class="block text-center leading-tight">Chuyên cần<br>lễ</span>
                        </x-tooltip>
                    </x-table-header>
                    @endif

                    <x-table-header
                        :sortable="true" sort-field="avg"
                        :current-sort="$sortField" :sort-direction="$sortDirection"
                        align="center"
                        class="bg-primary-50/80 text-primary-700 font-bold">
                        Điểm<br>trung bình
                    </x-table-header>

                    <x-table-header align="center" class="bg-primary-50/80 text-primary-700 font-bold">
                        Xếp loại
                    </x-table-header>
                </tr>
            </thead>

            <tbody class="divide-y divide-black/[0.04]">
                @forelse($students as $index => $sc)
                @php
                    $avg = $this->getAverage($sc->pivot_id);
                    $breakdown = $this->getBreakdown($sc->pivot_id);
                    $missingReason = $this->getMissingReason($sc->pivot_id);
                    $ratingLabel = \App\Support\StudentRating::labelFor($avg) ?: null;
                    $badgeClass  = \App\Support\StudentRating::badgeClassFor($avg);
                @endphp
                <tr class="hover:bg-black/[0.03] transition-colors" wire:key="sc-{{ $sc->pivot_id }}">

                    <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white/95 backdrop-blur-sm">
                        {{ ($students->firstItem() ?? 0) + $index }}
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-900">
                        {{ $sc->saint->name ?? '—' }}
                    </td>

                    <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                        {{ $sc->last_name }}
                    </td>

                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                        {{ $sc->first_name }}
                    </td>

                    @foreach($activeScoreTypes as $colIndex => $type)
                    <td class="px-3 py-2 text-center">
                        @if($canEditScores)
                        <input
                            type="text"
                            inputmode="decimal"
                            step="0.5"
                            min="0"
                            max="{{ $type->max_score }}"
                            wire:model.defer="draftScores.{{ $sc->pivot_id }}.{{ $type->id }}"
                            data-row="{{ $index }}"
                            data-col="{{ $colIndex }}"
                            class="score-input w-14 py-1.5 px-2 text-center rounded-lg text-sm font-semibold
                                   border shadow-mac-sm transition-all outline-none placeholder:text-slate-300
                                   focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40
                                   [appearance:textfield]
                                   [&::-webkit-outer-spin-button]:appearance-none
                                   [&::-webkit-inner-spin-button]:appearance-none
                                   {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                                       ? 'border-emerald-200/80 bg-emerald-50/80 text-emerald-700'
                                       : 'border-black/[0.06] bg-white/80 text-slate-600' }}" />
                        @else
                        @php
                            $cell = $scoresMatrix[$sc->pivot_id][$type->id]['value'] ?? null;
                        @endphp
                        <span @class([
                            'inline-flex min-w-[2.5rem] justify-center px-2 py-1 rounded-lg text-sm font-semibold',
                            'bg-emerald-50/80 text-emerald-700' => $cell !== null,
                            'text-slate-300' => $cell === null,
                        ])>
                            {{ $cell !== null ? number_format((float) $cell, 1) : '—' }}
                        </span>
                        @endif
                    </td>
                    @endforeach

                    @foreach(['academic', 'class_attendance', 'mass_attendance'] as $component)
                    @if($columns[$component])
                    <td class="px-3 py-2 text-center bg-sky-50/30">
                        @if($breakdown[$component] !== null)
                        <span class="inline-flex min-w-[2.5rem] justify-center px-2 py-1 rounded-lg text-sm font-semibold bg-sky-50/80 text-sky-700">
                            {{ number_format($breakdown[$component], 1) }}
                        </span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    @endif
                    @endforeach

                    {{-- Điểm TB --}}
                    <td class="px-4 py-3 text-center bg-primary-50/50">
                        @if($avg !== null)
                        <span class="font-bold text-lg tracking-tight
                             {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                            {{ number_format($avg, 1) }}
                        </span>
                        @elseif($missingReason)
                        <x-tooltip content="{{ $missingReason }}">
                            <span class="text-slate-300 text-lg font-bold cursor-help">—</span>
                        </x-tooltip>
                        @else
                        <span class="text-slate-300 text-lg font-bold">—</span>
                        @endif
                    </td>

                    {{-- Xếp loại --}}
                    <td class="px-4 py-3 text-center bg-primary-50/50">
                        @if($ratingLabel)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                            {{ $ratingLabel }}
                        </span>
                        @else
                        <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 6 + $activeScoreTypes->count() + count(array_filter($columns)) }}"
                        class="px-6 py-12 text-center text-slate-400">
                        Chưa có học sinh trong lớp này
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($students->hasPages())
<div class="mac-hairline-t">
    <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50, 100]" />
</div>
@endif
