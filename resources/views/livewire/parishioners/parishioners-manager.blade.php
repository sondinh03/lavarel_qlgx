<div class="min-h-screen bg-slate-50 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Quản lý giáo dân'],
        ]" />

        {{-- Thông báo --}}
        @if (session('message'))
        <x-toast-notification type="success">{{ session('message') }}</x-toast-notification>
        @endif
        @if (session('error'))
        <x-toast-notification type="error">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- Header + Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý giáo dân"
                description="Danh sách giáo dân trong giáo xứ"
                :stat-value="$this->parishioners->total()"
                stat-label="Giáo dân" />

            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70 space-y-4">

                {{-- Search + Nút thêm --}}
                <div class="flex items-center justify-between gap-4">
                    <input
                        wire:model.debounce.400ms="search"
                        type="search"
                        placeholder="Tìm theo tên, CCCD, SĐT..."
                        class="w-80 px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />

                    <button wire:click="create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm giáo dân
                    </button>
                </div>

                {{-- Filters --}}
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Giới tính</label>
                        <select wire:model="selectedGender" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Nhóm tuổi</label>
                        <select wire:model="selectedAgeGroup" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            @foreach($ageGroups as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Hôn nhân</label>
                        <select wire:model="selectedMarried" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            <option value="0">Độc thân</option>
                            <option value="1">Đã kết hôn</option>
                            <option value="2">Góa</option>
                            <option value="3">Ly hôn</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Trạng thái</label>
                        <select wire:model="selectedStatus" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Tắt</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Giáo họ</label>
                        <select wire:model="selectedGroup" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            {{-- Load từ DB nếu cần --}}
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button wire:click="resetFilters"
                            class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Đặt lại
                        </button>
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
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="p-{{ $p->id }}">

                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ ($this->parishioners->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0">
                                    @if($p->avatar_path)
                                    <img src="{{ asset('storage/' . $p->avatar_path) }}" alt="{{ $p->full_name }}" class="w-full h-full object-cover" />
                                    @else
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900 text-sm">{{ $p->full_name_with_saint }}</div>
                                @if($p->cccd)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $p->cccd }}</div>
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
                                <a href="tel:{{ $p->phone }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $p->phone }}</a>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $p->parishGroup?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $p->status ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $p->status_name }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('parishioners.show', $p->id) }}"
                                        class="text-sm text-slate-600 hover:text-slate-800 font-medium">Xem</a>
                                    {{-- <span class="text-slate-300">|</span>
                                    <button wire:click="edit({{ $p->id }})" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Sửa</button>
                                    <span class="text-slate-300">|</span>
                                    <button wire:click="toggleStatus({{ $p->id }})" wire:loading.attr="disabled"
                                        class="text-sm {{ $p->status ? 'text-amber-600 hover:text-amber-700' : 'text-green-600 hover:text-green-700' }} font-medium">
                                        {{ $p->status ? 'Tắt' : 'Bật' }}
                                    </button> --}}
                                    <span class="text-slate-300">|</span>
                                    <button wire:click="delete({{ $p->id }})"
                                        wire:confirm="Bạn có chắc muốn xóa giáo dân này không?"
                                        class="text-sm text-red-500 hover:text-red-600 font-medium">
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
                <p class="text-slate-400 text-lg">Chưa có giáo dân nào</p>
                <button wire:click="create" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition text-sm">
                    Thêm giáo dân đầu tiên
                </button>
            </div>
            @endif
        </div>

    </div>

    {{-- ===== MODAL: Form thêm/sửa giáo dân ===== --}}
    @if($showForm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="closeModal">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col" wire:click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-primary-50 to-white flex-shrink-0">
                <h2 class="text-lg font-bold text-slate-900">
                    {{ $editingId ? 'Cập nhật giáo dân' : 'Thêm giáo dân mới' }}
                </h2>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="border-b px-6 flex-shrink-0">
                <nav class="flex gap-1 -mb-px">
                    @foreach(['basic' => 'Cơ bản', 'address' => 'Địa chỉ', 'family' => 'Gia đình', 'classify' => 'Phân loại', 'other' => 'Khác'] as $tab => $label)
                    <button wire:click="goToTab('{{ $tab }}')"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                                    {{ $activeTab === $tab
                                        ? 'border-primary-500 text-primary-600'
                                        : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6">

                {{-- Tab: Cơ bản --}}
                @if($activeTab === 'basic')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Họ <span class="text-red-500">*</span></label>
                        <input wire:model.defer="last_name" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('last_name') border-red-400 @enderror" />
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tên <span class="text-red-500">*</span></label>
                        <input wire:model.defer="first_name" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('first_name') border-red-400 @enderror" />
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Giới tính <span class="text-red-500">*</span></label>
                        <select wire:model.defer="gender" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh</label>
                        <input wire:model.defer="birthday" type="date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('birthday') border-red-400 @enderror" />
                        @error('birthday') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">CCCD</label>
                        <input wire:model.defer="cccd" type="text" maxlength="12" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Điện thoại</label>
                        <input wire:model.defer="phone" type="tel" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input wire:model.defer="email" type="email" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-red-400 @enderror" />
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh đại diện</label>
                        <input wire:model="avatar" type="file" accept="image/*" class="w-full text-sm text-slate-600" />
                        @if($currentAvatarPath)
                        <img src="{{ asset('storage/' . $currentAvatarPath) }}" class="w-14 h-14 rounded-full mt-2 object-cover" />
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
                        <input wire:model.defer="origin" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Thường trú</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
                            <input wire:model.defer="permanent_province" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
                            <input wire:model.defer="permanent_residence" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                    </div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Tạm trú</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
                            <input wire:model.defer="temporary_province" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
                            <input wire:model.defer="temporary_residence" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                    </div>
                </div>
                @endif

                {{-- Tab: Gia đình --}}
                @if($activeTab === 'family')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tên cha</label>
                        <input wire:model.defer="father_name" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tên mẹ</label>
                        <input wire:model.defer="mother_name" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tình trạng hôn nhân</label>
                        <select wire:model.defer="married" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="0">Độc thân</option>
                            <option value="1">Đã kết hôn</option>
                            <option value="2">Góa</option>
                            <option value="3">Ly hôn</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Thuộc hộ gia đình</label>
                        <input wire:model.defer="family_id" type="number" placeholder="ID hộ gia đình" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p class="text-xs text-slate-400 mt-1">Nhập ID hoặc để trống nếu chưa có</p>
                    </div>
                </div>
                @endif

                {{-- Tab: Phân loại --}}
                @if($activeTab === 'classify')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nghề nghiệp</label>
                        <select wire:model.defer="career" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn --</option>
                            @foreach(config('parishioner.career', []) as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Học vấn</label>
                        <select wire:model.defer="education_level" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn --</option>
                            @foreach(config('parishioner.education_level', []) as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Trình độ giáo lý</label>
                        <select wire:model.defer="catechism_level" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn --</option>
                            @foreach(config('parishioner.catechism_level', []) as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dân tộc</label>
                        <select wire:model.defer="ethnic" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn --</option>
                            @foreach(config('parishioner.ethnic', []) as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Chức vụ</label>
                        <select wire:model.defer="position" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
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
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                        <textarea wire:model.defer="note" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ghi chú..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model.defer="status" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Kích hoạt</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model.defer="is_active" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Đang sinh hoạt tại giáo xứ</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model.defer="is_new_convert" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Tân tòng</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model.defer="is_included_in_stats" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Tính vào thống kê</span>
                        </label>
                    </div>
                </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-6 py-4 border-t bg-slate-50 flex-shrink-0">
                <button wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Hủy
                </button>
                <div class="flex items-center gap-3">
                    @if($activeTab !== 'basic')
                    <button wire:click="goToTab('{{ ['address' => 'basic', 'family' => 'address', 'classify' => 'family', 'other' => 'classify'][$activeTab] ?? 'basic' }}')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                        Quay lại
                    </button>
                    @endif
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
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

    {{-- ===== MODAL: Học sinh liên kết ===== --}}
    @if($showStudentLink)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="closeStudentLink">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[80vh] flex flex-col" wire:click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
                <h3 class="font-bold text-slate-900">Học sinh liên kết</h3>
                <button wire:click="closeStudentLink" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                @if($linkedStudents && $linkedStudents->count() > 0)
                <div class="space-y-3">
                    @foreach($linkedStudents as $student)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">{{ $student->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $student->lop?->schoolYear?->name }} · {{ $student->lop?->name }}
                                </p>
                            </div>
                            <a href="{{ route('students.show', $student->id) }}"
                                class="text-xs px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg hover:bg-primary-200 transition font-medium">
                                Xem
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-slate-400 py-8 text-sm">Chưa có học sinh nào liên kết</p>
                @endif
            </div>

            <div class="px-6 py-4 border-t flex justify-end">
                <button wire:click="closeStudentLink" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
                    Đóng
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Bí tích (nested Livewire component) ===== --}}
    @if($showSacraments && $sacramentParishionerId)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="closeSacraments">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col" wire:click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
                <h3 class="font-bold text-slate-900">Quản lý bí tích</h3>
                <button wire:click="closeSacraments" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                {{-- Nhúng SacramentsManager vào đây --}}
                @livewire('parishioners.sacraments-manager', ['parishionerId' => $sacramentParishionerId], key('sacraments-' . $sacramentParishionerId))
            </div>

        </div>
    </div>
    @endif

    {{-- Loading overlay --}}
    <div wire:loading.delay wire:target="save,delete,toggleStatus"
        class="fixed inset-0 bg-black/20 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-xl px-6 py-4 flex items-center gap-3 shadow-lg">
            <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span class="text-sm text-slate-700">Đang xử lý...</span>
        </div>
    </div>

</div>



{{--
<td class="px-4 py-3 text-center">
    <div class="inline-flex items-center gap-2">
        <a href="{{ route('parishioners.show', $p->id) }}"
class="text-sm text-slate-600 hover:text-slate-800 font-medium">Xem</a>
<span class="text-slate-300">|</span>
<button wire:click="delete({{ $p->id }})"
    wire:confirm="Bạn có chắc muốn xóa giáo dân này không?"
    class="text-sm text-red-500 hover:text-red-600 font-medium">
    Xóa
</button>
</div>
</td> --}}


{{-- <div class="overflow-x-auto">
    <table class="w-full">

        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">STT</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Ảnh</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Họ và tên</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Giới tính</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Tuổi</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Điện thoại</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Giáo họ</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Bí tích</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Học sinh</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Trạng thái</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500">Thao tác</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">

            @foreach($this->parishioners as $index => $p)
            <tr class="hover:bg-slate-50 transition-colors">

                <td class="px-5 py-3 text-sm text-slate-500">
                    {{ ($this->parishioners->firstItem() ?? 0) + $index }}
</td>

<td class="px-5 py-3">
    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
        @if($p->avatar_path)
        <img src="{{ asset('storage/' . $p->avatar_path) }}" class="w-full h-full object-cover">
        @else
        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-width="2" d="M16 7a4 4 0 11-8 0" />
        </svg>
        @endif
    </div>
</td>

<td class="px-5 py-3">
    <div class="font-semibold text-slate-900 text-sm">{{ $p->full_name_with_saint }}</div>
    <div class="text-xs text-slate-400">{{ $p->cccd }}</div>
</td>

<td class="px-5 py-3 text-center text-sm text-slate-600">
    {{ $p->gender_name }}
</td>

<td class="px-5 py-3 text-center text-sm text-slate-700">
    {{ $p->age ? $p->age . ' tuổi' : '—' }}
</td>

<td class="px-5 py-3 text-sm">
    {{ $p->phone ?? '—' }}
</td>

<td class="px-5 py-3 text-sm text-slate-600">
    {{ $p->parishGroup?->name ?? '—' }}
</td>

<td class="px-5 py-3 text-center">
    <button class="px-3 py-1 text-xs rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">
        Xem
    </button>
</td>

<td class="px-5 py-3 text-center text-xs text-slate-400">
    {{ $p->student ? 'Có' : '—' }}
</td>

<td class="px-5 py-3 text-center">
    <span class="px-2 py-1 text-xs rounded-full {{ $p->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
        {{ $p->status_name }}
    </span>
</td>

<td class="px-5 py-3 text-center text-sm">
    <button class="text-red-500 hover:text-red-600">Xóa</button>
</td>

</tr>
@endforeach

</tbody>

</table>
</div> --}}