@props([
    'src' => null,
    'name' => '',
    'initials' => null,
    'size' => 'md',
    'shape' => 'circle',
    'variant' => 'list',
    'fallbackClass' => null,
    'ring' => null,
    'shadow' => null,
])

@php
    $sizeClass = match ($size) {
        'xs'      => 'w-7 h-7 text-[10px]',
        'sm'      => 'w-8 h-8 text-xs',
        'md'      => 'w-9 h-9 text-xs',
        'lg'      => 'w-10 h-10 text-xs',
        'xl'      => 'w-11 h-11 text-sm',
        '2xl'     => 'w-12 h-12 text-base',
        'profile' => 'w-20 h-20 text-2xl',
        default   => 'w-9 h-9 text-xs',
    };

    $shapeClass = match ($shape) {
        'rounded'    => 'rounded-2xl',
        'rounded-xl' => 'rounded-xl',
        'square'     => 'rounded-none',
        default      => 'rounded-full',
    };

    // Màu nền + màu chữ, chỉ dùng khi không có ảnh.
    $toneClass = $fallbackClass ?: match ($variant) {
        'profile' => 'bg-gradient-to-br from-primary-500 to-primary-700 text-white font-semibold',
        'solid'   => 'bg-primary-500 text-white font-bold',
        'slate'   => 'bg-slate-100 text-slate-500 font-semibold',
        default   => 'bg-primary-50/80 text-primary-800 font-semibold',
    };

    // Ring/shadow áp cho cả ảnh thật lẫn fallback nên nằm riêng khỏi tone màu.
    $ringPreset = $ring === null
        ? match ($variant) {
            'profile' => 'primary-strong',
            default   => 'primary',
        }
        : $ring;

    $ringClass = match (true) {
        $ringPreset === false, $ringPreset === 'none' => '',
        $ringPreset === true, $ringPreset === 'primary' => 'ring-2 ring-primary-100',
        $ringPreset === 'primary-strong' => 'ring-4 ring-primary-50/80',
        $ringPreset === 'white'   => 'ring-2 ring-white',
        $ringPreset === 'slate'   => 'ring-1 ring-black/[0.06]',
        $ringPreset === 'success' => 'ring-2 ring-success-100',
        $ringPreset === 'danger'  => 'ring-2 ring-danger-100',
        $ringPreset === 'warning' => 'ring-2 ring-warning-100',
        default => (string) $ringPreset,
    };

    $shadowPreset = $shadow ?? 'sm';

    $shadowClass = match (true) {
        $shadowPreset === false, $shadowPreset === 'none' => '',
        $shadowPreset === true, $shadowPreset === 'sm' => 'shadow-mac-sm',
        $shadowPreset === 'md' => 'shadow-mac',
        $shadowPreset === 'lg' => 'shadow-ios',
        default => (string) $shadowPreset,
    };

    $name = trim((string) $name);
    if ($initials !== null && $initials !== '') {
        $label = mb_strtoupper((string) $initials, 'UTF-8');
    } else {
        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) >= 2) {
            $label = mb_strtoupper(
                mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr($parts[count($parts) - 1], 0, 1, 'UTF-8'),
                'UTF-8'
            );
        } else {
            $label = mb_strtoupper(mb_substr($name !== '' ? $name : 'U', 0, 1, 'UTF-8'), 'UTF-8');
        }
    }
@endphp

<div {{ $attributes->class([
    $sizeClass,
    $shapeClass,
    'overflow-hidden flex items-center justify-center flex-shrink-0',
    $ringClass,
    $shadowClass,
    $src ? 'bg-slate-100' : $toneClass,
]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="w-full h-full object-cover" />
    @else
        {{ $label }}
    @endif
</div>
