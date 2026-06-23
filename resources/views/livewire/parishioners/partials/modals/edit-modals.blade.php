@php
$modalShell = 'fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4';
$modalBox = 'bg-white rounded-2xl shadow-xl w-full max-h-[90vh] flex flex-col overflow-hidden';
@endphp

@if($showEditBasic)
<div class="{{ $modalShell }}" wire:click="$set('showEditBasic', false)">
    <div class="{{ $modalBox }} max-w-3xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa cơ bản & phân loại</h2>
            <p class="text-sm text-slate-500 mt-1">Thông tin nhân thân, phân loại và trạng thái</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            @include('livewire.parishioners.partials.forms.sections.basic-group')
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-3">
            <x-button type="button" variant="outline" wire:click="$set('showEditBasic', false)">Hủy</x-button>
            <x-button type="button" variant="primary" wire:click="saveBasic" wire:loading.attr="disabled" wire:target="saveBasic,avatar">
                <span wire:loading.remove wire:target="saveBasic">Lưu</span>
                <span wire:loading wire:target="saveBasic">Đang lưu...</span>
            </x-button>
        </div>
    </div>
</div>
@endif

@if($showEditAddress)
<div class="{{ $modalShell }}" wire:click="$set('showEditAddress', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa địa chỉ</h2>
            <p class="text-sm text-slate-500 mt-1">Quê quán, thường trú và tạm trú</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            <x-form-section-card title="Quê quán & Địa chỉ" icon="map">
                @include('livewire.parishioners.partials.forms.address-fields')
            </x-form-section-card>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-3">
            <x-button type="button" variant="outline" wire:click="$set('showEditAddress', false)">Hủy</x-button>
            <x-button type="button" variant="primary" wire:click="saveAddress" wire:loading.attr="disabled" wire:target="saveAddress">Lưu</x-button>
        </div>
    </div>
</div>
@endif

@if($showEditParish)
<div class="{{ $modalShell }}" wire:click="$set('showEditParish', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa sinh hoạt xứ</h2>
            <p class="text-sm text-slate-500 mt-1">Giáo họ, cấp bậc và thông tin gia nhập</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            <x-form-section-card title="Sinh hoạt giáo xứ" icon="church">
                @include('livewire.parishioners.partials.forms.parish-fields')
            </x-form-section-card>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-3">
            <x-button type="button" variant="outline" wire:click="$set('showEditParish', false)">Hủy</x-button>
            <x-button type="button" variant="primary" wire:click="saveParish" wire:loading.attr="disabled" wire:target="saveParish">Lưu</x-button>
        </div>
    </div>
</div>
@endif

@if($showEditFamily)
<div class="{{ $modalShell }}" wire:click="$set('showEditFamily', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa thông tin cá nhân</h2>
            <p class="text-sm text-slate-500 mt-1">Tên cha/mẹ (văn bản) và tình trạng hôn nhân dân sự. Hộ gia đình quản lý tại menu Gia đình.</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            <x-form-section-card title="Thông tin cá nhân (gia đình)" icon="family">
                @include('livewire.parishioners.partials.forms.family-fields')
            </x-form-section-card>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-3">
            <x-button type="button" variant="outline" wire:click="$set('showEditFamily', false)">Hủy</x-button>
            <x-button type="button" variant="primary" wire:click="saveFamily" wire:loading.attr="disabled" wire:target="saveFamily">Lưu</x-button>
        </div>
    </div>
</div>
@endif

@if($showEditMarriage)
<div class="{{ $modalShell }}" wire:click="$set('showEditMarriage', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa hôn phối</h2>
            <p class="text-sm text-slate-500 mt-1">Thông tin hôn phối và giấy chứng nhận</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            <x-form-section-card title="Chi tiết hôn phối" icon="heart">
                @include('livewire.parishioners.partials.forms.marriage-fields')
            </x-form-section-card>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-between gap-3">
            @if($marriage_id)
            <x-button type="button" variant="danger" wire:click="deleteMarriage">Xóa hôn phối</x-button>
            @else
            <span></span>
            @endif
            <div class="flex gap-3">
                <x-button type="button" variant="outline" wire:click="$set('showEditMarriage', false)">Hủy</x-button>
                <x-button type="button" variant="primary" wire:click="saveMarriage" wire:loading.attr="disabled" wire:target="saveMarriage">Lưu</x-button>
            </div>
        </div>
    </div>
</div>
@endif

@if($showEditDeceased)
<div class="{{ $modalShell }}" wire:click="$set('showEditDeceased', false)">
    <div class="{{ $modalBox }} max-w-2xl" wire:click.stop>
        <div class="flex-shrink-0 px-6 py-5 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Thông tin tử vong</h2>
            <p class="text-sm text-slate-500 mt-1">Ghi nhận hoặc cập nhật thông tin qua đời</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            @if($errors->any())
                @include('livewire.parishioners.partials.forms.form-errors')
            @endif
            <x-form-section-card title="Thông tin qua đời" icon="document">
                @include('livewire.parishioners.partials.forms.deceased-fields')
            </x-form-section-card>
        </div>
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-3">
            <x-button type="button" variant="outline" wire:click="$set('showEditDeceased', false)">Hủy</x-button>
            <x-button type="button" variant="primary" wire:click="saveDeceased" wire:loading.attr="disabled" wire:target="saveDeceased">Lưu</x-button>
        </div>
    </div>
</div>
@endif
