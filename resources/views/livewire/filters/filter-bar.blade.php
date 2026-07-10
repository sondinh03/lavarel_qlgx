<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

    @if($showNamHoc)
    <x-select-input
        wire:key="filter-nam-hoc-{{ $selectedNamHoc }}"
        label="Năm học"
        wire:model="selectedNamHoc"
        :value="$selectedNamHoc"
        :options="$namHocs"
        placeholder="" />
    @endif

    @if($showKhoi)
    <x-select-input
        wire:key="filter-khoi-{{ $selectedNamHoc }}-{{ $selectedKhoi }}"
        label="Khối"
        wire:model="selectedKhoi"
        :value="$selectedKhoi"
        :options="$khois"
        placeholder="-- Tất cả khối --"
        :disabled="!$selectedNamHoc" />
    @endif

    @if($showLop)
    <x-select-input
        wire:key="filter-lop-{{ $selectedNamHoc }}-{{ $selectedKhoi }}-{{ $selectedLop }}"
        label="Lớp"
        wire:model="selectedLop"
        :value="$selectedLop"
        :options="collect($lops)->pluck('name', 'id')"
        placeholder="-- Tất cả lớp --"
        :disabled="!$selectedNamHoc" />
    @endif

    @if($showKy)
    <x-select-input
        wire:key="filter-ky-{{ $selectedNamHoc }}-{{ $selectedKy }}"
        label="Học kỳ"
        wire:model="selectedKy"
        :value="$selectedKy"
        :options="$kys"
        placeholder=""
        :disabled="!$selectedNamHoc" />
    @endif

</div>
