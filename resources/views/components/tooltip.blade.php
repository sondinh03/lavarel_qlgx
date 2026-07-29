@props([
    'content' => '',
    'show' => true,
])

@if($show)
{{-- Dùng span để hợp lệ khi đặt trong th/button/span (tránh trình duyệt “đẩy” DOM lệch). --}}
<span
    {{ $attributes->class('relative inline-flex') }}
    x-data="{
        open: false,
        left: 0,
        top: 0,
        place() {
            const r = $el.getBoundingClientRect();
            const pad = 8;
            const tipW = 220;
            let x = r.left + r.width / 2;
            x = Math.max(tipW / 2 + pad, Math.min(x, window.innerWidth - tipW / 2 - pad));
            this.left = x;
            this.top = r.bottom + 8;
            this.open = true;
        },
    }"
    @mouseenter="place()"
    @mouseleave="open = false"
    @focus="place()"
    @blur="open = false">

    {{ $slot }}

    <template x-teleport="body">
        <span
            x-show="open"
            x-cloak
            :style="`position:fixed; left:${left}px; top:${top}px; transform:translateX(-50%); z-index:9999;`"
            class="pointer-events-none max-w-[min(18rem,calc(100vw-1rem))] whitespace-normal text-center
                   rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs leading-snug text-white shadow-mac-sm">
            {{ $content }}
            <span class="absolute left-1/2 -translate-x-1/2 -top-1 border-4 border-transparent border-b-slate-800"></span>
        </span>
    </template>
</span>
@else
    {{ $slot }}
@endif
