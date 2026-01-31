<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#student-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-form-main" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Danh sách lớp', 'url' => route('classes.index')],
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
                            {{ $isEdit ? 'Cập nhật thông tin học sinh' : 'Điền đầy đủ thông tin để thêm học sinh mới' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                     {{ $isEdit ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $isEdit ? 'Chế độ sửa' : 'Chế độ tạo mới' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium whitespace-nowrap">
                    @foreach([
                    'basic' => ['label' => 'Cơ bản', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    'baptism' => ['label' => 'Rửa tội', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'more_power' => ['label' => 'Thêm sức', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    'communion' => ['label' => 'Rước lễ', 'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
                    'anoint' => ['label' => 'Xức dầu', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    'other' => ['label' => 'Khác', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                    ] as $key => $tab)
                    <button wire:click="switchTab('{{ $key }}')"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
                               {{ $activeTab === $key
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50'
                               }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}" />
                        </svg>
                        {{ $tab['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- FORM CONTENT --}}
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6">

                    {{-- TAB: Thông tin cơ bản --}}
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
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('last_name') border-red-500 @enderror"
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
                                        wire:model.defer="name"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('name') border-red-500 @enderror"
                                        placeholder="A">
                                    @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giới tính --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giới tính <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.defer="sex"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="1">Nam</option>
                                        <option value="2">Nữ</option>
                                    </select>
                                    @error('sex')
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
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('birthday') border-red-500 @enderror">
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
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('email') border-red-500 @enderror"
                                        placeholder="email@example.com">
                                    @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- CCCD --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Số CCCD/CMND
                                    </label>
                                    <input type="text"
                                        wire:model.defer="cccd"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="001234567890">
                                    @error('cccd')
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
                                Thông tin gia đình
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên cha</label>
                                    <input type="text" wire:model.defer="father"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Họ và tên cha">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên mẹ</label>
                                    <input type="text" wire:model.defer="mother"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Họ và tên mẹ">
                                </div>
                            </div>
                        </div>

                        {{-- Địa chỉ --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Địa chỉ
                            </h3>

                            {{-- Nguyên quán --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Nguyên quán</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Địa chỉ</label>
                                        <input type="text" wire:model.defer="origin"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Số nhà, tên đường">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Phường/Xã</label>
                                        <input type="text" wire:model.defer="ward"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Phường/Xã">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Tỉnh/TP</label>
                                        <input type="text" wire:model.defer="province"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Tỉnh/Thành phố">
                                    </div>
                                </div>
                            </div>

                            {{-- Trú quán --}}
                            <div>
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Trú quán</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Địa chỉ</label>
                                        <input type="text" wire:model.defer="residence"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Số nhà, tên đường">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Phường/Xã</label>
                                        <input type="text" wire:model.defer="resi_ward"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Phường/Xã">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Tỉnh/TP</label>
                                        <input type="text" wire:model.defer="resi_province"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            placeholder="Tỉnh/Thành phố">
                                    </div>
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

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Giáo phận --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giáo phận <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="diocese_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('diocese_id') border-red-500 @enderror">
                                        <option value="">-- Chọn giáo phận --</option>
                                        @foreach($dioceses as $diocese)
                                        <option value="{{ $diocese->id }}">{{ $diocese->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('diocese_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giáo hạt --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giáo hạt <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="deanery_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('deanery_id') border-red-500 @enderror"
                                        :disabled="!diocese_id">
                                        <option value="">-- Chọn giáo hạt --</option>
                                        @foreach($deaneries as $deanery)
                                        <option value="{{ $deanery->id }}">{{ $deanery->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('deanery_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Giáo xứ --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giáo xứ <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="parish_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               @error('parish_id') border-red-500 @enderror"
                                        :disabled="!deanery_id">
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
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Giáo họ
                                    </label>
                                    <select wire:model.defer="paid"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        :disabled="!parish_id">
                                        <option value="">-- Chọn giáo họ --</option>
                                        @foreach($parishChildren as $pc)
                                        <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Bậc thánh --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Bậc thánh
                                    </label>
                                    <select wire:model.defer="holy"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn bậc thánh --</option>
                                        @foreach($holies as $holyItem)
                                        <option value="{{ $holyItem->id }}">{{ $holyItem->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Giáo dục & Nghề nghiệp --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Giáo dục & Nghề nghiệp
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Dân tộc</label>
                                    <select wire:model.defer="ethnic_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn dân tộc --</option>
                                        @foreach($ethnics as $ethnic)
                                        <option value="{{ $ethnic->id }}">{{ $ethnic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Trình độ học vấn</label>
                                    <select wire:model.defer="level_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn trình độ --</option>
                                        @foreach($levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Nghề nghiệp</label>
                                    <select wire:model.defer="career_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn nghề nghiệp --</option>
                                        @foreach($careers as $career)
                                        <option value="{{ $career->id }}">{{ $career->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Chức vụ</label>
                                    <select wire:model.defer="position_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn chức vụ --</option>
                                        @foreach($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Ngôn ngữ</label>
                                    <select wire:model.defer="language_id"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Chọn ngôn ngữ --</option>
                                        @foreach($languages as $language)
                                        <option value="{{ $language->id }}">{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Trình độ chuyên môn</label>
                                    <input type="text" wire:model.defer="professional_level"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="VD: Kỹ sư, Bác sĩ...">
                                </div>
                            </div>
                        </div>

                    </div>
                    @endif

                    {{-- TAB: Rửa tội --}}
                    @if($activeTab === 'baptism')
                    <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Rửa tội</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Rửa tội</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày rửa tội</label>
                                <input type="date" wire:model.defer="baptism_date"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Số sổ</label>
                                <input type="text" wire:model.defer="baptism_number"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Số sổ rửa tội">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Cha ban bí tích</label>
                                <select wire:model.defer="baptism_giver_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn linh mục --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Người đỡ đầu</label>
                                <select wire:model.defer="baptism_sponsor_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn người đỡ đầu --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nơi rửa tội --}}
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Nơi rửa tội</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo phận</label>
                                        <select wire:model="baptism_diocese_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Chọn giáo phận --</option>
                                            @foreach($dioceses as $diocese)
                                            <option value="{{ $diocese->id }}">{{ $diocese->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo hạt</label>
                                        <select wire:model="baptism_deanery_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :disabled="!baptism_diocese_id">
                                            <option value="">-- Chọn giáo hạt --</option>
                                            @foreach($baptismDeaneries as $deanery)
                                            <option value="{{ $deanery->id }}">{{ $deanery->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo xứ</label>
                                        <select wire:model.defer="baptism_parish_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :disabled="!baptism_deanery_id">
                                            <option value="">-- Chọn giáo xứ --</option>
                                            @foreach($baptismParishes as $parish)
                                            <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- TAB: Thêm sức --}}
                    @if($activeTab === 'more_power')
                    <div class="bg-gradient-to-br from-yellow-50 to-white rounded-xl p-6 border border-yellow-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-yellow-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Thêm sức</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Thêm sức</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày thêm sức</label>
                                <input type="date" wire:model.defer="more_power_date"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Số sổ</label>
                                <input type="text" wire:model.defer="more_power_number"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Số sổ thêm sức">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Đức cha ban bí tích</label>
                                <select wire:model.defer="more_power_giver_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <option value="">-- Chọn đức cha --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Người đỡ đầu</label>
                                <select wire:model.defer="more_power_sponsor_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <option value="">-- Chọn người đỡ đầu --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nơi thêm sức --}}
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Nơi thêm sức</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo phận</label>
                                        <select wire:model="more_power_diocese_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                            <option value="">-- Chọn giáo phận --</option>
                                            @foreach($dioceses as $diocese)
                                            <option value="{{ $diocese->id }}">{{ $diocese->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo hạt</label>
                                        <select wire:model="more_power_deanery_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                            :disabled="!more_power_diocese_id">
                                            <option value="">-- Chọn giáo hạt --</option>
                                            @foreach($morePowerDeaneries as $deanery)
                                            <option value="{{ $deanery->id }}">{{ $deanery->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo xứ</label>
                                        <select wire:model.defer="more_power_parish_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                            :disabled="!more_power_deanery_id">
                                            <option value="">-- Chọn giáo xứ --</option>
                                            @foreach($morePowerParishes as $parish)
                                            <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- TAB: Rước lễ --}}
                    @if($activeTab === 'communion')
                    <div class="bg-gradient-to-br from-green-50 to-white rounded-xl p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Rước lễ</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Rước lễ lần đầu</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày rước lễ</label>
                                <input type="date" wire:model.defer="communion_date"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Số sổ</label>
                                <input type="text" wire:model.defer="communion_number"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-green-500"
                                    placeholder="Số sổ rước lễ">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Cha ban bí tích</label>
                                <select wire:model.defer="communion_giver_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">-- Chọn linh mục --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nơi rước lễ --}}
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Nơi rước lễ</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo phận</label>
                                        <select wire:model="communion_diocese_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="">-- Chọn giáo phận --</option>
                                            @foreach($dioceses as $diocese)
                                            <option value="{{ $diocese->id }}">{{ $diocese->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo hạt</label>
                                        <select wire:model="communion_deanery_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-green-500"
                                            :disabled="!communion_diocese_id">
                                            <option value="">-- Chọn giáo hạt --</option>
                                            @foreach($communionDeaneries as $deanery)
                                            <option value="{{ $deanery->id }}">{{ $deanery->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-slate-600 mb-1">Giáo xứ</label>
                                        <select wire:model.defer="communion_parish_id"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                                   focus:outline-none focus:ring-2 focus:ring-green-500"
                                            :disabled="!communion_deanery_id">
                                            <option value="">-- Chọn giáo xứ --</option>
                                            @foreach($communionParishes as $parish)
                                            <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- TAB: Xức dầu --}}
                    @if($activeTab === 'anoint')
                    <div class="bg-gradient-to-br from-red-50 to-white rounded-xl p-6 border border-red-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-red-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Xức dầu</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Xức dầu bệnh nhân</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày xức dầu</label>
                                <input type="date" wire:model.defer="anoint_date"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tình trạng</label>
                                <select wire:model.defer="anoint_status"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="0">Chưa xức dầu</option>
                                    <option value="1">Đã xức dầu</option>
                                    <option value="2">Khỏi bệnh</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Cha ban bí tích</label>
                                <select wire:model.defer="anoint_giver_id"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">-- Chọn linh mục --</option>
                                    @foreach($catechists as $catechist)
                                    <option value="{{ $catechist->id }}">{{ $catechist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                                <textarea wire:model.defer="anoint_note"
                                    rows="3"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="Ghi chú thêm về bí tích xức dầu"></textarea>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- TAB: Thông tin khác --}}
                    @if($activeTab === 'other')
                    <div class="space-y-6">

                        {{-- Trạng thái học tập --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Trạng thái
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Trình độ học giáo lý
                                    </label>
                                    <select wire:model.defer="study"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="0">Chưa học</option>
                                        <option value="1">Đang học</option>
                                        <option value="2">Đã tốt nghiệp</option>
                                        <option value="3">Tạm dừng</option>
                                        <option value="4">Bỏ học</option>
                                        <option value="5">Chuyển đi</option>
                                        <option value="6">Khác</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Ngày hứa
                                    </label>
                                    <input type="date" wire:model.defer="promise_day"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>

                                <div class="md:col-span-2 space-y-3">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                            wire:model.defer="new_convert"
                                            class="w-4 h-4 rounded border-slate-300
                                                   text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm font-medium text-slate-700">Tân tòng</span>
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                            wire:model.defer="married"
                                            class="w-4 h-4 rounded border-slate-300
                                                   text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm font-medium text-slate-700">Đã lập gia đình</span>
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                            wire:model.defer="statistical"
                                            class="w-4 h-4 rounded border-slate-300
                                                   text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm font-medium text-slate-700">Thống kê</span>
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                            wire:model.defer="status"
                                            class="w-4 h-4 rounded border-slate-300
                                                   text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm font-medium text-slate-700">Trạng thái hoạt động</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Thông tin qua đời --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Thông tin qua đời (nếu có)
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                            wire:model.defer="die_status"
                                            class="w-4 h-4 rounded border-slate-300
                                                   text-red-600 focus:ring-red-500">
                                        <span class="text-sm font-medium text-slate-700">Đã qua đời</span>
                                    </label>
                                </div>

                                @if($die_status)
                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Thời gian</label>
                                    <input type="date" wire:model.defer="die_time"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Số xổ mất</label>
                                    <input type="text" wire:model.defer="die_lottery"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Số xổ mất">
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Nơi qua đời</label>
                                    <input type="text" wire:model.defer="die_death"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Địa điểm qua đời">
                                </div>

                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Nơi an táng</label>
                                    <input type="text" wire:model.defer="die_burial"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="Địa điểm an táng">
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Ghi chú --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Ghi chú
                            </h3>

                            <textarea wire:model.defer="note"
                                rows="4"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ghi chú thêm về học sinh..."></textarea>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
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

{{-- Alpine.js for interactions --}}
@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush