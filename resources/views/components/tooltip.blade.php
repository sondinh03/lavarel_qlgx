@props([
    'content' => '',
    'show' => true, // cho phép bật/tắt tooltip
])

@if($show)
<div class="relative inline-flex group">
    {{ $slot }}

    <div class="pointer-events-none absolute z-50
        left-1/2 -translate-x-1/2 top-full mt-2
        whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5
        text-xs text-white opacity-0 group-hover:opacity-100
        transition-opacity duration-200">

        {{ $content }}

        {{-- Arrow --}}
        <div class="absolute left-1/2 -translate-x-1/2 -top-1
            border-4 border-transparent border-b-slate-800">
        </div>
    </div>
</div>
@else
    {{ $slot }}
@endif