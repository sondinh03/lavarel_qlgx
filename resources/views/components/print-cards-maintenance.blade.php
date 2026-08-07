{{-- Banner bảo trì chức năng In thẻ / xuất PDF --}}
@props([])

@if(! config('qlgx.print_cards.enabled'))
<div {{ $attributes->merge(['class' => 'print:hidden', 'role' => 'status', 'aria-live' => 'polite']) }}>
    <x-inline-tip tone="amber">
        <p class="font-semibold text-sm mb-0.5">Đang bảo trì — In thẻ / xuất PDF</p>
        <p class="text-xs leading-relaxed opacity-90">
            {{ config('qlgx.print_cards.maintenance_message') }}
        </p>
    </x-inline-tip>
</div>
@endif
