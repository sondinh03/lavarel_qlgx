@props([
    'student',
    'size' => 'md',
    'variant' => 'list',
    'enabled' => false,
])

@php
    $name = trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
    $initials = mb_substr($student->last_name ?? '', 0, 1) . mb_substr($student->first_name ?? '', 0, 1);
    $src = $student->avatar_path ? $student->avatar_url : null;
    $inputId = 'quick-avatar-' . $student->id;
    $wireTarget = 'quickAvatars.' . $student->id;
@endphp

@if($enabled)
<div
    {{ $attributes->class(['relative inline-flex flex-shrink-0 group']) }}
    wire:key="quick-avatar-wrap-{{ $student->id }}"
    @click.stop
    @mousedown.stop>
    <x-entity-avatar
        :src="$src"
        :name="$name"
        :initials="$initials"
        :size="$size"
        :variant="$variant" />

    <label
        for="{{ $inputId }}"
        class="absolute inset-0 rounded-full cursor-pointer
            bg-black/0 sm:group-hover:bg-black/35
            flex items-center justify-center transition-colors"
        title="{{ $src ? 'Đổi ảnh' : 'Thêm ảnh' }}">
        {{-- Camera hint: luôn hiện góc dưới (mobile-friendly); hover làm rõ hơn trên desktop --}}
        <span class="absolute -bottom-0.5 -right-0.5 inline-flex items-center justify-center
            w-5 h-5 rounded-full bg-primary-500 text-white shadow-mac-sm
            ring-2 ring-white
            sm:opacity-90 sm:group-hover:opacity-100">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </span>
        <span
            wire:loading.flex
            wire:target="{{ $wireTarget }}"
            class="absolute inset-0 hidden items-center justify-center rounded-full bg-black/40">
            <svg class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </span>
    </label>

    <input
        id="{{ $inputId }}"
        type="file"
        accept="image/*"
        capture="environment"
        class="hidden"
        wire:model="{{ $wireTarget }}" />
</div>
@else
    <x-entity-avatar
        :src="$src"
        :name="$name"
        :initials="$initials"
        :size="$size"
        :variant="$variant" />
@endif
