@props([
'title' => '',
'description' => '',
'statValue' => null,
'statLabel' => '',
'iconType' => 'class', // 'class', 'block', 'student', 'teacher'
])

<div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
    <div class="flex items-center justify-between gap-6">
        {{-- LEFT: Icon + Title --}}
        <div class="flex items-center gap-4 min-w-0">
            @if($slot->isNotEmpty() || $iconType)
            <div class="w-12 h-12 rounded-xl bg-primary-500 flex items-center justify-center shadow-sm shrink-0">
                @if($slot->isNotEmpty())
                {{ $slot }}
                @else
                @switch($iconType)
                @case('block')
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4l8 4-8 4-8-4 8-4z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4 8-4" />
                </svg>
                @break
                @case('student')
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                @break
                @case('teacher')
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                @break
                @case('schoolYear')
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                @break
                @default
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
                @endswitch
                @endif
            </div>
            @endif

            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-900 truncate">
                    {{ $title }}
                </h1>
                @if($description)
                <p class="text-sm text-slate-600 mt-1">
                    {{ $description }}
                </p>
                @endif
            </div>
        </div>

        {{-- RIGHT: Stat --}}
        @if($statValue !== null)
        <div class="flex items-center gap-4 pl-6 border-l border-slate-200 text-right shrink-0">
            <div>
                <div class="text-3xl font-bold text-primary-600 leading-none">
                    {{ $statValue }}
                </div>
                <div class="text-xs text-slate-600 font-medium mt-1">
                    {{ $statLabel }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>