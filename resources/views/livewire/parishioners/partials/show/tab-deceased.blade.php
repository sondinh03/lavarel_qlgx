<div>
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-semibold text-slate-800">Thông tin tử vong</h3>
        <button wire:click="openEditDeceased" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
            {{ $parishioner->is_deceased ? 'Chỉnh sửa' : 'Ghi nhận tử vong' }}
        </button>
    </div>

    @if($parishioner->is_deceased)
    <x-parishioner-section-card title="Thông tin qua đời" edit-action="openEditDeceased">
        <x-info-row label="Ngày mất" :value="$parishioner->death_date?->format('d/m/Y')" />
        <x-info-row label="Số sổ mất" :value="$parishioner->death_book_number" />
        <x-info-row label="Nơi qua đời" :value="$parishioner->death_place" />
        <x-info-row label="Nơi an táng" :value="$parishioner->burial_place" />
    </x-parishioner-section-card>
    @else
    <div class="text-center py-12">
        <p class="text-slate-400 text-sm">Chưa ghi nhận thông tin tử vong</p>
        <button wire:click="openEditDeceased" class="mt-3 px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Ghi nhận tử vong</button>
    </div>
    @endif
</div>
