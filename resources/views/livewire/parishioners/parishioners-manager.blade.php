    @section('topbar')
    <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Quản lý giáo dân'],
        ]" />
    @endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">
            {{-- Header + Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <x-page-header
                    class="rounded-t-2xl"
                    title="Danh sách giáo dân"
                description="Quản lý hồ sơ giáo dân trong giáo xứ"
                :stat-value="$this->parishioners->total()"
                stat-label="Giáo dân"
                icon-type="default" />

                <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl space-y-4">

                    {{-- ===== FILTERS (Unified like student-list-new) ===== --}}
                    <div x-data="{ open: false }" class="space-y-4">

                        {{-- Top row: Search + Quick filters + Actions --}}
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

                            {{-- LEFT --}}
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">

                                {{-- Search --}}
                                <x-search-input
                                    wireModel="search"
                                    placeholder="Tìm theo tên, CCCD, SĐT..."
                                    debounce="500ms"
                                    class="max-w-md" />

                                {{-- Quick filters --}}
                                <div class="flex flex-wrap items-center gap-2">

                                    {{-- Giới tính --}}
                                    <select wire:model="selectedGender"
                                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">Giới tính</option>
                                        <option value="male">Nam</option>
                                        <option value="female">Nữ</option>
                                    </select>

                                    {{-- Trạng thái --}}
                                    <select wire:model="selectedStatus"
                                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">Trạng thái</option>
                                        <option value="1">Hoạt động</option>
                                        <option value="0">Tắt</option>
                                    </select>

                                    {{-- Tình trạng --}}
                                    <select wire:model="selectedDeceased"
                                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="0">Còn sống</option> {{-- mặc định --}}
                                        <option value="1">Đã qua đời</option>
                                        <option value="">Tất cả</option>
                                    </select>

                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <x-button wire:click="create">
                                    <x-icon name="plus" />
                                    Thêm giáo dân
                                </x-button>

                                {{-- Toggle advanced --}}
                                <x-button type="button" variant="outline" @click="open = !open">
                                    <x-icon name="filter" />
                                    Bộ lọc
                                    <svg :class="{ 'rotate-180': open }"
                                        class="w-4 h-4 transition-transform"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </x-button>

                                {{-- Reset --}}
                                <x-button wire:click="resetFilters" variant="subtle">
                                    <x-icon name="refresh" />
                                    Đặt lại
                                </x-button>
                            </div>
                        </div>

                        {{-- Active filter chips --}}
                        <div class="flex flex-wrap gap-2">
                            @if($selectedGender)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Giới tính: {{ $selectedGender === 'male' ? 'Nam' : 'Nữ' }}
                            </span>
                            @endif

                            @if($selectedStatus !== null && $selectedStatus !== '')
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Trạng thái: {{ $selectedStatus ? 'Hoạt động' : 'Tắt' }}
                            </span>
                            @endif

                            @if($selectedDeceased === '1')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Đã qua đời
                                <button wire:click="$set('selectedDeceased', '0')" class="ml-1 text-slate-400 hover:text-slate-600">✕</button>
                            </span>
                            @elseif($selectedDeceased === '')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                Hiển thị tất cả
                                <button wire:click="$set('selectedDeceased', '0')" class="ml-1 text-blue-400 hover:text-blue-600">✕</button>
                            </span>
                            @endif

                            @if($selectedAgeGroup)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Tuổi: {{ $ageGroups[$selectedAgeGroup] ?? '' }}
                            </span>
                            @endif

                            @if($selectedMarried !== null && $selectedMarried !== '')
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Hôn nhân:
                                {{ ['Độc thân','Đã kết hôn','Góa','Ly hôn'][$selectedMarried] ?? '' }}
                            </span>
                            @endif

                            @if($selectedGroup)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Giáo họ: {{ $parishGroups[(int)$selectedGroup] ?? '' }}
                            </span>
                            @endif
                        </div>

                        {{-- Advanced filters --}}
                        <div x-show="open" x-transition
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4
                                border border-slate-200 rounded-2xl bg-white">

                            {{-- Nhóm tuổi --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nhóm tuổi</label>
                                <select wire:model="selectedAgeGroup"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Tất cả</option>
                                    @foreach($ageGroups as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Hôn nhân --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Hôn nhân</label>
                                <select wire:model="selectedMarried"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Tất cả</option>
                                    <option value="0">Độc thân</option>
                                    <option value="1">Đã kết hôn</option>
                                    <option value="2">Góa</option>
                                    <option value="3">Ly hôn</option>
                                </select>
                            </div>

                            {{-- Giáo họ --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Giáo họ</label>
                                <select wire:model="selectedGroup"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Tất cả</option>
                                    @foreach($parishGroups as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                @if($this->parishioners->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">STT</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ảnh</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Họ và tên</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Giới tính</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Ngày sinh</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Điện thoại</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Giáo họ</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Trạng thái</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($this->parishioners as $index => $p)
                            <tr class="hover:bg-slate-50 transition-colors duration-200 {{ $p->is_deceased ? 'opacity-60' : '' }}"
                                wire:key="p-{{ $p->id }}">

                                <td class="px-4 py-3 text-sm text-slate-500">
                                    {{ ($this->parishioners->firstItem() ?? 0) + $index }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        @if($p->avatar_path)
                                        <img src="{{ asset('storage/' . $p->avatar_path) }}" alt="{{ $p->full_name }}" class="w-full h-full object-cover" />
                                        @else
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $p->full_name_with_saint }}</div>
                                    @if($p->cccd)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $p->cccd }}</div>
                                    @endif
                                    @if($p->is_deceased)
                                    <span class="inline-flex mt-1 px-1.5 py-0.5 rounded text-xs font-medium bg-slate-200 text-slate-600">
                                        Đã qua đời {{ $p->death_date?->format('d/m/Y') }}
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $p->gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                        {{ $p->gender_name }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center text-sm text-slate-700">
                                    {{ $p->birthday?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    @if($p->phone)
                                    <a href="tel:{{ $p->phone }}" class="text-primary-600 hover:text-primary-700 font-medium transition-colors duration-200">{{ $p->phone }}</a>
                                    @else
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $p->parishGroup?->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full
                                        {{ $p->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $p->status_name }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('parishioners.show', $p->id) }}"
                                            class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors duration-200">Xem</a>
                                        <span class="text-slate-300">|</span>
                                        <button wire:click="delete({{ $p->id }})"
                                            wire:confirm="Bạn có chắc muốn xóa giáo dân này không?"
                                            class="text-sm text-red-500 hover:text-red-600 font-medium transition-colors duration-200">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($this->parishioners->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    <x-pagination :paginator="$this->parishioners" :per-page-options="[10, 15, 25, 50]" />
                </div>
                @endif

                @else
                <div class="text-center py-16">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-slate-500 text-sm">Chưa có giáo dân nào</p>
                    <button wire:click="create" class="mt-4 px-4 py-2 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-all duration-200 text-sm font-medium">
                        Thêm giáo dân đầu tiên
                    </button>
                </div>
                @endif
            </div>

        </div>

        {{-- ===== MODAL: Form thêm/sửa ===== --}}
        @if($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-3xl max-h-[90vh] flex flex-col" wire:click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white flex-shrink-0 rounded-t-2xl">
                    <h2 class="text-base font-semibold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo dân' : 'Thêm giáo dân mới' }}
                    </h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="border-b border-slate-200 px-6 flex-shrink-0">
                    <nav class="flex gap-1 -mb-px">
                        @foreach([
                        'basic' => 'Cơ bản',
                        'address' => 'Địa chỉ',
                        'family' => 'Gia đình',
                        'classify' => 'Phân loại',
                        'other' => 'Khác',
                        ] as $tab => $label)
                        @php
                        $tabHasError = match($tab) {
                        'basic' => $errors->hasAny(['last_name','first_name','gender','birthday','email','avatar']),
                        'address' => $errors->hasAny(['origin','permanent_residence','temporary_residence']),
                        'family' => $errors->hasAny(['married','father_id','mother_id','family_id']),
                        'classify' => $errors->hasAny(['specialist_level','catechism_major']),
                        'other' => $errors->hasAny(['note','death_date']),
                        default => false,
                        };
                        @endphp
                        <button wire:click="goToTab('{{ $tab }}')"
                            class="relative px-4 py-2.5 text-sm font-medium border-b-2 transition-all duration-200
                                {{ $activeTab === $tab
                                    ? 'border-primary-500 text-primary-600'
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                            {{ $label }}
                            {{-- Dot đỏ khi tab có lỗi --}}
                            @if($tabHasError)
                            <span class="absolute top-2 right-1 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            @endif
                        </button>
                        @endforeach
                    </nav>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-6">

                    @php
                    $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200";
                    @endphp

                    {{-- Tab: Cơ bản --}}
                    @if($activeTab === 'basic')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Họ <span class="text-red-500">*</span></label>
                            <input wire:model.defer="last_name" type="text" class="{{ $input }} @error('last_name') border-red-400 @enderror" />
                            @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tên <span class="text-red-500">*</span></label>
                            <input wire:model.defer="first_name" type="text" class="{{ $input }} @error('first_name') border-red-400 @enderror" />
                            @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Giới tính <span class="text-red-500">*</span></label>
                            <select wire:model.defer="gender" class="{{ $input }}">
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh</label>
                            <input wire:model.defer="birthday" type="date" class="{{ $input }} @error('birthday') border-red-400 @enderror" />
                            @error('birthday') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Con thứ</label>
                            <input wire:model.defer="birth_order" type="number" min="1" placeholder="1, 2, 3..." class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CCCD</label>
                            <input wire:model.defer="cccd" type="text" maxlength="12" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Điện thoại</label>
                            <input wire:model.defer="phone" type="tel" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input wire:model.defer="email" type="email" class="{{ $input }} @error('email') border-red-400 @enderror" />
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh đại diện</label>
                            <input wire:model="avatar" type="file" accept="image/*" class="w-full text-sm text-slate-600" />
                            @if($currentAvatarPath)
                            <img src="{{ asset('storage/' . $currentAvatarPath) }}" class="w-12 h-12 rounded-full mt-2 object-cover" />
                            @endif
                            @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endif

                    {{-- Tab: Địa chỉ --}}
                    @if($activeTab === 'address')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Quê quán</label>
                            <input wire:model.defer="origin" type="text" class="{{ $input }}" />
                        </div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Thường trú</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
                                <input wire:model.defer="permanent_province" type="text" class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
                                <input wire:model.defer="permanent_residence" type="text" class="{{ $input }}" />
                            </div>
                        </div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Tạm trú</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
                                <input wire:model.defer="temporary_province" type="text" class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
                                <input wire:model.defer="temporary_residence" type="text" class="{{ $input }}" />
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Tab: Gia đình --}}
                    @if($activeTab === 'family')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tên cha</label>
                            <input wire:model.defer="father_name" type="text" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tên mẹ</label>
                            <input wire:model.defer="mother_name" type="text" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tình trạng hôn nhân</label>
                            <select wire:model.defer="married" class="{{ $input }}">
                                <option value="0">Độc thân</option>
                                <option value="1">Đã kết hôn</option>
                                <option value="2">Góa</option>
                                <option value="3">Ly hôn</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Thuộc hộ gia đình</label>
                            <input wire:model.defer="family_id" type="number" placeholder="ID hộ gia đình" class="{{ $input }}" />
                        </div>
                    </div>
                    @endif

                    {{-- Tab: Phân loại --}}
                    @if($activeTab === 'classify')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nghề nghiệp</label>
                            <select wire:model.defer="career" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.career', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Học vấn</label>
                            <select wire:model.defer="education_level" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.education_level', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Trình độ chuyên môn</label>
                            <select wire:model.defer="specialist_level" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.specialist_level', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Trình độ giáo lý</label>
                            <select wire:model.defer="catechism_level" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.catechism_level', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Chuyên ngành giáo lý</label>
                            <input wire:model.defer="catechism_major" type="text" class="{{ $input }}" placeholder="Vd: Giáo lý hôn nhân..." />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dân tộc</label>
                            <select wire:model.defer="ethnic" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.ethnic', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Chức vụ</label>
                            <select wire:model.defer="position" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.position', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    {{-- Tab: Khác --}}
                    @if($activeTab === 'other')
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                            <textarea wire:model.defer="note" rows="3" class="{{ $input }}" placeholder="Ghi chú..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input wire:model.defer="status" type="checkbox" class="w-4 h-4 rounded text-primary-500" />
                                <span class="text-sm text-slate-700">Kích hoạt</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input wire:model.defer="is_active" type="checkbox" class="w-4 h-4 rounded text-primary-500" />
                                <span class="text-sm text-slate-700">Đang sinh hoạt tại xứ</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input wire:model.defer="is_new_convert" type="checkbox" class="w-4 h-4 rounded text-primary-500" />
                                <span class="text-sm text-slate-700">Tân tòng</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input wire:model.defer="is_included_in_stats" type="checkbox" class="w-4 h-4 rounded text-primary-500" />
                                <span class="text-sm text-slate-700">Tính vào thống kê</span>
                            </label>
                        </div>

                        {{-- Tử vong --}}
                        <div class="pt-4 border-t border-slate-200">
                            <label class="flex items-center gap-2 cursor-pointer select-none mb-3">
                                <input wire:model="is_deceased" type="checkbox" class="w-4 h-4 rounded text-red-500" />
                                <span class="text-sm font-medium text-slate-700">Đã qua đời</span>
                            </label>

                            @if($is_deceased)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-red-50 border border-red-100 rounded-xl">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Ngày mất <span class="text-red-500">*</span></label>
                                    <input wire:model.defer="death_date" type="date" class="{{ $input }} @error('death_date') border-red-400 @enderror" />
                                    @error('death_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Số sổ mất</label>
                                    <input wire:model.defer="death_book_number" type="text" class="{{ $input }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nơi qua đời</label>
                                    <input wire:model.defer="death_place" type="text" class="{{ $input }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nơi an táng</label>
                                    <input wire:model.defer="burial_place" type="text" class="{{ $input }}" />
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 bg-slate-50 flex-shrink-0 rounded-b-2xl">
                    <button wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 transition-all duration-200">
                        Hủy
                    </button>
                    <div class="flex items-center gap-3">
                        @if($activeTab !== 'basic')
                        <button wire:click="goToTab('{{ ['address' => 'basic', 'family' => 'address', 'classify' => 'family', 'other' => 'classify'][$activeTab] ?? 'basic' }}')"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 transition-all duration-200">
                            Quay lại
                        </button>
                        @endif
                        <button wire:click="save" wire:loading.attr="disabled"
                            class="px-6 py-2 text-sm font-medium text-white bg-primary-500 rounded-xl hover:bg-primary-600 transition-all duration-200 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                {{ $activeTab === 'other' ? 'Lưu' : 'Tiếp theo' }}
                            </span>
                            <span wire:loading wire:target="save">Đang lưu...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        @endif

        {{-- Loading overlay --}}
        <div wire:loading.delay wire:target="save,delete,toggleStatus"
            class="fixed inset-0 bg-black/20 flex items-center justify-center z-[60]">
            <div class="bg-white rounded-2xl px-6 py-4 flex items-center gap-3 shadow-md">
                <svg class="animate-spin h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span class="text-sm text-slate-700">Đang xử lý...</span>
            </div>
        </div>

    </div>