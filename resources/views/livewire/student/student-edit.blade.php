@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'Học sinh', 'url' => route('students.index')],
    ['label' => $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#student-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-form-main" class="mx-auto max-w-4xl space-y-6">

        <div
            x-data="{
                avatarPreview: '{{ $existing_avatar ? asset($existing_avatar) : '' }}',
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
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <x-page-header
                class="rounded-t-2xl"
                icon-type="students"
                :title="$isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới'"
                :description="$isEdit ? 'Cập nhật thông tin học sinh giáo lý' : 'Điền đầy đủ thông tin để thêm học sinh mới'">
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-primary-100 text-primary-700">
                        {{ $isEdit ? 'Chế độ sửa' : 'Tạo mới' }}
                    </span>
                </x-slot>
            </x-page-header>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="inline-flex w-full sm:w-auto rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button type="button" wire:click="switchTab('basic')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all
                            {{ $activeTab === 'basic'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Thông tin cơ bản
                    </button>

                    <button type="button" wire:click="switchTab('other')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all
                            {{ $activeTab === 'other'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Thông tin khác
                    </button>
                </div>
            </div>

            <form wire:submit.prevent="save">
                @if($errors->any())
                <div class="mx-4 lg:mx-6 mt-5 p-4 bg-red-50 border border-red-200 rounded-xl">
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

                <div class="p-4 lg:p-6 space-y-6">

                    @if($activeTab === 'basic')
                    <div class="space-y-5">

                        {{-- Avatar --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                Ảnh đại diện
                            </h2>

                            <div class="flex flex-col sm:flex-row items-start gap-6">
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
                                        <label for="avatar_upload"
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
                                    <label for="avatar_upload"
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

                                    <input id="avatar_upload" type="file" accept="image/*" class="hidden"
                                        wire:model="avatar_path"
                                        x-on:change="handleFile($event.target.files[0])" />

                                    @error('avatar_path')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                    @enderror

                                    <template x-if="!isNewUpload() && avatarPreview">
                                        <button type="button"
                                            wire:click="removeAvatar"
                                            x-on:click="avatarPreview = ''"
                                            class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-500 transition-colors">
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                            Xóa ảnh
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Thông tin cá nhân --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                Thông tin cá nhân
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên thánh</label>
                                    <x-searchable-select
                                        wireModel="saint_id"
                                        :options="$this->saints"
                                        placeholder="-- Chọn --"
                                        labelKey="name"
                                        valueKey="id"
                                        :value="$this->saint_id" />
                                    @error('saint_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Họ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.defer="last_name" placeholder="Nguyễn"
                                        class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                            {{ $errors->has('last_name') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('last_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Tên <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.defer="first_name" placeholder="An"
                                        class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                            {{ $errors->has('first_name') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('first_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-1">
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

                                <div class="sm:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ngày sinh</label>
                                    <input type="date" wire:model.defer="birthday"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                            focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    @error('birthday')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Gia đình --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Điện thoại</label>
                                    <input type="tel" wire:model.defer="phone" placeholder="0123 456 789"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                            focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                                    <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                        class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                            {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                    @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Giáo xứ --}}
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                Giáo xứ
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Giáo xứ</label>
                                    <x-searchable-select
                                        wireModel="parish_id"
                                        :options="$this->parishes"
                                        placeholder="-- Chọn giáo xứ --"
                                        labelKey="name"
                                        valueKey="id"
                                        :value="$this->parish_id" />
                                    @error('parish_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Giáo họ</label>
                                    <x-searchable-select
                                        wireModel="parish_group_id"
                                        :options="$this->parishGroups"
                                        placeholder="-- Chọn giáo họ --"
                                        labelKey="name"
                                        valueKey="id"
                                        :value="$this->parish_group_id" />
                                    @error('parish_group_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    @endif

                    @if($activeTab === 'other')
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 space-y-5">
                        <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            Thông tin bổ sung
                        </h2>

                        <div class="bg-white rounded-xl border border-slate-200 p-4">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input id="is_active" type="checkbox" wire:model.defer="is_active"
                                    class="w-4 h-4 mt-0.5 rounded border-slate-300 text-primary-500 focus:ring-primary-500" />
                                <div>
                                    <span class="text-sm font-semibold text-slate-700">Đang học (kích hoạt)</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Học sinh đang theo học tại lớp giáo lý</p>
                                </div>
                            </label>
                        </div>

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
                    @endif

                </div>

                <div class="px-4 lg:px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex items-center justify-end gap-3 rounded-b-2xl">
                    <x-button as="a" variant="outline" href="{{
                        $isEdit
                            ? route('students.show', $studentId)
                            : route('students.index', $classId ? ['class' => $classId] : [])
                    }}">
                        <x-icon name="cancel" />
                        Hủy
                    </x-button>

                    <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="avatar_path,save">
                        <x-icon name="save" />
                        Lưu
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
