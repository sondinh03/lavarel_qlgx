@props([
    'user' => null,
    'size' => 'sm',
])

@php
    $user = $user ?? auth()->user();
    $sizeClass = match ($size) {
        'xs' => 'w-7 h-7 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-11 h-11 text-lg',
        'lg' => 'w-20 h-20 text-2xl',
        default => 'w-8 h-8 text-xs',
    };
    $initial = strtoupper(mb_substr((string) ($user?->name ?: 'U'), 0, 1));
    $url = $user?->avatar_path ? media_url($user->avatar_path) : null;
@endphp

@if ($url)
<img src="{{ $url }}" alt="{{ $user->name ?? '' }}"
    {{ $attributes->merge(['class' => "{$sizeClass} rounded-full object-cover flex-shrink-0 bg-slate-100"]) }} />
@else
<div {{ $attributes->merge([
    'class' => "{$sizeClass} rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold flex-shrink-0",
]) }}>
    {{ $initial }}
</div>
@endif
