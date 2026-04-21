@props([
    'content' => '',
    'show' => true,
])

@if($show)
<div class="relative inline-flex"
    x-data="{ show: false, x: 0, y: 0 }"
    @mouseenter="
        show = true;
        const r = $el.getBoundingClientRect();
        x = r.left + r.width / 2;
        y = r.bottom + 8;
    "
    @mouseleave="show = false">

    {{ $slot }}

    <template x-teleport="body">
        <div
            x-show="show"
            :style="`position:fixed; left:${x}px; top:${y}px; transform:translateX(-50%); z-index:9999;`"
            class="pointer-events-none whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs text-white">
            {{ $content }}
            <div class="absolute left-1/2 -translate-x-1/2 -top-1 border-4 border-transparent border-b-slate-800"></div>
        </div>
    </template>
</div>
@else
    {{ $slot }}
@endif