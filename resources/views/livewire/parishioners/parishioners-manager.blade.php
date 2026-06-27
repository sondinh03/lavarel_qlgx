    @section('topbar')
    <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
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
                                @can('create', App\Models\Parishioner::class)
                                <x-button as="a" href="{{ route('parishioners.create') }}">
                                    <x-icon name="plus" />
                                    Thêm giáo dân
                                </x-button>
                                @endcan

                                @can('create', App\Models\Parishioner::class)
                                <x-button as="a" href="{{ route('parishioners.import') }}" variant="outline">
                                    <x-icon name="upload" />
                                    Import Sổ GĐ
                                </x-button>
                                @endcan

                                @can('viewAny', App\Models\ParishionerRegistrationRequest::class)
                                <x-button as="a" href="{{ route('parishioners.registrations.index') }}" variant="outline">
                                    <x-icon name="user" />
                                    Duyệt đăng ký
                                </x-button>
                                @endcan

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

                            @if($selectedAssociation)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full">
                                Hội đoàn: {{ $associations[(int)$selectedAssociation] ?? '' }}
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

                            {{-- Hội đoàn --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Hội đoàn</label>
                                <select wire:model="selectedAssociation"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Tất cả</option>
                                    @foreach($associations as $id => $name)
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
                                <x-table-header>STT</x-table-header>
                                <x-table-header>Ảnh</x-table-header>
                                <x-table-header>Họ và tên</x-table-header>
                                <x-table-header class="text-center">Giới tính</x-table-header>
                                <x-table-header class="text-center">Ngày sinh</x-table-header>
                                <x-table-header>Điện thoại</x-table-header>
                                <x-table-header>Giáo họ</x-table-header>
                                <x-table-header>Hội đoàn</x-table-header>
                                <x-table-header class="text-center">Trạng thái</x-table-header>
                                <x-table-header class="text-center">Thao tác</x-table-header>
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
                                        <img src="{{ avatar_url($p->avatar_path) }}" alt="{{ $p->full_name }}" class="w-full h-full object-cover" />
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
                                        {{ $p->gender === 'male' ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
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

                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $p->association?->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full
                                        {{ $p->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $p->status_name }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-tooltip content="Xem chi tiết">
                                            <a href="{{ route('parishioners.show', $p->id) }}"
                                                class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                                <x-icon name="eye" />
                                            </a>
                                        </x-tooltip>

                                        @can('update', $p)
                                        <x-tooltip content="Chỉnh sửa">
                                            <a href="{{ route('parishioners.show', ['parishioner' => $p->id, 'edit' => 'basic']) }}"
                                                class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all inline-flex">
                                                <x-icon name="edit" />
                                            </a>
                                        </x-tooltip>
                                        @endcan

                                        <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                                            <x-dropdown-item
                                                x-on:click="$dispatch('open-confirm', {
                                                    message: 'Xóa giáo dân {{ $p->full_name_with_saint }}?',
                                                    wireMethod: 'delete({{ $p->id }})'
                                                })"
                                                icon="trash"
                                                class="text-red-600 hover:bg-red-50">
                                                Xóa giáo dân
                                            </x-dropdown-item>
                                        </x-dropdown>
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
                <x-stats.page-empty
                    tone="primary"
                    title="Chưa có giáo dân nào"
                    description="Hãy thêm giáo dân đầu tiên cho giáo xứ">
                    <x-slot name="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </x-slot>
                    @can('create', App\Models\Parishioner::class)
                    <x-button as="a" href="{{ route('parishioners.create') }}" variant="primary">
                        <x-icon name="plus" />
                        Thêm giáo dân
                    </x-button>
                    @endcan
                </x-stats.page-empty>
                @endif
            </div>

        </div>

        {{-- Loading overlay --}}
        <div wire:loading.delay wire:target="delete,toggleStatus"
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