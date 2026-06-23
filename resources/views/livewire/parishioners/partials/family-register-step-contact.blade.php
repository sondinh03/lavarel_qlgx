@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-1">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
    <input wire:model.defer="contact_phone" type="tel" inputmode="tel" class="{{ $input }}" />
    @error('contact_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-1">Người đăng ký (trong hộ) <span class="text-red-500">*</span></label>
    <select wire:model.defer="submitter_ref" class="{{ $input }}">
      <option value="">-- Chọn --</option>
      @foreach($members as $m)
      <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
      @endforeach
    </select>
    @error('submitter_ref') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-2 text-sm">
    <p class="font-semibold text-slate-800">Tóm tắt trước khi gửi</p>
    <p><span class="text-slate-500">Hộ:</span> {{ $family_name ?: '—' }}</p>
    <p><span class="text-slate-500">Thành viên:</span> {{ count($members) }} người</p>
    <p><span class="text-slate-500">Hôn phối:</span> {{ count($familyMarriages) }} bản ghi</p>
    <p><span class="text-slate-500">Bí tích:</span> {{ count($familySacraments) }} bản ghi</p>
  </div>
</div>
