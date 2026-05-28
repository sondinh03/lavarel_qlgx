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
            <span class="px-3 py-2 text-sm text-slate-400 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">
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
                @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $delta = 2; // Số trang hiển thị mỗi bên

                $range = [];

                // Luôn hiện trang đầu
                $range[] = 1;

                // Tính range quanh current page
                for ($i = max(2, $current - $delta); $i <= min($last - 1, $current + $delta); $i++) {
                    $range[]=$i;
                    }

                    // Luôn hiện trang cuối (nếu> 1)
                    if ($last > 1) {
                    $range[] = $last;
                    }

                    $range = array_unique($range);
                    sort($range);
                    @endphp

                    @foreach($range as $i => $page)
                    {{-- Hiện dấu ... nếu có khoảng cách --}}
                    @if($i > 0 && $page - $range[$i-1] > 1)
                    <span class="px-2 text-slate-400">...</span>
                    @endif

                    @if ($page == $current)
                    <span class="px-3 py-2 text-sm font-bold text-white
                         bg-gradient-to-r from-primary-500 to-primary-600 
                         rounded-lg shadow-md">
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
            <span class="px-3 py-2 text-sm text-slate-400 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">
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
                wire:model="perPage"
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