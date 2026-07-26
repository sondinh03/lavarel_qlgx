@props([
    'user' => null,
    'size' => 'sm',
    'shape' => 'circle',
    'ring' => null,
    'shadow' => null,
])

@php
    $user = $user ?? auth()->user();
    $url = $user?->avatar_path ? media_url($user->avatar_path) : null;
    $name = (string) ($user?->name ?? '');
@endphp

<x-entity-avatar
    :src="$url"
    :name="$name"
    :size="$size"
    :shape="$shape"
    :ring="$ring"
    :shadow="$shadow"
    variant="list"
    fallback-class="bg-primary-100 text-primary-700 font-bold"
    {{ $attributes }} />
