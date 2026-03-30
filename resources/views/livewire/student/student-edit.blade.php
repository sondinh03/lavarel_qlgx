<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ===== BREADCRUMB ===== --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Học sinh', 'url' => route('classes.index')],
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
                <svg class="animate-spin h-10 w-10 text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p class="text-sm text-slate-500">Đang tải dữ liệu...</p>
            </div>
        </div>

        @else

        {{-- ===== MAIN CARD ===== --}}
        {{--
            x-data: Alpine quản lý tab — không cần server biết
            tab mặc định 'basic', chuyển tab = đổi biến JS, không có HTTP roundtrip
        --}}
        <div
            x-data="{ tab: 'basic' }"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">
                            {{ $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới' }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ $isEdit ? 'Cập nhật thông tin học sinh giáo lý' : 'Điền đầy đủ thông tin để thêm học sinh mới' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold
                        {{ $isEdit ? 'bg-blue-100 text-blue-700' : 'bg-primary-100 text-primary-700' }}">
                        {{ $isEdit ? 'Chế độ sửa' : 'Tạo mới' }}
                    </span>
                </div>
            </div>

            {{-- TABS — Alpine driven, zero roundtrip --}}
            <div class="px-6 pt-4 pb-0 border-b border-slate-200 bg-white">
                <nav class="flex gap-1" role="tablist">
                    {{-- Tab: Cơ bản --}}
                    <button
                        type="button"
                        role="tab"
                        @click="tab = 'basic'"
                        :aria-selected="tab === 'basic'"
                        :class="tab === 'basic'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="inline-flex items-center gap-2 px-4 py-3 text-sm transition-all duration-200">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Thông tin cơ bản
                    </button>

                    {{-- Tab: Khác --}}
                    <button
                        type="button"
                        role="tab"
                        @click="tab = 'other'"
                        :aria-selected="tab === 'other'"
                        :class="tab === 'other'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="inline-flex items-center gap-2 px-4 py-3 text-sm transition-all duration-200">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Thông tin khác
                    </button>
                </nav>
            </div>

            {{-- FORM --}}
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
                    {{--
                        x-show thay vì @if:
                        - DOM luôn tồn tại → wire:model.defer hoạt động đúng dù tab bị ẩn
                        - Alpine toggle display:none → không tốn roundtrip
                    --}}
                    <div x-show="tab === 'basic'" x-cloak>
                        <div class="space-y-6">

                            {{-- SECTION: Thông tin cá nhân --}}
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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    {{-- Họ --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Họ <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model.defer="last_name"
                                            placeholder="Nguyễn"
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
                                        <input
                                            type="text"
                                            wire:model.defer="first_name"
                                            placeholder="Văn An"
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
                                        <select
                                            wire:model.defer="gender"
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
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Ngày sinh
                                        </label>
                                        <input
                                            type="date"
                                            wire:model.defer="birthday"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                        @error('birthday')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Điện thoại --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Điện thoại
                                        </label>
                                        <input
                                            type="tel"
                                            wire:model.defer="phone"
                                            placeholder="0123 456 789"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                        @error('phone')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Email
                                        </label>
                                        <input
                                            type="email"
                                            wire:model.defer="email"
                                            placeholder="email@example.com"
                                            class="w-full px-3 py-2 rounded-xl border text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all
                                                   {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                                        @error('email')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            {{-- SECTION: Gia đình --}}
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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên cha</label>
                                        <input
                                            type="text"
                                            wire:model.defer="father_name"
                                            placeholder="Họ và tên cha"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên mẹ</label>
                                        <input
                                            type="text"
                                            wire:model.defer="mother_name"
                                            placeholder="Họ và tên mẹ"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all" />
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION: Giáo xứ --}}
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

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                    {{-- Giáo xứ — wire:model (không defer) để trigger updatedParishId --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Giáo xứ <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            wire:model="parish_id"
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

                                    {{-- Giáo họ — disable khi chưa chọn giáo xứ --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Giáo họ
                                        </label>
                                        <select
                                            wire:model.defer="parish_group_id"
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

                                    {{-- Thánh bổn mạng --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                            Thánh bổn mạng
                                        </label>
                                        <select
                                            wire:model.defer="saint_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                                            <option value="">-- Chọn thánh --</option>
                                            @foreach($saints as $saint)
                                            <option value="{{ $saint->id }}">{{ $saint->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

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
                                        <input
                                            id="is_active"
                                            type="checkbox"
                                            wire:model.defer="is_active"
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
                                <textarea
                                    wire:model.defer="note"
                                    rows="5"
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

                {{-- FORM ACTIONS --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-4">

                    {{-- Cancel — dùng link thay vì wire:click để tránh roundtrip --}}
                    <a
                        href="{{ $isEdit ? route('students.show', $studentId) : route('classes.index') }}"
                        onclick="return !document.querySelector('form')?.querySelector('[wire\\:model\\.defer]') || confirm('Thay đổi chưa được lưu. Bạn có chắc muốn rời khỏi trang?')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200
                               bg-white text-sm font-medium text-slate-700
                               hover:bg-slate-50 hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hủy
                    </a>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-xl
                               bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium
                               hover:shadow-md transition-all duration-200
                               disabled:opacity-60 disabled:cursor-not-allowed">
                        {{-- Icon: normal --}}
                        <svg wire:loading.remove wire:target="save"
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{-- Icon: loading --}}
                        <svg wire:loading wire:target="save"
                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>

                        <span wire:loading.remove wire:target="save">
                            {{ $isEdit ? 'Cập nhật' : 'Tạo mới' }}
                        </span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>

                </div>

            </form>
        </div>{{-- end main card --}}

        @endif {{-- end isLoading --}}

    </div>
</div>
