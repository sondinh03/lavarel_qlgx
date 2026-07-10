@props(['paginator', 'perPageOptions' => [10, 15, 25, 50, 100]])

@if ($paginator->hasPages())
<div class="px-4 py-3 bg-white/50 border-t border-black/[0.06]">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

        {{-- Showing results --}}
        <div class="text-sm text-slate-500">
            Hiển thị
            <span class="font-semibold text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span>
            đến
            <span class="font-semibold text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span>
            trong tổng số
            <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
            kết quả
        </div>

        {{-- Pagination --}}
        <nav class="flex items-center gap-1.5">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
            <span class="w-9 h-9 inline-flex items-center justify-center text-slate-300 bg-slate-100/80 rounded-full cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
            </span>
            @else
            <button
                wire:click="previousPage"
                wire:loading.attr="disabled"
                class="w-9 h-9 inline-flex items-center justify-center bg-white/80 border border-black/[0.06] rounded-full
                           hover:bg-black/[0.03] active:scale-95 transition-all shadow-mac-sm
                           disabled:opacity-50">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                $delta = 2;

                $range = [];
                $range[] = 1;

                for ($i = max(2, $current - $delta); $i <= min($last - 1, $current + $delta); $i++) {
                    $range[] = $i;
                }

                if ($last > 1) {
                    $range[] = $last;
                }

                $range = array_unique($range);
                sort($range);
                @endphp

                @foreach($range as $i => $page)
                @if($i > 0 && $page - $range[$i-1] > 1)
                <span class="px-1.5 text-slate-400 text-sm">…</span>
                @endif

                @if ($page == $current)
                <span class="min-w-[2.25rem] h-9 px-2 inline-flex items-center justify-center text-sm font-semibold text-white
                         bg-primary-500 rounded-full shadow-mac-sm">
                    {{ $page }}
                </span>
                @else
                <button
                    wire:click="gotoPage({{ $page }})"
                    wire:loading.attr="disabled"
                    class="min-w-[2.25rem] h-9 px-2 text-sm font-medium text-slate-600
                       bg-white/80 border border-black/[0.06] rounded-full
                       hover:bg-black/[0.03] active:scale-95 transition-all
                       disabled:opacity-50">
                    {{ $page }}
                </button>
                @endif
                @endforeach
            </div>

            {{-- Mobile indicator --}}
            <div class="sm:hidden px-3 h-9 inline-flex items-center text-sm font-medium text-slate-600
                        bg-slate-100/80 rounded-full">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
            <button
                wire:click="nextPage"
                wire:loading.attr="disabled"
                class="w-9 h-9 inline-flex items-center justify-center bg-white/80 border border-black/[0.06] rounded-full
                           hover:bg-black/[0.03] active:scale-95 transition-all shadow-mac-sm
                           disabled:opacity-50">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
            </button>
            @else
            <span class="w-9 h-9 inline-flex items-center justify-center text-slate-300 bg-slate-100/80 rounded-full cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
            </span>
            @endif
        </nav>

        {{-- Per page --}}
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 whitespace-nowrap">Hiển thị:</label>
            <select
                wire:model="perPage"
                class="h-9 px-3 text-sm bg-white/80 border border-black/[0.06] rounded-xl
                       focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40
                       transition-all shadow-mac-sm">
                @foreach($perPageOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
@endif
