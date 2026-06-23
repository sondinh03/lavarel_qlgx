@php
$role = $role ?? 'groom';
$prefix = $prefix ?? 'current';
$prefixLabel = $prefixLabel ?? 'Giáo xứ hiện tại';
$optional = $optional ?? false;

$ids = $participantIds[$role][$prefix] ?? [];
$dropdowns = $participantDropdowns[$role][$prefix] ?? ['deanery' => [], 'parish' => [], 'parish_group' => []];
$dioceseId = $ids['diocese'] ?? null;
$deaneryId = $ids['deanery'] ?? null;
$parishId = $ids['parish'] ?? null;
$groupId = $ids['parish_management'] ?? null;
@endphp

<div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
        {{ $prefixLabel }}
        @if($optional)<span class="font-normal normal-case text-slate-400">(tùy chọn)</span>@endif
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Giáo phận</label>
            <x-searchable-select
                wireModel="participantIds.{{ $role }}.{{ $prefix }}.diocese"
                :options="$dioceses"
                :live="true"
                placeholder="-- Chọn giáo phận --"
                labelKey="name"
                valueKey="id"
                :value="$dioceseId" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Giáo hạt</label>
            <x-searchable-select
                wire:key="p-deanery-{{ $role }}-{{ $prefix }}-{{ $dioceseId ?? 'none' }}"
                wireModel="participantIds.{{ $role }}.{{ $prefix }}.deanery"
                :options="$dropdowns['deanery'] ?? []"
                :live="true"
                placeholder="{{ $dioceseId ? '-- Chọn giáo hạt --' : 'Chọn giáo phận trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$deaneryId" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Giáo xứ</label>
            <x-searchable-select
                wire:key="p-parish-{{ $role }}-{{ $prefix }}-{{ $deaneryId ?? 'none' }}"
                wireModel="participantIds.{{ $role }}.{{ $prefix }}.parish"
                :options="$dropdowns['parish'] ?? []"
                :live="true"
                placeholder="{{ $deaneryId ? '-- Chọn giáo xứ --' : 'Chọn giáo hạt trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$parishId" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Giáo họ</label>
            <x-searchable-select
                wire:key="p-group-{{ $role }}-{{ $prefix }}-{{ $parishId ?? 'none' }}"
                wireModel="participantIds.{{ $role }}.{{ $prefix }}.parish_management"
                :options="$dropdowns['parish_group'] ?? []"
                placeholder="{{ $parishId ? '-- Chọn giáo họ --' : 'Chọn giáo xứ trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$groupId" />
        </div>
    </div>
</div>
