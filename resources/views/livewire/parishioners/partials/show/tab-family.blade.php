@php $roleLabels = ['husband' => 'Chồng', 'wife' => 'Vợ', 'child' => 'Con', 'other' => 'Khác']; @endphp

<div class="space-y-4 lg:space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach(['father' => 'Cha', 'mother' => 'Mẹ'] as $rel => $label)
        @php $person = $parishioner->$rel; @endphp
        <x-parishioner-section-card :title="$label" edit-action="openEditFamily">
            @if($person)
            <div class="px-4 py-3">
                <a href="{{ route('parishioners.show', $person->id) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                    {{ $person->full_name_with_saint }}
                </a>
                @if($person->phone)<p class="text-xs text-slate-400 mt-1">{{ $person->phone }}</p>@endif
            </div>
            @elseif($parishioner->{$rel . '_name'})
            <div class="px-4 py-3">
                <p class="text-sm text-slate-700">{{ $parishioner->{$rel . '_name'} }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Chưa có hồ sơ trong hệ thống</p>
            </div>
            @else
            <div class="px-4 py-3 text-sm text-slate-400 italic">Chưa có thông tin</div>
            @endif
        </x-parishioner-section-card>
        @endforeach
    </div>

    <x-parishioner-section-card title="Vai trò & Hôn nhân" edit-action="openEditFamily">
        <x-info-row label="Vai trò trong gia đình" :value="$roleLabels[$parishioner->family_role] ?? null" />
        <x-info-row label="Tình trạng hôn nhân" :value="$parishioner->married_status_name" />
    </x-parishioner-section-card>

    @if($parishioner->family)
    <x-parishioner-section-card title="Hộ gia đình">
        <div class="px-4 py-3">
            <a href="{{ route('families.show', $parishioner->family_id) }}"
                class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                {{ $parishioner->family->name }}
            </a>
            <p class="text-xs text-slate-400 mt-1">
                {{ $parishioner->family->member_count ? $parishioner->family->member_count . ' thành viên' : '' }}
                @if($parishioner->family->parishGroup) · {{ $parishioner->family->parishGroup->name }} @endif
            </p>
        </div>
    </x-parishioner-section-card>
    @endif

    @if($this->children->count() > 0)
    <x-parishioner-section-card :title="'Con cái (' . $this->children->count() . ')'">
        @foreach($this->children as $child)
        <div class="px-4 py-3 flex items-center justify-between gap-3">
            <div>
                <a href="{{ route('parishioners.show', $child->id) }}"
                    class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                    {{ $child->full_name_with_saint }}
                </a>
                @if($child->birthday)
                <p class="text-xs text-slate-400 mt-0.5">{{ $child->birthday->format('d/m/Y') }}</p>
                @endif
            </div>
            @if($child->birth_order)
            <span class="text-xs text-slate-500">Con thứ {{ $child->birth_order }}</span>
            @endif
        </div>
        @endforeach
    </x-parishioner-section-card>
    @endif
</div>
