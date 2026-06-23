@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Loại bí tích <span class="text-red-500">*</span></label>
        <select wire:model="type" class="{{ $input }} @error('type') border-red-400 @enderror">
            <option value="">-- Chọn --</option>
            @foreach($typeOptions as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Ngày lãnh nhận</label>
        <input wire:model.defer="received_date" type="date" class="{{ $input }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Số chứng chỉ</label>
        <input wire:model.defer="certificate_number" class="{{ $input }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Số sách</label>
        <input wire:model.defer="book_number" type="number" min="1" class="{{ $input }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Người ban bí tích</label>
        <input wire:model.defer="giver" class="{{ $input }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Người đỡ đầu</label>
        <input wire:model.defer="sponsor" class="{{ $input }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Giáo xứ / Nơi lãnh nhận</label>
        <input wire:model.defer="parish_name" class="{{ $input }}" placeholder="Tên giáo xứ">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700 mb-1 block">Nhà thờ / Họ đạo cụ thể</label>
        <input wire:model.defer="church_name" class="{{ $input }}" placeholder="VD: Nhà thờ Hành Đông">
    </div>

    @if($type === 'anointing')
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700 mb-1 block">Tình trạng (Xức dầu)</label>
        <input wire:model.defer="anointing_condition" class="{{ $input }}" placeholder="VD: Bệnh nặng, Hấp hối, Phẫu thuật..." />
    </div>
    @endif

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700 mb-1 block">Ghi chú</label>
        <textarea wire:model.defer="note" rows="2" class="{{ $input }}"></textarea>
    </div>

</div>
