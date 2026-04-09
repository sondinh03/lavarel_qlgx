<div class="flex items-center h-14">
    <nav 
        class="flex items-center text-sm text-slate-500"
        aria-label="Breadcrumb"
    >
        @foreach ($items as $index => $item)

            @if ($index > 0)
                <svg class="w-4 h-4 mx-2 text-slate-300"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7" />
                </svg>
            @endif

            @if (!empty($item['url']) && $index !== count($items) - 1)
                <a href="{{ $item['url'] }}"
                   class="hover:text-slate-800 transition truncate max-w-[160px]">
                    {{ $item['label'] }}
                </a>
            @else
                <span class="font-semibold text-slate-800 truncate max-w-[200px]">
                    {{ $item['label'] }}
                </span>
            @endif

        @endforeach
    </nav>
</div>