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
    $hasMoreActions = ($session['canCancel'] ?? false) || $canDeleteSessions;
@endphp

@if($layout === 'mobile')
    <div class="mt-3 flex flex-wrap items-center gap-4">
        @unless($session['cancelled'] ?? false)
            <x-button as="a" href="{{ $attendanceUrl }}" variant="primary" size="sm" class="flex-1 min-w-[7.5rem] justify-center">
                <x-icon name="clipboard" class="w-4 h-4" />
                Điểm danh
            </x-button>
        @endunless

        @if($session['canLock'] ?? false)
            <x-button
                type="button"
                variant="secondary"
                size="sm"
                wire:click="lockSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="lockSession({{ $session['id'] }})">
                Khóa
            </x-button>
        @endif

        @if($session['canReopen'] ?? false)
            <x-button
                type="button"
                variant="secondary"
                size="sm"
                wire:click="reopenSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="reopenSession({{ $session['id'] }})">
                Mở lại
            </x-button>
        @endif

        @if($session['canRestore'] ?? false)
            <x-button
                type="button"
                variant="secondary"
                size="sm"
                class="flex-1 justify-center"
                wire:click="restoreSession({{ $session['id'] }})"
                wire:loading.attr="disabled"
                wire:target="restoreSession({{ $session['id'] }})">
                Khôi phục
            </x-button>
        @endif

        @if($hasMoreActions)
            <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                @if($session['canCancel'] ?? false)
                <x-dropdown-item
                    x-on:click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($cancelMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('cancelSession(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    icon="cancel"
                    class="text-red-600 hover:bg-red-50">
                    Hủy phiên
                </x-dropdown-item>
                @endif
                @if($canDeleteSessions)
                <x-dropdown-item
                    x-on:click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($deleteMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('delete(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    icon="trash"
                    class="text-red-600 hover:bg-red-50">
                    Xóa phiên
                </x-dropdown-item>
                @endif
            </x-dropdown>
        @endif
    </div>
@else
    <div class="flex items-center justify-center gap-1">
        @unless($session['cancelled'] ?? false)
            <x-tooltip content="Điểm danh">
                <a href="{{ $attendanceUrl }}"
                    class="p-2 rounded-lg text-primary-600 hover:bg-primary-50/90 transition-all duration-200">
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
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-all duration-200 disabled:opacity-50">
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
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-all duration-200 disabled:opacity-50">
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
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-all duration-200 disabled:opacity-50">
                    <x-icon name="refresh" class="w-4 h-4" />
                </button>
            </x-tooltip>
        @endif

        @if($hasMoreActions)
            <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                @if($session['canCancel'] ?? false)
                <x-dropdown-item
                    x-on:click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($cancelMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('cancelSession(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    icon="cancel"
                    class="text-red-600 hover:bg-red-50">
                    Hủy phiên
                </x-dropdown-item>
                @endif
                @if($canDeleteSessions)
                <x-dropdown-item
                    x-on:click="$dispatch('open-confirm', {
                        message: {{ \Illuminate\Support\Js::from($deleteMessage) }},
                        wireMethod: {{ \Illuminate\Support\Js::from('delete(' . $session['id'] . ')') }},
                        componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                    })"
                    icon="trash"
                    class="text-red-600 hover:bg-red-50">
                    Xóa phiên
                </x-dropdown-item>
                @endif
            </x-dropdown>
        @endif
    </div>
@endif
