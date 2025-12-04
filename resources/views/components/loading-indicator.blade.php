@props([
    'target' => null,
    'message' => 'Đang xử lý...',
])

<div 
    wire:loading 
    @if($target) wire:target="{{ $target }}" @endif
    class="fixed inset-0 z-[9999] flex items-center justify-center"
    style="display: none;"
>
    {{-- Overlay mờ - chặn click --}}
    <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>

    {{-- Spinner box --}}
    <div class="relative z-10 flex flex-col items-center gap-3 bg-white px-6 py-4 rounded-2xl shadow-2xl border border-slate-200/50 min-w-[200px]">
        {{-- Spinner --}}
        <svg class="animate-spin h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" 
                stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>

        {{-- Message --}}
        <span class="text-sm font-medium text-slate-700 text-center">{{ $message }}</span>
    </div>
</div>