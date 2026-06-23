@php $canManage = $canManage ?? false; @endphp

<div class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-white/60 transition-colors">
    <div class="flex items-center gap-3 min-w-0 flex-1">
        @if($member['avatar'])
        <img src="{{ asset($member['avatar']) }}" alt=""
            class="w-10 h-10 rounded-xl object-cover flex-shrink-0 ring-2 ring-white shadow-sm" />
        @else
        @php
        $avatarClass = match($member['family_role']) {
            'husband' => 'bg-blue-100 text-blue-700',
            'wife'    => 'bg-pink-100 text-pink-700',
            'child'   => 'bg-emerald-100 text-emerald-700',
            default   => 'bg-slate-100 text-slate-600',
        };
        @endphp
        <div class="w-10 h-10 rounded-xl {{ $avatarClass }} flex items-center justify-center text-xs font-bold flex-shrink-0">
            {{ $member['initials'] }}
        </div>
        @endif

        <div class="min-w-0">
            <a href="{{ $member['url'] }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 truncate block">
                @if($member['saint_name']){{ $member['saint_name'] }} @endif{{ $member['name'] }}
            </a>
            <p class="text-xs text-slate-400 mt-0.5 flex flex-wrap gap-x-2 gap-y-0.5">
                <span>{{ $member['gender'] }}</span>
                @if($member['birthday'])
                <span>· {{ $member['birthday'] }}</span>
                @endif
                @if($member['birth_order'])
                <span>· Con thứ {{ $member['birth_order'] }}</span>
                @endif
                @if($member['phone'])
                <span>· {{ $member['phone'] }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="flex items-center gap-1 flex-shrink-0">
        @if($canManage)
        <button type="button"
            wire:click="openRoleModal({{ $member['id'] }}, '{{ addslashes($member['name']) }}', '{{ $member['family_role'] }}')"
            class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition"
            title="Đổi vai trò">
            <x-icon name="edit-3" class="w-4 h-4" />
        </button>
        <button type="button"
            wire:click="confirmRemoveMember({{ $member['id'] }}, '{{ addslashes($member['name']) }}')"
            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
            title="Xóa khỏi gia đình">
            <x-icon name="log-out" class="w-4 h-4" />
        </button>
        @endif
    </div>
</div>
