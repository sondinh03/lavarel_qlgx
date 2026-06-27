@php
    $canManage = $canManage ?? false;
    $tone = $tone ?? 'blue';
    $toneClasses = $tone === 'pink'
    ? ['wrap' => 'bg-pink-50/50', 'badge' => 'bg-pink-100 text-pink-700', 'avatar' => 'bg-pink-100 text-pink-700']
    : ['wrap' => 'bg-blue-50/50', 'badge' => 'bg-blue-100 text-blue-700', 'avatar' => 'bg-blue-100 text-blue-700'];
@endphp

<div class="p-4 {{ $toneClasses['wrap'] }}">
    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $toneClasses['badge'] }}">
        {{ $label }}
    </span>

    @if($member)
    <div class="mt-3 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if($member['avatar'])
            <img src="{{ $member['avatar'] }}" alt="" class="w-12 h-12 rounded-xl object-cover shadow-sm" />
            @else
            <div class="w-12 h-12 rounded-xl {{ $toneClasses['avatar'] }} flex items-center justify-center text-sm font-bold">
                {{ $member['initials'] }}
            </div>
            @endif
            <div class="min-w-0">
                <a href="{{ $member['url'] }}" class="text-sm font-bold text-slate-900 hover:text-primary-600 line-clamp-2">
                    @if($member['saint_name'])<span class="font-normal text-slate-500">{{ $member['saint_name'] }}</span> @endif
                    {{ $member['name'] }}
                </a>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $member['gender'] }}
                    @if($member['birthday']) · {{ $member['birthday'] }} @endif
                </p>
            </div>
        </div>
        @if($canManage)
        <div class="flex gap-0.5 flex-shrink-0">
            <button type="button"
                wire:click="openRoleModal({{ $member['id'] }}, '{{ addslashes($member['name']) }}', '{{ $member['family_role'] }}')"
                class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-white/80 transition"
                title="Đổi vai trò">
                <x-icon name="edit-3" class="w-4 h-4" />
            </button>
            <button type="button"
                wire:click="confirmRemoveMember({{ $member['id'] }}, '{{ addslashes($member['name']) }}')"
                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-white/80 transition"
                title="Xóa khỏi gia đình">
                <x-icon name="log-out" class="w-4 h-4" />
            </button>
        </div>
        @endif
    </div>
    @else
    <p class="mt-3 text-sm text-slate-400 italic">Chưa có thông tin</p>
    @endif
</div>
