<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                [
                    'label' => 'Trang chủ',
                    'url' => route('dashboard'),
                ],
                [
                    'label' => 'Quản lý giáo dân',
                    'url' => route('parishioners.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\' />
                            </svg>',
                ],
            ]"
            separator="arrow" />

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

            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">
                {{ session('warning') }}
            </x-toast-notification>
            @endif

            @if (session()->has('info'))
            <x-toast-notification type="info" :duration="3500">
                {{ session('info') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Quản lý giáo dân"
                description="Danh sách giáo dân trong giáo xứ"
                :stat-value="$parishioners?->total()"
                stat-label="Giáo dân"
                icon-type="parishioner">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">
                    {{-- LEFT: Search --}}
                    <div class="flex items-center gap-3">
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm kiếm theo tên, CCCD, SĐT..."
                            class="w-80 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    {{-- RIGHT: Primary Action --}}
                    <x-action-button wire="create" icon="plus">
                        Thêm giáo dân
                    </x-action-button>
                </div>

                {{-- Filters Row --}}
                <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                    {{-- Giới tính --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Giới tính</label>
                        <select
                            wire:model="selectedSex"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl
                                   text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Nam</option>
                            <option value="0">Nữ</option>
                        </select>
                    </div>

                    {{-- Nhóm tuổi --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nhóm tuổi</label>
                        <select
                            wire:model="selectedAgeGroup"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl
                                   text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả --</option>
                            @foreach($ageGroups as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Hôn nhân --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Hôn nhân</label>
                        <select
                            wire:model="selectedMarried"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl
                                   text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả --</option>
                            <option value="0">Độc thân</option>
                            <option value="1">Đã kết hôn</option>
                        </select>
                    </div>

                    {{-- Trạng thái --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Trạng thái</label>
                        <select
                            wire:model="selectedStatus"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl
                                   text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Tắt</option>
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="flex items-end">
                        <button
                            wire:click="resetFilters"
                            class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 
                                   text-slate-700 text-sm font-medium rounded-xl
                                   transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Đặt lại
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($parishioners && $parishioners->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Ảnh</x-table-header>
                            <x-table-header>Họ và tên</x-table-header>
                            <x-table-header class="text-center">Giới tính</x-table-header>
                            <x-table-header class="text-center">Tuổi</x-table-header>
                            <x-table-header>Điện thoại</x-table-header>
                            <x-table-header>Địa chỉ</x-table-header>
                            <x-table-header class="text-center">Học sinh</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($parishioners as $index => $p)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="parishioner-{{ $p->id }}">
                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($parishioners->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Ảnh --}}
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
                                    @if($p->image)
                                    <img src="{{ asset('storage/' . $p->image) }}"
                                        alt="{{ $p->last_name }} {{ $p->name }}"
                                        class="w-full h-full object-cover" />
                                    @else
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    @endif
                                </div>
                            </td>

                            {{-- Họ và tên --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $p->last_name }} {{ $p->name }}
                                </div>
                                @if($p->cccd)
                                <div class="text-xs text-slate-500 mt-0.5">
                                    CCCD: {{ $p->cccd }}
                                </div>
                                @endif
                            </td>

                            {{-- Giới tính --}}
                            <td class="px-6 py-4 text-center">
                                @if($p->sex == 1)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full 
                                             text-xs font-semibold bg-blue-100 text-blue-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                    </svg>
                                    Nam
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full 
                                             text-xs font-semibold bg-pink-100 text-pink-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                    </svg>
                                    Nữ
                                </span>
                                @endif
                            </td>

                            {{-- Tuổi --}}
                            <td class="px-6 py-4 text-center text-sm text-slate-700">
                                @if($p->birthday)
                                {{ $this->calculateAge($p->birthday) }} tuổi
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Điện thoại --}}
                            <td class="px-6 py-4 text-sm text-slate-700">
                                @if($p->phone)
                                <a href="tel:{{ $p->phone }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ $p->phone }}
                                </a>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Địa chỉ --}}
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">
                                {{ $p->residence ?: $p->origin ?: '-' }}
                            </td>

                            {{-- Học sinh liên kết --}}
                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="openStudentLink({{ $p->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 
                                           text-xs font-semibold rounded-full
                                           bg-purple-100 text-purple-700 hover:bg-purple-200
                                           transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Xem</span>
                                </button>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                             {{ $p->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $p->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">
                                    {{-- Sửa --}}
                                    <x-table-action
                                        wire="edit({{ $p->id }})"
                                        icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    {{-- Toggle Status --}}
                                    <x-table-action
                                        wire="toggleStatus({{ $p->id }})"
                                        :icon="$p->status ? 'archive' : 'check'"
                                        :color="$p->status ? 'warning' : 'success'"
                                        :loading="true"
                                        debounce="500">
                                        {{ $p->status ? 'Tắt' : 'Bật' }}
                                    </x-table-action>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($parishioners->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination
                    :paginator="$parishioners"
                    :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @else
            <div class="text-center py-12">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có giáo dân nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700
                        transition-all">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Thêm giáo dân đầu tiên
                </button>
            </div>
            @endif
        </div>

        {{-- Form Modal --}}
        @if ($showForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="parishioner-modal-title"
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="parishioner-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo dân' : 'Thêm giáo dân mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Thông tin cơ bản về giáo dân
                    </p>
                </div>

                {{-- Body - SCROLLABLE --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    {{-- Error Summary --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 animate-shake">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-red-800 mb-2">
                                    Vui lòng kiểm tra lại thông tin
                                </h4>
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

                    {{-- SECTION 1: THÔNG TIN CƠ BẢN --}}
                    <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cơ bản
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Họ --}}
                            <x-form-input
                                label="Họ"
                                name="last_name"
                                wire:model.defer="last_name"
                                placeholder="Nguyễn Văn"
                                required />

                            {{-- Tên --}}
                            <x-form-input
                                label="Tên"
                                name="name"
                                wire:model.defer="name"
                                placeholder="An"
                                required />

                            {{-- Giới tính --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Giới tính <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="sex"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="1">Nam</option>
                                    <option value="0">Nữ</option>
                                </select>
                                @error('sex')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Ngày sinh --}}
                            <x-form-input
                                label="Ngày sinh"
                                name="birthday"
                                type="date"
                                wire:model.defer="birthday" />

                            {{-- CCCD --}}
                            <x-form-input
                                label="CCCD"
                                name="cccd"
                                wire:model.defer="cccd"
                                placeholder="001234567890" />

                            {{-- Điện thoại --}}
                            <x-form-input
                                label="Điện thoại"
                                name="phone"
                                wire:model.defer="phone"
                                placeholder="0901234567" />

                            {{-- Email --}}
                            <x-form-input
                                label="Email"
                                name="email"
                                type="email"
                                wire:model.defer="email"
                                placeholder="email@example.com" />

                            {{-- Tình trạng hôn nhân --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Tình trạng hôn nhân <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="married"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="0">Độc thân</option>
                                    <option value="1">Đã kết hôn</option>
                                </select>
                            </div>
                        </div>

                        {{-- Upload ảnh --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Ảnh đại diện
                            </label>
                            <input
                                type="file"
                                wire:model="image"
                                accept="image/*"
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl
                                       text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            @if($currentImage)
                            <div class="mt-2 flex items-center gap-2">
                                <img src="{{ asset('storage/' . $currentImage) }}"
                                    class="w-12 h-12 rounded-lg object-cover" />
                                <span class="text-xs text-slate-500">Ảnh hiện tại</span>
                            </div>
                            @endif
                            @error('image')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- SECTION 2: ĐỊA CHỈ --}}
                    <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Địa chỉ
                        </h3>

                        <div class="grid grid-cols-1 gap-4">
                            {{-- Nguyên quán --}}
                            <x-form-input
                                label="Nguyên quán"
                                name="origin"
                                wire:model.defer="origin"
                                placeholder="Số nhà, đường..." />

                            {{-- Nơi ở hiện tại --}}
                            <x-form-input
                                label="Nơi ở hiện tại"
                                name="residence"
                                wire:model.defer="residence"
                                placeholder="Số nhà, đường..." />
                        </div>
                    </div>

                    {{-- SECTION 3: GIA ĐÌNH --}}
                    <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Gia đình
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Cha --}}
                            <x-form-input
                                label="Tên cha"
                                name="father"
                                wire:model.defer="father"
                                placeholder="Họ tên cha" />

                            {{-- Mẹ --}}
                            <x-form-input
                                label="Tên mẹ"
                                name="mother"
                                wire:model.defer="mother"
                                placeholder="Họ tên mẹ" />
                        </div>
                    </div>

                    {{-- SECTION 4: THÁNH SỰ --}}
                    <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thánh sự
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Ngày rửa tội --}}
                            <x-form-input
                                label="Ngày rửa tội"
                                name="baptism_date"
                                type="date"
                                wire:model.defer="baptism_date" />

                            {{-- Ngày thêm sức --}}
                            <x-form-input
                                label="Ngày thêm sức"
                                name="more_power_date"
                                type="date"
                                wire:model.defer="more_power_date" />

                            {{-- Ngày rước lễ lần đầu --}}
                            <x-form-input
                                label="Ngày rước lễ lần đầu"
                                name="communion_date"
                                type="date"
                                wire:model.defer="communion_date" />
                        </div>
                    </div>

                    {{-- SECTION 5: GHI CHÚ & TRẠNG THÁI --}}
                    <div class="space-y-4">
                        {{-- Ghi chú --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Ghi chú
                            </label>
                            <textarea
                                wire:model.defer="note"
                                rows="3"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ghi chú thêm..."></textarea>
                            @error('note')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="border border-slate-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <input
                                    id="parishioner-status"
                                    type="checkbox"
                                    wire:model.defer="status"
                                    class="mt-0.5 w-4 h-4 rounded border-slate-300
                                           text-primary-600 focus:ring-primary-500">
                                <div class="flex-1">
                                    <label for="parishioner-status" class="text-sm font-semibold text-slate-900 cursor-pointer">
                                        Kích hoạt giáo dân
                                    </label>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Giáo dân đang hoạt động trong giáo xứ
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">
                        Hủy
                    </x-action-button>

                    <x-action-button wire="save" icon="save" :loading="true">
                        {{ $editingId ? 'Cập nhật' : 'Tạo mới' }}
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

        {{-- Student Link Modal --}}
        @if ($showStudentLink)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            wire:click="closeStudentLink">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-purple-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        Học sinh liên kết
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Danh sách học sinh của giáo dân này
                    </p>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-6">
                    @if($linkedStudents && $linkedStudents->count() > 0)
                    <div class="space-y-3">
                        @foreach($linkedStudents as $student)
                        <div class="border border-slate-200 rounded-xl p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-slate-900">
                                        {{ $student->name }}
                                    </h4>
                                    <div class="mt-1 flex items-center gap-4 text-sm text-slate-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ $student->lop?->schoolYear?->name }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ $student->lop?->name }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('students.show', $student->id) }}"
                                    class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg
                                          hover:bg-primary-200 text-sm font-medium transition-colors">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p class="mt-3 text-sm text-slate-500">
                            Chưa có học sinh nào liên kết với giáo dân này
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                    <x-action-button wire="closeStudentLink" variant="secondary">
                        Đóng
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Loading Indicator --}}
<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>