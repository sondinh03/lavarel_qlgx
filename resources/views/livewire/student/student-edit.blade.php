<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#student-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-form-main" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Chi tiết học sinh', 'url' => route('students.show', ['id' => $studentId])],
            ['label' => $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới']
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">
                {{ session('error') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Loading State --}}
        @if($isLoading)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12">
            <div class="flex items-center justify-center gap-3">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg text-slate-700">Đang tải dữ liệu...</span>
            </div>
        </div>

        @else

        {{-- FORM CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới' }}
                        </h1>
                        <p class="text-sm text-slate-600 mt-1">
                            {{ $isEdit ? 'Cập nhật thông tin học sinh giáo lý' : 'Điền đầy đủ thông tin để thêm học sinh giáo lý' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                 {{ $isEdit ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                        {{ $isEdit ? 'Chế độ sửa' : 'Chế độ tạo mới' }}
                    </span>
                </div>
            </div>

            {{-- TABS --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium whitespace-nowrap">
                    @foreach([
                    'basic' => ['label' => 'Thông tin cơ bản', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    'other' => ['label' => 'Khác', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                    ] as $key => $tab)
                    <button wire:click="switchTab('{{ $key }}')"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
                               {{ $activeTab === $key
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}" />
                        </svg>
                        {{ $tab['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- FORM --}}
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6">

                    {{-- Error Summary --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-red-800 mb-2">Vui lòng kiểm tra lại thông tin</h4>
                                <ul class="space-y-1 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                    <li class="flex items-start gap-2">
                                        <span class="text-red-400 font-bold">•</span>
                                        <span>{{ $error }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ====== TAB: CƠ BẢN ====== --}}
                    @if($activeTab === 'basic')
                    <div class="space-y-6">

                        {{-- Thông tin cá nhân --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Thông tin cá nhân
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Họ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Họ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                        wire:model.defer="last_name"
                                        class="w-full px-3 py-2 rounded-xl border
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               {{ $errors->has('last_name') ? 'border-red-500 bg-red-50' : 'border-slate-300' }}"
                                        placeholder="Nguyễn Văn">
                                    @error('last_name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tên --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Tên <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                        wire:model.defer="first_name"
                                        class="w-full px-3 py-2 rounded-xl border
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               {{ $errors->has('first_name') ? 'border-red-500 bg-red-50' : 'border-slate-300' }}"
                                        placeholder="An">
                                    @error('first_name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giới tính --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giới tính <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.defer="gender"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="male">Nam</option>
                                        <option value="female">Nữ</option>
                                    </select>
                                    @error('gender')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Ngày sinh --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Ngày sinh
                                    </label>
                                    <input type="date"
                                        wire:model.defer="birthday"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    @error('birthday')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Điện thoại --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Điện thoại
                                    </label>
                                    <input type="tel"
                                        wire:model.defer="phone"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="0123456789">
                                    @error('phone')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Email
                                    </label>
                                    <input type="email"
                                        wire:model.defer="email"
                                        class="w-full px-3 py-2 rounded-xl border
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-slate-300' }}"
                                        placeholder="email@example.com">
                                    @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Gia đình --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Gia đình
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên cha</label>
                                    <input type="text"
                                        wire:model.defer="father_name"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Họ và tên cha">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên mẹ</label>
                                    <input type="text"
                                        wire:model.defer="mother_name"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Họ và tên mẹ">
                                </div>
                            </div>
                        </div>

                        {{-- Giáo xứ --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Giáo xứ
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                {{-- Giáo xứ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giáo xứ <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="parish_id"
                                        class="w-full px-3 py-2 rounded-xl border
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               {{ $errors->has('parish_id') ? 'border-red-500 bg-red-50' : 'border-slate-300' }}">
                                        <option value="">-- Chọn giáo xứ --</option>
                                        @foreach($parishes as $parish)
                                        <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('parish_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giáo họ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
                                    <select wire:model.defer="parish_group_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               disabled:bg-slate-100 disabled:text-slate-400"
                                        @disabled(!$parish_id)>
                                        <option value="">-- Chọn giáo họ --</option>
                                        @foreach($parishGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Bậc thánh --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Bậc thánh</label>
                                    <select wire:model.defer="saint_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn bậc thánh --</option>
                                        @foreach($saints as $saint)
                                        <option value="{{ $saint->id }}">{{ $saint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                    </div>
                    @endif

                    {{-- ====== TAB: KHÁC ====== --}}
                    @if($activeTab === 'other')
                    <div class="space-y-6">

                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Thông tin bổ sung
                            </h3>

                            <div class="space-y-4">

                                {{-- Trạng thái --}}
                                <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-200">
                                    <input type="checkbox"
                                        id="is_active"
                                        wire:model.defer="is_active"
                                        class="w-4 h-4 rounded border-slate-300
                                               text-primary-600 focus:ring-primary-500">
                                    <div>
                                        <label for="is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">
                                            Đang học (kích hoạt)
                                        </label>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            Học sinh đang theo học tại lớp giáo lý
                                        </p>
                                    </div>
                                </div>

                                {{-- Ghi chú --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                                    <textarea wire:model.defer="note"
                                        rows="5"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Ghi chú thêm về học sinh..."></textarea>
                                    @error('note')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                    </div>
                    @endif

                </div>

                {{-- FORM ACTIONS --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-4">

                    <button type="button"
                        wire:click="cancel"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                               bg-white border border-slate-200 text-slate-700 font-semibold
                               hover:bg-slate-50 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hủy
                    </button>

                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-xl
                               bg-primary-600 text-white font-semibold
                               hover:bg-primary-700 active:scale-95 transition-all
                               disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>{{ $isEdit ? 'Cập nhật' : 'Tạo mới' }}</span>
                        <span wire:loading>Đang lưu...</span>
                    </button>

                </div>
            </form>

        </div>
        @endif

    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush