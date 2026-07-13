<div>
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-semibold text-slate-800">Thông tin hôn phối</h3>
        @can('update', $parishioner)
        <button wire:click="openEditMarriage" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
            {{ $marriage ? 'Chỉnh sửa' : 'Thêm hôn phối' }}
        </button>
        @endcan
    </div>

    @if($marriage)
    <div class="space-y-4">
        @php
        $spouse = $parishioner->gender === 'male' ? $marriage->wife : $marriage->husband;
        $spouseLabel = $parishioner->gender === 'male' ? 'Vợ' : 'Chồng';
        @endphp

        @if($spouse)
        <x-parishioner-section-card :title="$spouseLabel">
            <div class="px-4 py-3">
                <a href="{{ route('parishioners.show', $spouse->id) }}"
                    class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                    {{ $spouse->full_name_with_saint }} →
                </a>
            </div>
        </x-parishioner-section-card>
        @endif

        <x-parishioner-section-card title="Chi tiết hôn phối" edit-action="openEditMarriage">
            <x-info-row label="Ngày kết hôn" :value="$marriage->married_date?->format('d/m/Y')" />
            <x-info-row label="Trạng thái hôn phối (giáo hội)" :value="$marriage->status_name" />
            <x-info-row label="Số chứng chỉ" :value="$marriage->certificate_number" />
            <x-info-row label="Nơi kết hôn" :value="$marriage->parish?->name ?? $marriage->parish_name" />
            <x-info-row label="Tỉnh/TP" :value="$marriage->place_province" />
            <x-info-row label="Linh mục chứng" :value="$marriage->priest_witness" />
            <x-info-row label="Nhân chứng 1" :value="$marriage->witness_1" />
            <x-info-row label="Nhân chứng 2" :value="$marriage->witness_2" />
        </x-parishioner-section-card>

        @if($marriage->note)
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm text-slate-700">
            <p class="text-xs font-semibold text-slate-400 mb-1">Ghi chú</p>
            {{ $marriage->note }}
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-slate-400 text-sm">Chưa có thông tin hôn phối</p>
        @can('update', $parishioner)
        <button wire:click="openEditMarriage" class="mt-3 px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 transition">Thêm hôn phối</button>
        @endcan
    </div>
    @endif
</div>
