{{-- Danh sách thẻ học sinh cho giáo lý viên trên điện thoại --}}
<div class="p-4 space-y-3" wire:key="score-cards-{{ $selectedLop }}-{{ $selectedSemester }}">
    @forelse($students as $index => $sc)
    @php
        $avg = $this->getAverage($sc->pivot_id);
        $missingReason = $this->getMissingReason($sc->pivot_id);
        $ratingLabel = \App\Support\StudentRating::labelFor($avg) ?: null;
        $badgeClass  = \App\Support\StudentRating::badgeClassFor($avg);
        $initials = strtoupper(
            mb_substr($sc->last_name ?? '', 0, 1) . mb_substr($sc->first_name ?? '', 0, 1)
        );
    @endphp
    <button type="button"
        wire:key="score-card-{{ $sc->pivot_id }}"
        wire:click="openStudentScoreDetail({{ $sc->pivot_id }})"
        class="w-full text-left bg-white/70 rounded-xl border border-black/[0.06] p-4
              flex items-center gap-3 hover:border-primary-300/50
              hover:bg-black/[0.02] transition-all active:scale-[0.99]">

        <x-entity-avatar
            :src="!empty($sc->avatar_path) ? $sc->avatar_url : null"
            :name="trim(($sc->last_name ?? '') . ' ' . ($sc->first_name ?? ''))"
            :initials="$initials"
            size="xl" />

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
                <span class="text-sm font-medium text-slate-900 truncate">
                    {{ $sc->saint->name ?? '' }} {{ $sc->last_name }} {{ $sc->first_name }}
                </span>
            </div>
            @if($ratingLabel)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                {{ $ratingLabel }}
            </span>
            @else
            <span class="text-xs text-slate-400">{{ $missingReason ?? 'Chưa có điểm trung bình' }}</span>
            @endif
        </div>

        <div class="flex-shrink-0 flex items-center gap-2">
            @if($avg !== null)
            <span class="text-base font-bold tabular-nums
                {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                {{ number_format($avg, 1) }}
            </span>
            @else
            <span class="text-xs font-semibold text-slate-400 tabular-nums">TB —</span>
            @endif
            <span class="text-[11px] font-semibold text-primary-600 bg-primary-50/90
                ring-1 ring-primary-100/70 rounded-lg px-2 py-1">
                Chi tiết
            </span>
        </div>
    </button>
    @empty
    <x-stats.page-empty
        :panel="false"
        title="Chưa có học sinh trong lớp này"
        description="Chọn lớp khác hoặc liên hệ ban giáo lý">
        <x-slot name="icon">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </x-slot>
    </x-stats.page-empty>
    @endforelse

    @if($students->hasPages())
    <div class="pt-2">
        <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50]" />
    </div>
    @endif
</div>
