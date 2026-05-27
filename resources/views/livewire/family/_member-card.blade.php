{{-- Member card component --}}
@php
$roleStyles = [
    'blue'  => ['bg' => 'bg-blue-50',  'border' => 'border-blue-100',  'avatar' => 'bg-blue-100 text-blue-700'],
    'pink'  => ['bg' => 'bg-pink-50',  'border' => 'border-pink-100',  'avatar' => 'bg-pink-100 text-pink-700'],
    'green' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'avatar' => 'bg-emerald-100 text-emerald-700'],
    'gray'  => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'avatar' => 'bg-slate-100 text-slate-600'],
];

$roleColor = match(true) {
    in_array($member['family_role'], ['husband', 'wife']) => in_array($member['family_role'], ['husband']) ? 'blue' : 'pink',
    $member['family_role'] === 'child' => 'green',
    default => 'gray',
};

$s = $roleStyles[$roleColor] ?? $roleStyles['gray'];

$roleTextColor = match($roleColor) {
    'blue'  => 'text-blue-700',
    'pink'  => 'text-pink-700',
    'green' => 'text-emerald-700',
    default => 'text-slate-500',
};
@endphp

<div class="bg-white rounded-2xl border {{ $s['border'] }} shadow-sm overflow-hidden">

    {{-- Role header --}}
    <div class="px-5 py-3 {{ $s['bg'] }} border-b {{ $s['border'] }} flex items-center justify-between">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide {{ $roleTextColor }}">
            @if($roleColor === 'blue')
            <x-icon name="user" class="w-3.5 h-3.5" />
            @elseif($roleColor === 'pink')
            <x-icon name="user" class="w-3.5 h-3.5" />
            @elseif($roleColor === 'green')
            <x-icon name="users" class="w-3.5 h-3.5" />
            @else
            <x-icon name="people" class="w-3.5 h-3.5" />
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
                        class="w-14 h-14 rounded-xl object-cover shadow-sm ring-2 ring-white" />
                    @else
                    <div class="w-14 h-14 rounded-xl {{ $s['avatar'] }} flex items-center justify-center
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
                            <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />
                            {{ $member['birthday'] }}
                            @if($member['age'])
                            <span class="text-slate-400">({{ $member['age'] }} tuổi)</span>
                            @endif
                        </span>
                        @endif

                        <span class="flex items-center gap-1.5">
                            <x-icon name="user" class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />
                            {{ $member['gender'] }}
                        </span>

                        @if($member['phone'])
                        <a href="tel:{{ $member['phone'] }}"
                            class="flex items-center gap-1.5 hover:text-primary-600 transition-colors">
                            <x-icon name="phone" class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />
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
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                           text-primary-600 bg-primary-50 hover:bg-primary-100 transition-all whitespace-nowrap">
                    <x-icon name="external-link" class="w-3.5 h-3.5" />
                    Hồ sơ
                </a>

                {{-- Đổi vai trò + Xóa --}}
                <div class="flex items-center gap-1">

                    <x-tooltip content="Đổi vai trò">
                        <button
                            wire:click="openRoleModal({{ $member['id'] }}, '{{ addslashes($member['name']) }}', '{{ $member['family_role'] }}')"
                            class="p-1.5 rounded-lg text-slate-300 hover:text-primary-500 hover:bg-primary-50 transition-all">
                            <x-icon name="edit-3" class="w-4 h-4" />
                        </button>
                    </x-tooltip>

                    <x-tooltip content="Xóa khỏi gia đình">
                        <button
                            wire:click="confirmRemoveMember({{ $member['id'] }}, '{{ addslashes($member['name']) }}')"
                            class="p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                            <x-icon name="log-out" class="w-4 h-4" />
                        </button>
                    </x-tooltip>

                </div>
            </div>

        </div>
    </div>

</div>
