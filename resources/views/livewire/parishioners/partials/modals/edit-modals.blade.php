@php
$modalShell = 'fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4';
$modalBox = 'bg-white rounded-2xl shadow-xl w-full max-h-[90vh] flex flex-col overflow-hidden';
@endphp

@if($showEditBasic)
<div class="{{ $modalShell }}" wire:click="$set('showEditBasic', false)">
    <div class="{{ $modalBox }} max-w-3xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa cơ bản & phân loại</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            @include('livewire.parishioners.partials.forms.basic-fields')
            @include('livewire.parishioners.partials.forms.classify-fields')
            @include('livewire.parishioners.partials.forms.status-fields')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                <textarea wire:model.defer="note" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            </div>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
            <button type="button" wire:click="$set('showEditBasic', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
            <button type="button" wire:click="saveBasic" wire:loading.attr="disabled" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
        </div>
    </div>
</div>
@endif

@if($showEditAddress)
<div class="{{ $modalShell }}" wire:click="$set('showEditAddress', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b"><h2 class="text-xl font-bold">Chỉnh sửa địa chỉ</h2></div>
        <div class="flex-1 overflow-y-auto p-6">@include('livewire.parishioners.partials.forms.address-fields')</div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
            <button type="button" wire:click="$set('showEditAddress', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
            <button type="button" wire:click="saveAddress" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
        </div>
    </div>
</div>
@endif

@if($showEditParish)
<div class="{{ $modalShell }}" wire:click="$set('showEditParish', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b"><h2 class="text-xl font-bold">Chỉnh sửa sinh hoạt xứ</h2></div>
        <div class="flex-1 overflow-y-auto p-6">@include('livewire.parishioners.partials.forms.parish-fields')</div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
            <button type="button" wire:click="$set('showEditParish', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
            <button type="button" wire:click="saveParish" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
        </div>
    </div>
</div>
@endif

@if($showEditFamily)
<div class="{{ $modalShell }}" wire:click="$set('showEditFamily', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b"><h2 class="text-xl font-bold">Chỉnh sửa gia đình</h2></div>
        <div class="flex-1 overflow-y-auto p-6">@include('livewire.parishioners.partials.forms.family-fields')</div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
            <button type="button" wire:click="$set('showEditFamily', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
            <button type="button" wire:click="saveFamily" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
        </div>
    </div>
</div>
@endif

@if($showEditMarriage)
<div class="{{ $modalShell }}" wire:click="$set('showEditMarriage', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b"><h2 class="text-xl font-bold">Chỉnh sửa hôn phối</h2></div>
        <div class="flex-1 overflow-y-auto p-6">@include('livewire.parishioners.partials.forms.marriage-fields')</div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-between gap-3">
            @if($marriage_id)
            <button type="button" wire:click="deleteMarriage" class="px-4 py-2 text-sm text-red-600 border border-red-200 rounded-xl">Xóa hôn phối</button>
            @else
            <span></span>
            @endif
            <div class="flex gap-3">
                <button type="button" wire:click="$set('showEditMarriage', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
                <button type="button" wire:click="saveMarriage" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($showEditDeceased)
<div class="{{ $modalShell }}" wire:click="$set('showEditDeceased', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 p-6 border-b"><h2 class="text-xl font-bold">Thông tin tử vong</h2></div>
        <div class="flex-1 overflow-y-auto p-6">@include('livewire.parishioners.partials.forms.deceased-fields')</div>
        <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
            <button type="button" wire:click="$set('showEditDeceased', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
            <button type="button" wire:click="saveDeceased" class="px-5 py-2 text-sm text-white bg-primary-600 rounded-xl">Lưu</button>
        </div>
    </div>
</div>
@endif
