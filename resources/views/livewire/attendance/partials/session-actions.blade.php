{{-- Thao tác trạng thái phiên: khóa / mở lại / hủy / khôi phục / xóa --}}
@php
    $layout = $layout ?? 'desktop';
    $subjectTarget = $subjectTarget ?? 'students';
    $selectedNamHoc = $selectedNamHoc ?? null;
    $selectedClassId = $selectedClassId ?? null;
    $canDeleteSessions = $canDeleteSessions ?? false;

    $attendanceUrl = $subjectTarget === 'teachers'
        ? route('attendance.show', [
            'target' => 'teachers',
            'namHoc' => $selectedNamHoc,
            'type'   => $session['type'],
            'date'   => $session['dateStr'],
        ])
        : route('attendance.show', [
            'classId' => $selectedClassId ?? '',
            'type'    => $session['type'],
            'date'    => $session['dateStr'],
        ]);

    $cancelMessage = 'Hủy phiên ngày ' . $session['fullDate']
        . '? Buổi đã hủy không tính vào báo cáo và điểm chuyên cần.';
    $deleteMessage = 'Xóa phiên điểm danh ngày ' . $session['fullDate'] . '?';
@endphp

@if($layout === 'mobile')
    <div class="mt-3 flex flex-wrap items-center gap-2">
        @unless($session['cancelled'] ?? false)
            <a href="{{ $attendanceUrl }}"
                class="flex-1 min-w-[7.5rem] inline-flex items-center justify-center gap-1.5 h-10 rounded-xl
                    bg-primary-50/90 text-primary-700 text-sm font-semibold ring-1 ring-primary-100/80
                    shadow-mac-sm hover:bg-primary-100/90 transition-all active:scale-[0.98]">
                <x-icon name="clipboard" class="w-4 h-4" />
                Điểm danh
            </a>
        @endunless

        @if($session['canLock'] ?? false)
            <button type="button"
                wire:click="lockSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="lockSession({{ $session['id'] }})"
                class="h-10 px-3 rounded-xl text-sm font-semibold
                    bg-amber-50/90 text-amber-700 ring-1 ring-amber-100/80 shadow-mac-sm
                    hover:bg-amber-100/90 transition-all active:scale-[0.98] disabled:opacity-50">
                Khóa
            </button>
        @endif

        @if($session['canReopen'] ?? false)
            <button type="button"
                wire:click="reopenSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="reopenSession({{ $session['id'] }})"
                class="h-10 px-3 rounded-xl text-sm font-semibold
                    bg-emerald-50/90 text-emerald-700 ring-1 ring-emerald-100/80 shadow-mac-sm
                    hover:bg-emerald-100/90 transition-all active:scale-[0.98] disabled:opacity-50">
                Mở lại
            </button>
        @endif

        @if($session['canRestore'] ?? false)
            <button type="button"
                wire:click="restoreSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="restoreSession({{ $session['id'] }})"
                class="flex-1 h-10 px-3 rounded-xl text-sm font-semibold
                    bg-emerald-50/90 text-emerald-700 ring-1 ring-emerald-100/80 shadow-mac-sm
                    hover:bg-emerald-100/90 transition-all active:scale-[0.98] disabled:opacity-50">
                Khôi phục
            </button>
        @endif

        @if($session['canCancel'] ?? false)
            <button type="button"
                @click="$dispatch('open-confirm', {
                    message: {{ \Illuminate\Support\Js::from($cancelMessage) }},
                    wireMethod: {{ \Illuminate\Support\Js::from('cancelSession(' . $session['id'] . ')') }},
                    componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                })"
                class="h-10 px-3 rounded-xl text-sm font-semibold
                    bg-red-50/90 text-red-600 ring-1 ring-red-100/80 shadow-mac-sm
                    hover:bg-red-100/90 transition-all active:scale-[0.98]">
                Hủy
            </button>
        @endif

        @if($canDeleteSessions)
            <button type="button"
                @click="$dispatch('open-confirm', {
                    message: {{ \Illuminate\Support\Js::from($deleteMessage) }},
                    wireMethod: {{ \Illuminate\Support\Js::from('delete(' . $session['id'] . ')') }},
                    componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                })"
                class="h-10 w-10 inline-flex items-center justify-center rounded-xl
                    bg-red-50/90 text-red-600 ring-1 ring-red-100/80 shadow-mac-sm
                    hover:bg-red-100/90 transition-all active:scale-[0.98]">
                <x-icon name="trash" class="w-4 h-4" />
            </button>
        @endif
    </div>
@else
    <div class="flex items-center justify-center gap-1">
        @unless($session['cancelled'] ?? false)
            <x-tooltip content="Điểm danh">
                <a href="{{ $attendanceUrl }}"
                    class="p-2 rounded-lg text-primary-600 hover:bg-primary-50/90 transition-all">
                    <x-icon name="clipboard" class="w-4 h-4" />
                </a>
            </x-tooltip>
        @endunless

        @if($session['canLock'] ?? false)
            <x-tooltip content="Khóa phiên — kết luận số liệu sớm">
                <button type="button"
                    wire:click="lockSession({{ $session['id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="lockSession({{ $session['id'] }})"
                    class="p-2 rounded-lg text-amber-600 hover:bg-amber-50/90 transition-all disabled:opacity-50">
                    <x-icon name="lock" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif

        @if($session['canReopen'] ?? false)
            <x-tooltip content="Mở lại phiên">
                <button type="button"
                    wire:click="reopenSession({{ $session['id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="reopenSession({{ $session['id'] }})"
                    class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50/90 transition-all disabled:opacity-50">
                    <x-icon name="unlock" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif

        @if($session['canRestore'] ?? false)
            <x-tooltip content="Khôi phục thành hoạt động">
                <button type="button"
                    wire:click="restoreSession({{ $session['id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="restoreSession({{ $session['id'] }})"
                    class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50/90 transition-all disabled:opacity-50">
                    <x-icon name="refresh" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif

        @if($session['canCancel'] ?? false)
            <x-tooltip content="Hủy phiên">
                <button type="button"
                    @click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($cancelMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('cancelSession(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    class="p-2 rounded-lg text-red-500 hover:bg-red-50/90 transition-all">
                    <x-icon name="cancel" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif

        @if($canDeleteSessions)
            <x-tooltip content="Xóa phiên">
                <button type="button"
                    @click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($deleteMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('delete(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    class="p-2 rounded-lg text-red-500 hover:bg-red-50/90 transition-all">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif
    </div>
@endif
