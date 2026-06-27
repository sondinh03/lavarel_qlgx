<div
    x-data="{
        avatarPreview: '{{ $currentAvatarPath ? avatar_url($currentAvatarPath) : '' }}',
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
    }"
    class="flex flex-col sm:flex-row items-start gap-6">
    <div class="flex-shrink-0">
        <div class="relative w-28 h-28 group">
            <template x-if="avatarPreview">
                <div class="relative">
                    <img :src="avatarPreview"
                        class="w-28 h-28 rounded-2xl object-cover shadow-sm ring-4 ring-primary-50" alt="" />
                    <span x-show="isNewUpload()"
                        class="absolute -top-1 -right-1 bg-primary-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                        Mới
                    </span>
                </div>
            </template>
            <template x-if="!avatarPreview">
                <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600
                    flex items-center justify-center shadow-sm ring-4 ring-primary-50">
                    <svg class="w-10 h-10 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </template>
            <label for="parishioner_avatar_upload"
                class="absolute inset-0 rounded-2xl bg-black/0 group-hover:bg-black/30
                    flex items-center justify-center cursor-pointer transition-all duration-200">
                <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </label>
        </div>
    </div>

    <div class="flex-1 w-full min-w-0">
        <label for="parishioner_avatar_upload"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="isDragging = false; handleFile($event.dataTransfer.files[0])"
            :class="isDragging
                ? 'border-primary-400 bg-primary-50'
                : 'border-slate-300 bg-white hover:border-primary-300 hover:bg-primary-50/40'"
            class="flex flex-col items-center justify-center gap-2 w-full h-28 border-2 border-dashed
                rounded-xl cursor-pointer transition-all group">
            <svg class="w-7 h-7 text-slate-400 group-hover:text-primary-500 transition-colors"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div class="text-center">
                <span class="text-sm font-medium text-primary-600">Chọn ảnh</span>
                <span class="text-sm text-slate-500"> hoặc kéo thả vào đây</span>
            </div>
            <p class="text-xs text-slate-400">PNG, JPG, WEBP · Tối đa 2MB</p>
        </label>

        <input id="parishioner_avatar_upload" type="file" accept="image/*" class="hidden"
            wire:model="avatar"
            x-on:change="handleFile($event.target.files[0])" />

        @error('avatar')
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>
