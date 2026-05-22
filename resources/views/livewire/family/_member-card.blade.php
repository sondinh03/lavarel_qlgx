{{--
    Partial: _member-card.blade.php
    Đặt tại: resources/views/livewire/family/partial/_member-card.blade.php
    Props:
        $member     — array từ mapMember()
        $roleColor  — 'blue' | 'pink' | 'green' | 'gray'
--}}

@php
$roleStyles = [
    'blue'  => ['bg' => 'bg-blue-50',  'border' => 'border-blue-100',  'avatar' => 'bg-blue-100 text-blue-700'],
    'pink'  => ['bg' => 'bg-pink-50',  'border' => 'border-pink-100',  'avatar' => 'bg-pink-100 text-pink-700'],
    'green' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'avatar' => 'bg-emerald-100 text-emerald-700'],
    'gray'  => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'avatar' => 'bg-slate-100 text-slate-600'],
];
$s = $roleStyles[$roleColor] ?? $roleStyles['gray'];

$roleTextColor = match($roleColor) {
    'blue'  => 'text-blue-700',
    'pink'  => 'text-pink-700',
    'green' => 'text-emerald-700',
    default => 'text-slate-500',
};
$roleIconColor = match($roleColor) {
    'blue'  => 'text-blue-400',
    'pink'  => 'text-pink-400',
    'green' => 'text-emerald-400',
    default => 'text-slate-400',
};
@endphp

<div class="bg-white rounded-2xl border {{ $s['border'] }} shadow-sm overflow-hidden">

    {{-- Role header --}}
    <div class="px-5 py-2.5 {{ $s['bg'] }} border-b {{ $s['border'] }} flex items-center justify-between">

        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide {{ $roleTextColor }}">
            @if(in_array($roleColor, ['blue', 'pink']))
            {{-- Cha / Mẹ --}}
            <svg class="w-3.5 h-3.5 {{ $roleIconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            @elseif($roleColor === 'green')
            {{-- Con --}}
            <svg class="w-3.5 h-3.5 {{ $roleIconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
            </svg>
            @else
            {{-- Khác --}}
            <svg class="w-3.5 h-3.5 {{ $roleIconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            @endif
            {{ $member['role'] }}
        </span>

        @if(!$member['status'])
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                     bg-slate-100 text-slate-500">
            Không hoạt động
        </span>
        @endif
    </div>

    {{-- Member info --}}
    <div class="p-5">
        <div class="flex items-start justify-between gap-4">

            {{-- Avatar + info --}}
            <div class="flex items-start gap-4 min-w-0 flex-1">

                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    @if($member['avatar'])
                    <img src="{{ asset($member['avatar']) }}"
                        alt="{{ $member['name'] }}"
                        class="w-14 h-14 rounded-2xl object-cover shadow-sm ring-2 ring-white" />
                    @else
                    <div class="w-14 h-14 rounded-2xl {{ $s['avatar'] }} flex items-center justify-center
                                font-bold text-base shadow-sm ring-2 ring-white">
                        {{ $member['initials'] }}
                    </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="min-w-0 flex-1">

                    {{-- Tên thánh + tên --}}
                    @if($member['saint_name'])
                    <p class="text-xs text-slate-400 leading-none mb-0.5">{{ $member['saint_name'] }}</p>
                    @endif
                    <h3 class="text-base font-bold text-slate-900 leading-tight">
                        {{ $member['name'] }}
                    </h3>

                    {{-- Chi tiết --}}
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-slate-500">

                        @if($member['birthday'])
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $member['birthday'] }}
                            @if($member['age'])
                            <span class="text-slate-400">({{ $member['age'] }} tuổi)</span>
                            @endif
                        </span>
                        @endif

                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $member['gender'] }}
                        </span>

                        @if($member['phone'])
                        <a href="tel:{{ $member['phone'] }}"
                            class="flex items-center gap-1.5 hover:text-primary-600 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $member['phone'] }}
                        </a>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex-shrink-0 flex flex-col items-end gap-2">

                {{-- Link hồ sơ giáo dân --}}
                <a href="{{ $member['url'] }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold
                           text-primary-600 bg-primary-50 hover:bg-primary-100 transition-all whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Hồ sơ
                </a>

                {{-- Đổi vai trò + Xóa --}}
                <div class="flex items-center gap-1">

                    <x-tooltip content="Đổi vai trò">
                        <button
                            wire:click="openRoleModal({{ $member['id'] }}, '{{ addslashes($member['name']) }}', '{{ $member['family_role'] }}')"
                            class="p-1.5 rounded-lg text-slate-300 hover:text-primary-500 hover:bg-primary-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </button>
                    </x-tooltip>

                    <x-tooltip content="Xóa khỏi gia đình">
                        <button
                            wire:click="confirmRemoveMember({{ $member['id'] }}, '{{ addslashes($member['name']) }}')"
                            class="p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </x-tooltip>

                </div>
            </div>

        </div>
    </div>

</div>