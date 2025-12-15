@props(['wireTarget' => '', 'mode' => 'inline'])

@if($mode === 'inline')
<div wire:loading.delay @if($wireTarget) wire:target="{{ $wireTarget }}" @endif class="mt-4" role="status" aria-live="polite">
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center gap-3">
        <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-purple-700 font-medium">{{ $slot->isEmpty() ? 'Đang tải dữ liệu...' : $slot }}</span>
    </div>
</div>
@else
<div wire:loading.delay @if($wireTarget) wire:target="{{ $wireTarget }}" @endif class="relative" aria-hidden="false">
    <div class="absolute inset-0 bg-white/75 backdrop-blur-sm z-30 flex items-center justify-center" role="status" aria-live="polite">
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center gap-3">
            <svg class="animate-spin h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-slate-700 font-medium">{{ $slot->isEmpty() ? 'Đang tải danh sách...' : $slot }}</p>
        </div>
    </div>
</div>
@endif
