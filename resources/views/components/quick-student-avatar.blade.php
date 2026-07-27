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
@endphp

<x-quick-avatar-upload
    :id="$student->id"
    :name="$name"
    :initials="$initials"
    :src="$src"
    wire-target="quickAvatars.{{ $student->id }}"
    :enabled="$enabled"
    :size="$size"
    :variant="$variant"
    input-prefix="quick-avatar-student"
    {{ $attributes }} />
