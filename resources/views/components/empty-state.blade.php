@props([
    'icon' => 'default',
    'title' => 'Không có dữ liệu',
    'description' => '',
    'actionText' => '',
    'actionUrl' => '#',
    'colspan' => 1
])

@php
$icons = [
    'default' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
    'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />',
    'class' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
    'students' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
    'file' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
];

$selectedIcon = $icons[$icon] ?? $icons['default'];
@endphp

<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center justify-center">
            {{-- Icon --}}
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $selectedIcon !!}
                </svg>
            </div>

            {{-- Title --}}
            <p class="text-slate-700 font-semibold text-lg mb-2">{{ $title }}</p>

            {{-- Description --}}
            @if($description)
                <p class="text-slate-500 text-sm mb-4 max-w-md">{{ $description }}</p>
            @endif

            {{-- Action Button --}}
            @if($actionText)
                <a href="{{ $actionUrl }}"
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $actionText }}
                </a>
            @endif

            {{-- Custom Slot --}}
            {{ $slot }}
        </div>
    </td>
</tr>