@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
  <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
    Khai báo bí tích cho từng thành viên (rửa tội, rước lễ, thêm sức...). Có thể bỏ qua nếu chưa có đủ thông tin.
  </p>

  @forelse($familySacraments as $index => $row)
  <div class="rounded-xl border border-slate-200 p-3 text-sm">
    <div class="flex justify-between gap-2">
      <div>
        <p class="font-semibold text-slate-900">{{ $sacramentTypes[$row['type'] ?? ''] ?? ($row['type'] ?? '') }}</p>
        <p class="text-xs text-slate-500 mt-0.5">
          {{ $this->memberLabel($row['member_ref'] ?? null) }}
          @if(!empty($row['received_date'])) · {{ $row['received_date'] }} @endif
        </p>
      </div>
      <div class="flex gap-1 shrink-0">
        <button type="button" wire:click="openFamilySacramentForm({{ $index }})" class="px-2 py-1 text-xs rounded-lg border">Sửa</button>
        <button type="button" wire:click="removeFamilySacrament({{ $index }})" class="px-2 py-1 text-xs rounded-lg border border-red-200 text-red-600">Xóa</button>
      </div>
    </div>
  </div>
  @empty
  <p class="text-sm text-slate-500 text-center py-4">Chưa có bí tích nào.</p>
  @endforelse

  <button type="button" wire:click="openFamilySacramentForm"
    class="w-full py-2.5 rounded-xl border border-dashed border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50">
    + Thêm bí tích
  </button>

  @if($showFamilySacramentForm)
  <div class="rounded-xl border border-primary-200 bg-primary-50/40 p-4 space-y-3">
    <h3 class="text-sm font-bold text-slate-800">{{ $editingFamilySacramentIndex !== null ? 'Sửa bí tích' : 'Thêm bí tích' }}</h3>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Thành viên <span class="text-red-500">*</span></label>
      <select wire:model.defer="fs_member_ref" class="{{ $input }}">
        <option value="">-- Chọn --</option>
        @foreach($members as $m)
        <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
        @endforeach
      </select>
      @error('fs_member_ref') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
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
      <button type="button" wire:click="saveFamilySacrament" class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold">Lưu</button>
      <button type="button" wire:click="closeFamilySacramentForm" class="px-4 py-2 rounded-xl border text-sm">Hủy</button>
    </div>
  </div>
  @endif
</div>
