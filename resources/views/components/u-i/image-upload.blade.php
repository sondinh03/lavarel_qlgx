@props([
    'model',          // wire:model
    'imageUrl' => null,
    'size' => 112,    // px
])

<div
    x-data="{
        preview: null,
        isDragging: false,
        handle(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => this.preview = e.target.result;
            reader.readAsDataURL(file);
        }
    }"
    class="flex flex-col sm:flex-row gap-5 items-start">

    {{-- PREVIEW --}}
    <div class="relative group"
        style="width: {{ $size }}px; height: {{ $size }}px">

        {{-- Preview mới --}}
        <template x-if="preview">
            <img :src="preview"
                class="w-full h-full rounded-2xl object-cover shadow-md ring-4 ring-primary-50" />
        </template>

        {{-- Ảnh DB --}}
        <template x-if="!preview">
            @if($imageUrl)
                <img src="{{ asset($imageUrl) }}"
                    class="w-full h-full rounded-2xl object-cover shadow-md ring-4 ring-primary-50" />
            @else
                {{-- fallback --}}
                <div class="w-full h-full rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600
                            flex items-center justify-center shadow-md ring-4 ring-primary-50">
                    <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            @endif
        </template>

        {{-- overlay --}}
        <label :for="'upload-' + @js($model)"
            class="absolute inset-0 rounded-2xl bg-black/0 group-hover:bg-black/30
                   flex items-center justify-center cursor-pointer transition">

            <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93..."/>
            </svg>
        </label>
    </div>

    {{-- DROP ZONE --}}
    <div class="flex-1 w-full">
        <label
            :for="'upload-' + @js($model)"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="isDragging = false; handle($event.dataTransfer.files[0])"
            :class="isDragging
                ? 'border-primary-400 bg-primary-50'
                : 'border-slate-300 bg-white hover:border-primary-300'"
            class="flex flex-col items-center justify-center gap-2 w-full h-28
                   border-2 border-dashed rounded-xl cursor-pointer transition">

            <span class="text-sm text-primary-600 font-medium">
                Chọn ảnh
            </span>
            <span class="text-xs text-slate-400">
                PNG, JPG · tối đa 2MB
            </span>
        </label>

        <input
            type="file"
            :id="'upload-' + @js($model)"
            class="hidden"
            accept="image/*"
            wire:model="{{ $model }}"
            x-on:change="handle($event.target.files[0])"
        />

        {{-- ERROR --}}
        @error($model)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror

        {{-- REMOVE --}}
        <template x-if="preview">
            <button type="button"
                @click="preview = null; $wire.set('{{ $model }}', null)"
                class="mt-2 text-xs text-slate-500 hover:text-red-500">
                Xóa ảnh
            </button>
        </template>
    </div>
</div>