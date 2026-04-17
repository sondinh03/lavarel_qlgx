<div class="min-h-screen bg-slate-50 p-4 sm:p-6">
    <div class="max-w-4xl mx-auto space-y-5">

        {{-- ===== BREADCRUMB ===== --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Học sinh', 'url' => route('students.index')],
            ['label' => $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới'],
        ]" separator="arrow" />

        {{-- ===== TOAST ===== --}}
        @if(session('message'))
        <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session('error'))
        <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- ===== LOADING STATE ===== --}}
        @if($isLoading)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-16">
            <div class="flex flex-col items-center justify-center gap-4">
                <svg class="animate-spin h-8 w-8 text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p class="text-sm text-slate-500">Đang tải dữ liệu...</p>
            </div>
        </div>

        @else

        {{-- ===== MAIN CARD ===== --}}
        <div
            x-data="{
                tab: 'basic',
                avatarPreview: '{{ $avatarPreviewUrl ?? '' }}',
                isDragging: false,
                handleFile(file) {
                    if (!file || !file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = e => { this.avatarPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- ── HEADER ── --}}
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">
                            {{ $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới' }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ $isEdit ? 'Cập nhật thông tin học sinh giáo lý' : 'Điền đầy đủ thông tin để thêm học sinh mới' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold flex-shrink-0
                        {{ $isEdit ? 'bg-blue-100 text-blue-700' : 'bg-primary-100 text-primary-700' }}">
                        {{ $isEdit ? 'Chế độ sửa' : 'Tạo mới' }}
                    </span>
                </div>
            </div>

            {{-- ── TABS ── --}}
            <div class="px-6 pt-0 border-b border-slate-200 bg-white">
                <nav class="flex gap-1" role="tablist">

                    <button type="button" role="tab"
                        @click="tab = 'basic'"
                        :aria-selected="tab === 'basic'"
                        :class="tab === 'basic'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="inline-flex items-center gap-2 px-4 py-3.5 text-sm transition-all duration-200">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Thông tin cơ bản
                    </button>

                    <button type="button" role="tab"
                        @click="tab = 'other'"
                        :aria-selected="tab === 'other'"
                        :class="tab === 'other'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="inline-flex items-center gap-2 px-4 py-3.5 text-sm transition-all duration-200">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Thông tin khác
                    </button>

                </nav>
            </div>

            {{-- ── FORM ── --}}
            <form wire:submit.prevent="save">

                {{-- ERROR SUMMARY --}}
                @if($errors->any())
                <div class="mx-6 mt-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại</p>
                            <ul class="text-sm text-red-700 space-y-0.5">
                                @foreach($errors->all() as $error)
                                <li>· {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div class="p-6 space-y-6">

                    {{-- ==================== TAB: CƠ BẢN ==================== --}}
                    <div x-show="tab === 'basic'" x-cloak class="space-y-5">

                        {{-- ── SECTION: Avatar + Định danh ── --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                Ảnh đại diện
                            </h2>

                            <div class="flex flex-col sm:flex-row items-start gap-6">

                                {{-- Preview --}}
                                <div class="flex-shrink-0">
                                    <div class="relative w-28 h-28 group">
                                        {{-- 1. Preview (ưu tiên cao nhất)  --}}
                                        <template x-if="avatarPreview">
                                            <img :src="avatarPreview" alt="Avatar preview"
                                                class="w-28 h-28 rounded-2xl object-cover shadow-md ring-4 ring-primary-50" />
                                        </template>

                                        {{-- 2. Ảnh từ DB --}}
                                        <template x-if="!avatarPreview">
                                            @if($avatar_path)
                                            <img src="{{ asset($avatar_path) }}"
                                                alt="Avatar"
                                                class="w-28 h-28 rounded-2xl object-cover shadow-md ring-4 ring-primary-50" />
                                            @else
                                            {{-- 3. Fallback icon --}}
                                            <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600
                            flex items-center justify-center shadow-md ring-4 ring-primary-50">
                                                <svg class="w-10 h-10 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            @endif
                                        </template>

                                        {{-- Overlay edit icon --}}
                                        <label for="avatar_upload"
                                            class="absolute inset-0 rounded-2xl bg-black/0 group-hover:bg-black/30
                                                   flex items-center justify-center cursor-pointer transition-all duration-200">
                                            <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 drop-shadow"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </label>
                                    </div>
                                </div>

                                {{-- Upload zone --}}
                                <div class="flex-1 w-full">
                                    {{-- Drop zone --}}
                                    <label for="avatar_upload"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="isDragging = false; handleFile($event.dataTransfer.files[0])"
                                        :class="isDragging
                                            ? 'border-primary-400 bg-primary-50'
                                            : 'border-slate-300 bg-white hover:border-primary-300 hover:bg-primary-50/40'"
                                        class="flex flex-col items-center justify-center gap-2 w-full h-28 border-2 border-dashed
                                               rounded-xl cursor-pointer transition-all duration-200 group">

                                        <svg class="w-7 h-7 text-slate-400 group-hover:text-primary-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div class="text-center">
                                            <span class="text-sm font-medium text-primary-600 group-hover:text-primary-700">Chọn ảnh</span>
                                            <span class="text-sm text-slate-500"> hoặc kéo thả vào đây</span>
                                        </div>
                                        <p class="text-xs text-slate-400">PNG, JPG, WEBP · Tối đa 2MB</p>
                                    </label>

                                    {{-- Hidden input --}}
                                    <input id="avatar_upload" type="file" accept="image/*" class="hidden"
                                        wire:model="avatar_path"
                                        x-on:change="handleFile($event.target.files[0])" />

                                    @error('avatar_path')
                                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                    @enderror

                                    {{-- Remove button --}}
                                    <template x-if="avatarPreview">
                                        <button type="button"
                                            wire:click="removeAvatar"
                                            @click="avatarPreview = null"
                                            class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Xóa ảnh
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- ── SECTION: Thông tin cá nhân ── --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                Thông tin cá nhân
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                {{-- Thánh bổn mạng (tên thánh) --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Thánh bổn mạng
                                    </label>
                                    <select wire:model.defer="saint_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                                        <option value="">-- Chọn thánh bổn mạng --</option>
                                        @foreach($saints as $saint)
                                        <option value="{{ $saint->id }}">{{ $saint->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('saint_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Họ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Họ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.defer="last_name" placeholder="Nguyễn"
                                        class="w-full px-3 py-2 rounded-xl border text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                               {{ $errors->has('last_name') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('last_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tên --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Tên <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.defer="first_name" placeholder="Văn An"
                                        class="w-full px-3 py-2 rounded-xl border text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                               {{ $errors->has('first_name') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('first_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giới tính --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Giới tính <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.defer="gender"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                                        <option value="male">Nam</option>
                                        <option value="female">Nữ</option>
                                    </select>
                                    @error('gender')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Ngày sinh --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ngày sinh</label>
                                    <input type="date" wire:model.defer="birthday"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    @error('birthday')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Điện thoại --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Điện thoại</label>
                                    <input type="tel" wire:model.defer="phone" placeholder="0123 456 789"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                                    <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                        class="w-full px-3 py-2 rounded-xl border text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                               {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── SECTION: Gia đình ── --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </span>
                                Gia đình
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên cha</label>
                                    <input type="text" wire:model.defer="father_name" placeholder="Họ và tên cha"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên mẹ</label>
                                    <input type="text" wire:model.defer="mother_name" placeholder="Họ và tên mẹ"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                </div>
                            </div>
                        </div>

                        {{-- ── SECTION: Giáo xứ ── --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                Giáo xứ
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                {{-- Giáo xứ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Giáo xứ <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="parish_id"
                                        class="w-full px-3 py-2 rounded-xl border text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                               {{ $errors->has('parish_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                                        <option value="">-- Chọn giáo xứ --</option>
                                        @foreach($parishes as $parish)
                                        <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('parish_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giáo họ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Giáo họ</label>
                                    <select wire:model.defer="parish_group_id"
                                        @disabled(!$parish_id)
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                               disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                                        <option value="">-- Chọn giáo họ --</option>
                                        @foreach($parishGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                    </div>{{-- end tab basic --}}

                    {{-- ==================== TAB: KHÁC ==================== --}}
                    <div x-show="tab === 'other'" x-cloak>
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 space-y-5">

                            <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                Thông tin bổ sung
                            </h2>

                            {{-- Trạng thái --}}
                            <div class="bg-white rounded-xl border border-slate-200 p-4">
                                <label class="flex items-start gap-3 cursor-pointer select-none">
                                    <div class="relative flex items-center mt-0.5">
                                        <input id="is_active" type="checkbox" wire:model.defer="is_active"
                                            class="w-4 h-4 rounded border-slate-300 text-primary-500
                                                   focus:ring-2 focus:ring-primary-500 focus:ring-offset-0 transition-all" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-slate-700">Đang học (kích hoạt)</span>
                                        <p class="text-xs text-slate-500 mt-0.5">Học sinh đang theo học tại lớp giáo lý</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Ghi chú --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ghi chú</label>
                                <textarea wire:model.defer="note" rows="5"
                                    placeholder="Ghi chú thêm về học sinh..."
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all resize-none"></textarea>
                                @error('note')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>{{-- end tab other --}}

                </div>

                {{-- ── FORM ACTIONS ── --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-4">

                    <a href="{{ $isEdit ? route('students.show', $studentId) : route('students.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200
                               bg-white text-sm font-medium text-slate-700
                               hover:bg-slate-50 hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hủy
                    </a>

                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-xl
                               bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold
                               hover:shadow-md active:scale-95 transition-all duration-200
                               disabled:opacity-60 disabled:cursor-not-allowed">

                        <svg wire:loading.remove wire:target="save"
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading wire:target="save"
                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>

                        <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Cập nhật' : 'Tạo mới' }}</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>

                </div>

            </form>
        </div>{{-- end main card --}}

        @endif
    </div>
</div>