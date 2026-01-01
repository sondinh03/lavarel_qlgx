@props(['paginator', 'perPageOptions' => [10, 15, 25, 50, 100]])

@if ($paginator->hasPages())
<div class="px-6 py-4 bg-white border-t border-slate-200">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

        {{-- Showing results --}}
        <div class="text-sm text-slate-600">
            Hiển thị
            <span class="font-semibold text-slate-900">{{ $paginator->firstItem() ?? 0 }}</span>
            đến
            <span class="font-semibold text-slate-900">{{ $paginator->lastItem() ?? 0 }}</span>
            trong tổng số
            <span class="font-semibold text-slate-900">{{ $paginator->total() }}</span>
            kết quả
        </div>

        {{-- Pagination --}}
        <nav class="flex items-center gap-2">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <button
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg
                           hover:bg-slate-50 active:scale-95 transition-all
                           disabled:opacity-50">
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @endif

            {{-- Page numbers (desktop) --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 text-sm font-bold text-white
                                     bg-primary-600 rounded-lg shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <button
                            wire:click="gotoPage({{ $page }})"
                            wire:loading.attr="disabled"
                            class="px-3 py-2 text-sm font-medium text-slate-700
                                   bg-white border border-slate-300 rounded-lg
                                   hover:bg-slate-50 active:scale-95 transition-all
                                   disabled:opacity-50">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- Mobile indicator --}}
            <div class="sm:hidden px-3 py-2 text-sm font-medium text-slate-700
                        bg-slate-50 border border-slate-200 rounded-lg">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg
                           hover:bg-slate-50 active:scale-95 transition-all
                           disabled:opacity-50">
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </nav>

        {{-- Per page --}}
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-600 whitespace-nowrap">Hiển thị:</label>
            <select
                wire:model.live="perPage"
                class="px-3 py-2 text-sm border border-slate-300 rounded-lg
                       focus:ring-2 focus:ring-primary-500
                       focus:border-transparent transition-all">
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>

    </div>
</div>
@endif
