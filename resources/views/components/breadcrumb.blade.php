@props([
'items' => [],
'separator' => 'chevron', // 'chevron', 'slash', 'arrow'
'size' => 'md' // 'sm', 'md', 'lg'
])

@php
$sizeClasses = [
'sm' => 'text-xs',
'md' => 'text-sm',
'lg' => 'text-base'
];

$separatorIcons = [
'chevron' => '<svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
</svg>',
'slash' => '<span class="text-slate-400 mx-2">/</span>',
'arrow' => '<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
</svg>'
];

$textSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<nav {{ $attributes->merge(['class' => 'mb-4 sm:mb-6']) }} aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 flex-wrap"
        itemscope
        itemtype="https://schema.org/BreadcrumbList">

        @foreach($items as $index => $item)
        <li class="inline-flex items-center"
            itemprop="itemListElement"
            itemscope
            itemtype="https://schema.org/ListItem">

            {{-- Separator (không hiển thị cho item đầu tiên) --}}
            @if($index > 0)
            <span class="mx-1 md:mx-2" aria-hidden="true">
                {!! $separatorIcons[$separator] !!}
            </span>
            @endif

            {{-- Home Icon cho item đầu tiên --}}
            @if($index === 0 && !isset($item['icon']))
            <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            @endif

            {{-- Custom Icon (nếu có) --}}
            @if(isset($item['icon']) && $index !== 0)
            <span class="mr-1.5 text-slate-500" aria-hidden="true">
                {!! $item['icon'] !!}
            </span>
            @endif

            {{-- Link hoặc Text --}}
            @if(isset($item['url']) && $index !== count($items) - 1)
            <a href="{{ $item['url'] }}"
                class="inline-flex items-center {{ $textSize }} font-medium text-slate-600 hover:text-blue-600 transition-colors duration-200"
                itemprop="item">
                <span itemprop="name">{{ $item['label'] }}</span>
            </a>
            @else
            <span class="inline-flex items-center {{ $textSize }} font-semibold text-slate-900"
                itemprop="item"
                aria-current="page">
                <span itemprop="name">{{ $item['label'] }}</span>
            </span>
            @endif

            <meta itemprop="position" content="{{ $index + 1 }}">
        </li>
        @endforeach
    </ol>
</nav>