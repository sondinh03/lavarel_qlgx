@props([
    'title' => '',
    'description' => '',
    'icon' => 'default',
    'gradient' => 'purple', // purple, blue, green, orange, red, indigo
    'count' => null,
    'countLabel' => 'Tổng số'
])

@php
$gradients = [
    'purple' => 'from-purple-500 to-indigo-600',
    'blue' => 'from-blue-500 to-cyan-600',
    'green' => 'from-green-500 to-emerald-600',
    'orange' => 'from-orange-500 to-amber-600',
    'red' => 'from-red-500 to-pink-600',
    'indigo' => 'from-indigo-500 to-purple-600'
];

$icons = [
    'default' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
    'class' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
    'students' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
    'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'
];

$selectedGradient = $gradients[$gradient] ?? $gradients['purple'];
$selectedIcon = $icons[$icon] ?? $icons['default'];
@endphp

<div class="bg-gradient-to-r {{ $selectedGradient }} p-6 rounded-t-2xl">
    <div class="flex items-center justify-between text-white">
        <div class="flex items-center gap-4">
            {{-- Icon --}}
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $selectedIcon !!}
                </svg>
            </div>

            {{-- Title & Description --}}
            <div>
                <h1 class="text-xl font-bold">{{ $title }}</h1>
                @if($description)
                    <p class="text-white/90 text-sm mt-0.5">{{ $description }}</p>
                @endif
            </div>
        </div>

        {{-- Count Badge --}}
        @if($count !== null)
            <div class="text-right">
                <p class="text-white/90 text-sm font-medium">{{ $countLabel }}</p>
                <p class="text-3xl font-bold">{{ $count }}</p>
            </div>
        @endif

        {{-- Custom Slot --}}
        {{ $slot }}
    </div>
</div>