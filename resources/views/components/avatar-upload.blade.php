@props([
    'wireModel' => 'avatar_path',
    'existing' => null,
    'inputId' => 'avatar_upload',
    'removeMethod' => 'removeAvatar',
    'sizeClass' => 'w-24 h-24',
])

<div
    {{ $attributes->class(['flex-shrink-0 flex flex-col items-center sm:items-start gap-2']) }}
    x-data="{
        avatarPreview: @js($existing ? media_url($existing) : ''),
        isDragging: false,
        handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => { this.avatarPreview = e.target.result; };
            reader.readAsDataURL(file);
        },
        isNewUpload() {
            return this.avatarPreview && this.avatarPreview.startsWith('data:');
        }
    }">
    <div class="relative {{ $sizeClass }} group">
        <template x-if="avatarPreview">
            <div class="relative">
                <img :src="avatarPreview"
                    class="{{ $sizeClass }} rounded-2xl object-cover shadow-mac-sm ring-4 ring-primary-50/80" alt="" />
                <span x-show="isNewUpload()"
                    class="absolute -top-1 -right-1 bg-primary-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-lg shadow-mac-sm">
                    Mới
                </span>
            </div>
        </template>
        <template x-if="!avatarPreview">
            <div class="{{ $sizeClass }} rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600
                flex items-center justify-center shadow-mac-sm ring-4 ring-primary-50/80">
                <svg class="w-9 h-9 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </template>
        <label for="{{ $inputId }}"
            class="absolute inset-0 rounded-2xl bg-black/0 group-hover:bg-black/30
                flex items-center justify-center cursor-pointer transition-all duration-200">
            <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </label>
    </div>

    <label for="{{ $inputId }}"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="isDragging = false; handleFile($event.dataTransfer.files[0])"
        :class="isDragging ? 'text-primary-600' : 'text-slate-500 hover:text-primary-600'"
        class="text-xs font-medium cursor-pointer transition-colors text-center sm:text-left">
        Đổi ảnh
    </label>

    <input id="{{ $inputId }}" type="file" accept="image/*" class="hidden"
        wire:model="{{ $wireModel }}"
        x-on:change="handleFile($event.target.files[0])" />

    @error($wireModel)
    <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror

    <template x-if="!isNewUpload() && avatarPreview">
        <button type="button"
            wire:click="{{ $removeMethod }}"
            x-on:click="avatarPreview = ''"
            class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-red-500 transition-colors">
            <x-icon name="trash" class="w-3 h-3" />
            Xóa
        </button>
    </template>
</div>
