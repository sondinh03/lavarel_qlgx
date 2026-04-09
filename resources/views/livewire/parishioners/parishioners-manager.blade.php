@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Quản lý giáo dân'],
    ]" />
@endsection

<div class="min-h-screen bg-slate-50 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">



        @if (session('message'))
        <x-toast-notification type="success">{{ session('message') }}</x-toast-notification>
        @endif
        @if (session('error'))
        <x-toast-notification type="error">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- Header + Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-300 overflow-hidden">
            <x-page-header
                title="Quản lý giáo dân"
                description="Danh sách giáo dân trong giáo xứ"
                :stat-value="$this->parishioners->total()"
                stat-label="Giáo dân" />

            <div class="px-6 py-4 border-b border-slate-300 bg-slate-50/70 space-y-4">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <input wire:model.debounce.400ms="search" type="search"
                        placeholder="Tìm theo tên, CCCD, SĐT..."
                        class="w-full sm:w-80 px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />

                    <button wire:click="create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm giáo dân
                    </button>
                </div>

                {{-- Filters --}}
                <div class="space-y-4">
                    <div class="text-xs font-semibold text-slate-500 uppercase">
                        Bộ lọc
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Giới tính</label>
                            <select wire:model="selectedGender" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Tất cả</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Nhóm tuổi</label>
                            <select wire:model="selectedAgeGroup" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Tất cả</option>
                                @foreach($ageGroups as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Hôn nhân</label>
                            <select wire:model="selectedMarried" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Tất cả</option>
                                <option value="0">Độc thân</option>
                                <option value="1">Đã kết hôn</option>
                                <option value="2">Góa</option>
                                <option value="3">Ly hôn</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Trạng thái</label>
                            <select wire:model="selectedStatus" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Tất cả</option>
                                <option value="1">Hoạt động</option>
                                <option value="0">Tắt</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Giáo họ</label>
                            <select wire:model="selectedGroup" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Tất cả</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">Tình trạng</label>
                            <select wire:model="selectedDeceased" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="0">Còn sống</option>
                                <option value="1">Đã qua đời</option>
                                <option value="">Tất cả</option>
                            </select>
                        </div>

                        <div class="col-span-full flex justify-end pt-2">
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
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-300 overflow-hidden">
            @if($this->parishioners->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-300">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">STT</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ảnh</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Họ và tên</th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Giới tính</th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Ngày sinh</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Điện thoại</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Giáo họ</th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Trạng thái</th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($this->parishioners as $index => $p)
                        <tr class="hover:bg-slate-50 transition-colors {{ $p->is_deceased ? 'opacity-60' : '' }}"
                            wire:key="p-{{ $p->id }}">

                            <td class="px-4 py-4 text-sm text-slate-500">
                                {{ ($this->parishioners->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-4">
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

                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900 text-sm">{{ $p->full_name_with_saint }}</div>
                                @if($p->cccd)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $p->cccd }}</div>
                                @endif
                                @if($p->is_deceased)
                                <span class="inline-flex mt-0.5 px-1.5 py-0.5 rounded text-xs font-medium bg-slate-200 text-slate-600">
                                    Đã qua đời {{ $p->death_date?->format('d/m/Y') }}
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $p->gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $p->gender_name }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center text-sm text-slate-700">
                                {{ $p->birthday?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-sm">
                                @if($p->phone)
                                <a href="tel:{{ $p->phone }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $p->phone }}</a>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-sm text-slate-600">
                                {{ $p->parishGroup?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $p->status ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $p->status_name }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center">
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($this->parishioners->hasPages())
            <div class="px-6 py-4 border-t border-slate-300">
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

    {{-- ===== MODAL: Form thêm/sửa ===== --}}
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
                            {{ $activeTab === $tab ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6">

                @php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

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
                            <input wire:model.defer="status" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Kích hoạt</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model.defer="is_active" type="checkbox" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700">Đang sinh hoạt tại xứ</span>
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

                    {{-- Tử vong --}}
                    <div class="pt-3 border-t border-slate-300">
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