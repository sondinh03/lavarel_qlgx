@props([
    'title' => '',
    'description' => '',
    'count' => null,
])

<div class="px-6 py-4 border-b border-slate-200 bg-white rounded-t-2xl">
    <div class="flex items-center justify-between gap-4">
        
        {{-- LEFT: Title --}}
        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-slate-900 truncate">
                {{ $title }}
                
                @if($count !== null)
                    <span class="ml-2 text-sm font-normal text-slate-500">
                        ({{ $count }})
                    </span>
                @endif
            </h1>

            @if($description)
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ $description }}
                </p>
            @endif
        </div>

        {{-- RIGHT: Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions ?? '' }}
        </div>

    </div>
</div>