@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-3 pt-3 border-t border-primary-100">
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-semibold text-slate-500 uppercase">Bí tích</p>
        <button type="button" wire:click="openMemberSacramentForm"
            class="text-xs font-semibold text-primary-600 hover:text-primary-700">
            + Thêm bí tích
        </button>
    </div>

    @php
        $memberSacraments = collect($familySacraments ?? [])
            ->map(fn ($row, $globalIndex) => ['index' => $globalIndex, 'row' => $row])
            ->filter(fn ($item) => ($item['row']['member_ref'] ?? '') === $member_ref)
            ->values();
    @endphp

    @forelse($memberSacraments as $item)
    @php $row = $item['row']; $globalIndex = $item['index']; @endphp
    <div class="rounded-lg border border-slate-200 bg-white p-2.5 text-sm">
        <div class="flex justify-between gap-2">
            <div class="min-w-0">
                <p class="font-semibold text-slate-900">{{ $sacramentTypes[$row['type'] ?? ''] ?? ($row['type'] ?? '') }}</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if(!empty($row['received_date'])){{ $row['received_date'] }}@endif
                    @if(!empty($row['parish_name'])) · {{ $row['parish_name'] }}@endif
                </p>
            </div>
            <div class="flex gap-1 shrink-0">
                <button type="button" wire:click="openMemberSacramentForm({{ $globalIndex }})"
                    class="px-2 py-1 text-xs rounded-lg border border-slate-300 text-slate-600">Sửa</button>
                <button type="button" wire:click="removeFamilySacrament({{ $globalIndex }})"
                    class="px-2 py-1 text-xs rounded-lg border border-red-200 text-red-600">Xóa</button>
            </div>
        </div>
    </div>
    @empty
    <p class="text-xs text-slate-500">Chưa khai báo bí tích (có thể bỏ qua).</p>
    @endforelse

    @if($showFamilySacramentForm && $inlineMemberSacramentForm)
    <div class="rounded-xl border border-primary-200 bg-white p-3 space-y-3">
        <h4 class="text-sm font-bold text-slate-800">
            {{ $editingFamilySacramentIndex !== null ? 'Sửa bí tích' : 'Thêm bí tích' }}
        </h4>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Loại bí tích <span class="text-red-500">*</span></label>
            <select wire:model.defer="fs_type" class="{{ $input }}">
                <option value="">-- Chọn --</option>
                @foreach($sacramentTypes as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('fs_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Ngày lãnh nhận</label>
            <input wire:model.defer="fs_received_date" type="date" class="{{ $input }}" />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <input wire:model.defer="fs_certificate_number" type="text" class="{{ $input }}" placeholder="Số chứng thư" />
            <input wire:model.defer="fs_book_number" type="number" min="1" class="{{ $input }}" placeholder="Số quyển" />
        </div>
        <input wire:model.defer="fs_giver" type="text" class="{{ $input }}" placeholder="Người ban bí tích" />
        <input wire:model.defer="fs_sponsor" type="text" class="{{ $input }}" placeholder="Đỡ đầu" />
        <input wire:model.defer="fs_parish_name" type="text" class="{{ $input }}" placeholder="Giáo xứ / Nơi lãnh nhận" />
        <textarea wire:model.defer="fs_note" rows="2" class="{{ $input }} resize-none" placeholder="Ghi chú"></textarea>
        <div class="flex gap-2">
            <button type="button" wire:click="saveFamilySacrament"
                class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold">Lưu bí tích</button>
            <button type="button" wire:click="closeFamilySacramentForm"
                class="px-4 py-2 rounded-xl border border-slate-300 text-sm">Hủy</button>
        </div>
    </div>
    @endif
</div>
