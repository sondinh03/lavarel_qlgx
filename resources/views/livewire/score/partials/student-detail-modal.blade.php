{{-- Chi tiết điểm một học sinh, dùng cho giáo lý viên trên điện thoại --}}
@php
    $sc = $viewingStudent;
    $avg = $this->getAverage($sc->pivot_id);
    $breakdown = $this->getBreakdown($sc->pivot_id);
    $missingReason = $this->getMissingReason($sc->pivot_id);
    $ratingLabel = \App\Support\StudentRating::labelFor($avg) ?: null;
    $badgeClass  = \App\Support\StudentRating::badgeClassFor($avg);
    $initials = strtoupper(
        mb_substr($sc->last_name ?? '', 0, 1) . mb_substr($sc->first_name ?? '', 0, 1)
    );
@endphp
<div class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    role="dialog" aria-modal="true"
    wire:key="score-detail-{{ $sc->pivot_id }}">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeStudentScoreDetail"></div>
    <div class="relative w-full max-w-md max-h-[min(85vh,calc(100vh-2rem-var(--bottom-offset,0px)))] overflow-y-auto
        bg-white rounded-2xl shadow-mac border border-black/[0.06] p-5 space-y-4">
        <div class="flex items-start gap-3">
            <x-entity-avatar
                :src="!empty($sc->avatar_path) ? $sc->avatar_url : null"
                :name="trim(($sc->last_name ?? '') . ' ' . ($sc->first_name ?? ''))"
                :initials="$initials"
                size="2xl" />
            <div class="flex-1 min-w-0">
                <p class="text-base font-semibold text-slate-900 truncate">
                    {{ $sc->saint->name ?? '' }} {{ $sc->last_name }} {{ $sc->first_name }}
                </p>
                <div class="mt-1 flex items-center gap-2 flex-wrap">
                    @if($avg !== null)
                    <span class="text-xl font-bold tabular-nums
                        {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                        {{ number_format($avg, 1) }}
                    </span>
                    @if($ratingLabel)
                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                        {{ $ratingLabel }}
                    </span>
                    @endif
                    @else
                    <span class="text-sm font-semibold text-slate-400">TB —</span>
                    <span class="text-xs text-slate-400">{{ $missingReason ?? 'Chưa có điểm trung bình' }}</span>
                    @endif
                </div>
                @if($avg === null)
                <p class="mt-1.5 text-[11px] text-slate-400 leading-snug">
                    Điểm trung bình chỉ tính khi đã đủ các thành phần: điểm trung bình học tập cần điểm giữa kỳ và cuối kỳ
                    (nếu lớp có cấu hình), điểm chuyên cần cần có buổi đã điểm danh trong kỳ.
                </p>
                @endif
            </div>
            <button type="button" wire:click="closeStudentScoreDetail"
                class="p-2 -mr-1 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-2.5 pt-1 border-t border-black/[0.06]">
            @foreach($activeScoreTypes as $type)
            <div class="flex items-center justify-between gap-3 py-1">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $type->name }}</p>
                    <p class="text-[11px] text-slate-400">Hệ số {{ $type->coefficient }}</p>
                </div>
                @if($canEditScores)
                <input
                    type="text"
                    inputmode="decimal"
                    step="0.5"
                    min="0"
                    max="{{ $type->max_score }}"
                    wire:model.defer="draftScores.{{ $sc->pivot_id }}.{{ $type->id }}"
                    class="score-input w-16 py-2 px-2 text-center rounded-lg text-sm font-semibold
                           border shadow-mac-sm outline-none
                           focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40
                           {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                               ? 'border-emerald-200/80 bg-emerald-50/80 text-emerald-700'
                               : 'border-black/[0.06] bg-white text-slate-600' }}" />
                @else
                @php
                    $cell = $scoresMatrix[$sc->pivot_id][$type->id]['value'] ?? null;
                @endphp
                <span @class([
                    'inline-flex min-w-[2.75rem] justify-center px-2.5 py-1.5 rounded-lg text-sm font-semibold',
                    'bg-emerald-50/80 text-emerald-700' => $cell !== null,
                    'bg-slate-50 text-slate-300' => $cell === null,
                ])>
                    {{ $cell !== null ? number_format((float) $cell, 1) : '—' }}
                </span>
                @endif
            </div>
            @endforeach

            @if($columns['class_attendance'] || $columns['mass_attendance'])
            <div class="pt-2 mt-1 border-t border-black/[0.06] space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700">Trung bình học tập</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $this->weightLabel($gradingSettings->weight_academic) }}% của TB kỳ
                        </p>
                    </div>
                    <span class="text-sm font-semibold {{ $breakdown['academic'] !== null ? 'text-slate-700' : 'text-slate-300' }}">
                        {{ $breakdown['academic'] !== null ? number_format($breakdown['academic'], 1) : '—' }}
                    </span>
                </div>

                @if($columns['class_attendance'])
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700">Chuyên cần học</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $this->weightLabel($gradingSettings->weight_class_attendance) }}% của TB kỳ
                        </p>
                    </div>
                    <span class="text-sm font-semibold {{ $breakdown['class_attendance'] !== null ? 'text-sky-700' : 'text-slate-300' }}">
                        {{ $breakdown['class_attendance'] !== null ? number_format($breakdown['class_attendance'], 1) : '—' }}
                    </span>
                </div>
                @endif

                @if($columns['mass_attendance'])
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700">Chuyên cần lễ</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $this->weightLabel($gradingSettings->weight_mass_attendance) }}% của TB kỳ
                        </p>
                    </div>
                    <span class="text-sm font-semibold {{ $breakdown['mass_attendance'] !== null ? 'text-sky-700' : 'text-slate-300' }}">
                        {{ $breakdown['mass_attendance'] !== null ? number_format($breakdown['mass_attendance'], 1) : '—' }}
                    </span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="flex gap-2 pt-1">
            <x-button type="button" variant="outline" class="flex-1" wire:click="closeStudentScoreDetail">
                Đóng
            </x-button>
            @if($canEditScores)
            <x-button type="button" variant="primary" class="flex-1" wire:click="saveAllScores">
                <x-icon name="save" />
                Lưu điểm
            </x-button>
            @endif
        </div>
    </div>
</div>
