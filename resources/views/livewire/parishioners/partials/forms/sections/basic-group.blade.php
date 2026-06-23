<div class="space-y-5">
    <x-form-section-card title="Ảnh đại diện" icon="photo">
        @include('livewire.parishioners.partials.forms.avatar-field')
    </x-form-section-card>

    <x-form-section-card title="Thông tin cá nhân" icon="user">
        @include('livewire.parishioners.partials.forms.basic-fields')
    </x-form-section-card>

    <x-form-section-card title="Phân loại" icon="tag">
        @include('livewire.parishioners.partials.forms.classify-fields')
    </x-form-section-card>

    <x-form-section-card title="Trạng thái" icon="document">
        @include('livewire.parishioners.partials.forms.status-fields')
    </x-form-section-card>

    <x-form-section-card title="Ghi chú" icon="document">
        <textarea wire:model.defer="note" rows="3" placeholder="Ghi chú thêm về giáo dân..."
            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
        @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </x-form-section-card>
</div>
