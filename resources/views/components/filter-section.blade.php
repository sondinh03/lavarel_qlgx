@props(['showReset' => true])

<div class="p-6 bg-slate-50 border-t border-slate-200">
    <div class="space-y-4">
        {{-- Filter Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{ $slot }}

            {{-- Reset Button (if enabled) --}}
            @if($showReset)
                <div class="flex items-end">
                    <button 
                        wire:click="resetFilters"
                        type="button"
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="font-semibold text-slate-900">Đặt lại</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Loading Indicator --}}
        @if(isset($loadingTargets))
            <div wire:loading wire:target="{{ $loadingTargets }}" class="mt-4">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-700 font-medium">Đang tải dữ liệu...</span>
                </div>
            </div>
        @endif
    </div>
</div>