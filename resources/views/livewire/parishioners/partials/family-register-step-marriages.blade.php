@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
  <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
    Thêm hôn phối của cha–mẹ, vợ–chồng... Chọn chồng và vợ từ danh sách thành viên đã khai ở bước trước.
  </p>

  @forelse($familyMarriages as $index => $marriage)
  <div class="rounded-xl border border-slate-200 p-3">
    <div class="flex justify-between gap-2">
      <div class="text-sm">
        <p class="font-semibold text-slate-900">
          {{ $this->memberLabel($marriage['husband_ref'] ?? null) }}
          <span class="text-slate-400">&</span>
          {{ $this->memberLabel($marriage['wife_ref'] ?? null) }}
        </p>
        <p class="text-xs text-slate-500 mt-1">
          @if(!empty($marriage['married_date'])) Ngày: {{ $marriage['married_date'] }} @endif
          @if(!empty($marriage['certificate_number'])) · Số HP: {{ $marriage['certificate_number'] }} @endif
        </p>
      </div>
      <div class="flex gap-1 shrink-0">
        <button type="button" wire:click="openMarriageForm({{ $index }})" class="px-2 py-1 text-xs rounded-lg border">Sửa</button>
        <button type="button" wire:click="removeMarriage({{ $index }})" class="px-2 py-1 text-xs rounded-lg border border-red-200 text-red-600">Xóa</button>
      </div>
    </div>
  </div>
  @empty
  <p class="text-sm text-slate-500 text-center py-4">Chưa có hôn phối. Bạn có thể bỏ qua nếu chưa có thông tin.</p>
  @endforelse

  <button type="button" wire:click="openMarriageForm"
    class="w-full py-2.5 rounded-xl border border-dashed border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50">
    + Thêm hôn phối
  </button>

  @if($showMarriageForm)
  <div class="rounded-xl border border-primary-200 bg-primary-50/40 p-4 space-y-3">
    <h3 class="text-sm font-bold text-slate-800">{{ $editingMarriageIndex !== null ? 'Sửa hôn phối' : 'Thêm hôn phối' }}</h3>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Chồng <span class="text-red-500">*</span></label>
      <select wire:model.defer="marriage_husband_ref" class="{{ $input }}">
        <option value="">-- Chọn --</option>
        @foreach($members as $m)
        <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
        @endforeach
      </select>
      @error('marriage_husband_ref') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Vợ <span class="text-red-500">*</span></label>
      <select wire:model.defer="marriage_wife_ref" class="{{ $input }}">
        <option value="">-- Chọn --</option>
        @foreach($members as $m)
        <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
        @endforeach
      </select>
      @error('marriage_wife_ref') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Ngày hôn phối</label>
      <input wire:model.defer="marriage_married_date" type="date" class="{{ $input }}" />
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Số hôn phối</label>
      <input wire:model.defer="marriage_certificate_number" type="text" class="{{ $input }}" />
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Giáo xứ / Nơi hôn phối</label>
      <input wire:model.defer="marriage_parish_name" type="text" class="{{ $input }}" />
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Linh mục chứng hôn</label>
      <input wire:model.defer="marriage_priest_witness" type="text" class="{{ $input }}" />
    </div>
    <div class="grid grid-cols-1 gap-3">
      <input wire:model.defer="marriage_witness_1" type="text" class="{{ $input }}" placeholder="Nhân chứng 1" />
      <input wire:model.defer="marriage_witness_2" type="text" class="{{ $input }}" placeholder="Nhân chứng 2" />
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Tình trạng</label>
      <select wire:model.defer="marriage_status" class="{{ $input }}">
        @foreach($marriageStatuses as $val => $label)
        <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
      <textarea wire:model.defer="marriage_note" rows="2" class="{{ $input }} resize-none"></textarea>
    </div>
    <div class="flex gap-2">
      <button type="button" wire:click="saveMarriage" class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold">Lưu</button>
      <button type="button" wire:click="closeMarriageForm" class="px-4 py-2 rounded-xl border text-sm">Hủy</button>
    </div>
  </div>
  @endif
</div>
