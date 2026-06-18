@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => auth()->user()->isCatechist() ? route('catechist.dashboard') : route('parish-admin.dashboard')],
        ['label' => 'Học sinh']
    ]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showEnroll: false, showLink: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openEnrollModal', () => { showEnroll = true; });
            Livewire.on('closeEnrollModal', () => { showEnroll = false; });
            Livewire.on('openLinkModal', () => { showLink = true; });
            Livewire.on('closeLinkModal', () => { showLink = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">
        @php $isCatechist = auth()->user()->isCatechist(); @endphp

        {{-- Header card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <x-page-header
                title="Danh sách học sinh"
                :description="$isCatechist ? 'Lớp được phân công' : 'Quản lý hồ sơ và ghi danh theo năm học'"
                :stat-value="$students->total()"
                stat-label="Học sinh"
                icon-type="students" />

            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl">
                @if($isCatechist)
                <div class="flex flex-col gap-4">
                    <livewire:filters.filter-bar
                        :parish-id="$parishId"
                        :show-nam-hoc="false"
                        :show-khoi="false"
                        :show-lop="true"
                        :show-ky="false"
                        :selected-nam-hoc="$selectedNamHoc"
                        :selected-khoi="$selectedKhoi"
                        :selected-lop="$selectedLop" />

                    <x-search-input
                        placeholder="Tìm kiếm học sinh..."
                        wire-model="search"
                        debounce="500ms"
                        class="max-w-md" />
                </div>
                @else
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1 min-w-0">
                            <livewire:filters.filter-bar
                                :parish-id="$parishId"
                                :show-nam-hoc="true"
                                :show-khoi="true"
                                :show-lop="true"
                                :show-ky="false"
                                :selected-nam-hoc="$selectedNamHoc"
                                :selected-khoi="$selectedKhoi"
                                :selected-lop="$selectedLop" />
                        </div>
                        <div class="flex-shrink-0 pb-0.5">
                            <x-button wire:click="resetFilters" variant="subtle">
                                <x-icon name="refresh" />
                                Đặt lại
                            </x-button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            placeholder="Tìm theo tên thánh, họ tên, mã HS..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md" />

                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <x-tooltip content="Vui lòng chọn lớp trước" :show="!$selectedLop">
                                <x-button
                                    wire:click="openEnrollModal('existing')"
                                    :disabled="!$selectedLop">
                                    <x-icon name="user-plus" />
                                    Ghi danh
                                </x-button>
                            </x-tooltip>

                            <x-button
                                as="a"
                                href="{{ route('students.statistics', ['namHoc' => $selectedNamHoc]) }}"
                                variant="outline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Thống kê
                            </x-button>

                            <x-dropdown label="Khác" icon="grid" align="right" position="fixed">
                                <x-dropdown-item wire:click="printSelected" icon="printer">
                                    In thẻ
                                </x-dropdown-item>
                                <div class="my-1 border-t border-slate-100"></div>
                                <x-dropdown-item as="a" :href="$this->importUrl" icon="upload">
                                    Import Excel
                                </x-dropdown-item>
                                <x-dropdown-item wire:click="export" icon="download">
                                    Export Excel
                                </x-dropdown-item>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($selectedNamHoc)
        @if($isCatechist)
        {{-- ══ CATECHIST: Card list ══ --}}
        <div class="space-y-3" wire:key="student-cards-{{ $listContext }}">
            @if($students && $students->count() > 0)

            @foreach($students as $student)
            <a href="{{ route('students.show', $student->id) }}"
                wire:key="student-card-{{ $student->id }}"
                class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4
                      flex items-center gap-3 hover:border-primary-300
                      hover:bg-primary-50/30 transition-all active:scale-[0.99] block">

                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex-shrink-0 flex items-center justify-center
                    text-sm font-semibold shadow-sm
                    bg-primary-50 text-primary-800">
                    @if($student->avatar_path)
                    <img src="{{ asset($student->avatar_path) }}"
                        class="w-full h-full rounded-full object-cover" />
                    @else
                    {{ strtoupper(mb_substr($student->last_name, 0, 1) . mb_substr($student->first_name, 0, 1)) }}
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-sm font-medium text-slate-900 truncate">
                            {{ $student->saint->name ?? '' }} {{ $student->last_name }} {{ $student->first_name }}
                        </span>
                        <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $student->gender === 'male'
                                ? 'bg-primary-100 text-primary-700'
                                : 'bg-pink-100 text-pink-700' }}">
                            {{ $student->gender === 'male' ? 'Nam' : 'Nữ' }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mb-1.5">
                        {{ $student->birthday?->format('d/m/Y') ?? '—' }}
                        · {{ $student->parishGroup->name ?? '—' }}
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <span>
                            <span class="text-slate-500">Bố:</span>
                            {{ $student->father_name ?? '—' }}
                        </span>
                        @if($student->phone)
                        <span>{{ $student->phone }}</span>
                        @endif
                    </div>
                </div>

                {{-- Arrow --}}
                <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @endforeach

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="pt-2">
                <x-pagination :paginator="$students" :per-page-options="[15, 25, 50]" />
            </div>
            @endif

            @else
            <x-stats.page-empty
                title="Không tìm thấy học sinh"
                description="Không có học sinh nào phù hợp với bộ lọc của bạn">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </x-slot>
            </x-stats.page-empty>
            @endif
        </div>

        @else
        {{-- ══ ADMIN: Table đầy đủ ══ --}}
        @if($students && $students->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
            wire:key="student-list-{{ $listContext }}">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>
                                <input type="checkbox" wire:model="selectAll"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </x-table-header>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header class="w-[180px]"
                                :sortable="true" sort-field="last_name"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Họ & Tên đệm
                            </x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="first_name"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Tên
                            </x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="birthday"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Ngày sinh
                            </x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="gender"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Giới tính
                            </x-table-header>
                            <x-table-header class="w-[140px]">Họ tên bố</x-table-header>
                            <x-table-header class="w-[140px]">Số điện thoại</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 overflow-visible">
                        @foreach($students as $index => $student)
                        <tr class="hover:bg-slate-50 transition-colors overflow-visible" wire:key="student-{{ $student->id }}">

                            <td class="px-4 py-3 text-sm text-slate-500">
                                <input type="checkbox" wire:model="selectedStudents"
                                    value="{{ $student->id }}"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-500">
                                {{ ($students->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-900">
                                {{ $student->saint->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                {{ $student->last_name }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                {{ $student->first_name }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $student->birthday?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $student->gender === 'male'
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'bg-pink-100 text-pink-700' }}">
                                    {{ $student->gender_text }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                {{ $student->father_name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $student->phone ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                {{ $student->parishGroup->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 overflow-visible">
                                <div class="flex items-center justify-center gap-1">
                                    <x-tooltip content="Xem chi tiết">
                                        <a href="{{ route('students.show', $student->id) }}"
                                            class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                            <x-icon name="eye" />
                                        </a>
                                    </x-tooltip>

                                    <x-tooltip content="Chỉnh sửa">
                                        <a href="{{ route('students.edit', $student->id) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="edit" />
                                        </a>
                                    </x-tooltip>
                                    <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                                        <x-dropdown-item wire:click="openLinkParishioner({{ $student->id }})" icon="link">
                                            Liên kết giáo dân
                                        </x-dropdown-item>

                                        <div class="h-px bg-slate-100 my-1"></div>

                                        <x-dropdown-item
                                            x-on:click="$dispatch('open-confirm', {
                                                message: 'Xóa học sinh {{ $student->full_name_with_saint }} khỏi lớp?',
                                                wireMethod: 'delete({{ $student->id }})'
                                            })"
                                            icon="trash"
                                            class="text-red-600 hover:bg-red-50">
                                            Xóa học sinh
                                        </x-dropdown-item>
                                    </x-dropdown>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bulk action bar --}}
            @if(count($selectedStudents) > 0)
            <div class="px-6 py-3 bg-primary-50 border-t border-primary-200 flex items-center justify-between">
                <span class="text-sm font-semibold text-primary-700">
                    Đã chọn {{ count($selectedStudents) }} học sinh
                </span>
                <button type="button" wire:click="$set('selectedStudents', [])"
                    class="px-3 py-1.5 text-sm font-medium text-primary-600
                       hover:bg-primary-100 rounded-lg transition">
                    Bỏ chọn tất cả
                </button>
            </div>
            @endif

            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50, 100]" />
            </div>
            @endif
        </div>
        @else
        <x-stats.page-empty
            title="Không tìm thấy học sinh"
            description="Không có học sinh nào phù hợp với bộ lọc của bạn">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </x-slot>
        </x-stats.page-empty>
        @endif
        @endif

        @else
        <x-stats.page-empty
            tone="primary"
            title="Vui lòng chọn năm học"
            description="Chọn năm học ở bộ lọc phía trên để xem danh sách học sinh">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </x-slot>
        </x-stats.page-empty>
        @endif

        @if(!$isCatechist)
        {{-- Modal liên kết giáo dân --}}
        <div
            x-show="showLink"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            @click="showLink = false; $wire.closeLinkModal()"
            @keydown.escape.window="showLink = false; $wire.closeLinkModal()">

            <div
                x-show="showLink"
                x-transition
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop>

                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Liên kết hồ sơ giáo dân</h2>
                            <p class="text-sm text-slate-600 mt-1">
                                @if($showLinkModal)
                                    Tìm thấy {{ $suggestedParishioners->count() }} giáo dân có thể trùng khớp
                                @else
                                    Chọn giáo dân phù hợp để liên kết với học sinh
                                @endif
                            </p>
                        </div>
                        <button type="button"
                            @click="showLink = false; $wire.closeLinkModal()"
                            class="flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    @if($showLinkModal)
                    @if($suggestedParishioners->count() > 0)
                    <div class="space-y-3">
                        @foreach($suggestedParishioners as $p)
                        <div class="border border-slate-200 rounded-xl p-4 hover:border-primary-300
                            hover:bg-primary-50/30 transition-all">
                            <div class="flex items-center justify-between gap-4">
                                {{-- Thông tin giáo dân --}}
                                <div class="flex items-center gap-3">
                                    {{-- Avatar --}}
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex-shrink-0">
                                        @if($p->avatar_path)
                                        <img src="{{ asset('storage/' . $p->avatar_path) }}"
                                            class="w-full h-full object-cover" />
                                        @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $p->full_name_with_saint }}</div>
                                        <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-500">
                                            <span>{{ $p->gender === 'male' ? 'Nam' : 'Nữ' }}</span>
                                            @if($p->birthday)
                                            <span>{{ $p->birthday->format('d/m/Y') }}</span>
                                            @endif
                                            @if($p->cccd)
                                            <span>CCCD: {{ $p->cccd }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Nút liên kết --}}
                                <button wire:click="confirmLink({{ $p->id }})"
                                    class="flex-shrink-0 px-4 py-2 bg-primary-500 text-white text-sm
                                   font-semibold rounded-xl hover:bg-primary-600 transition-colors">
                                    Liên kết
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @else
                    {{-- Không tìm thấy gợi ý --}}
                    <div class="text-center py-6">
                        <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-3 text-slate-600 font-medium">Không tìm thấy giáo dân phù hợp</p>
                        <p class="mt-1 text-sm text-slate-400">
                            Không có giáo dân nào khớp họ tên và ngày sinh
                        </p>
                    </div>
                    @endif
                    @endif
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-button variant="outline" wire="skipLink">
                        Bỏ qua
                    </x-button>
                </div>
            </div>
        </div>

        {{-- Modal ghi danh — 3 tabs --}}
        <div
            x-show="showEnroll"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="enroll-modal-title"
            @click="showEnroll = false; $wire.closeEnrollModal()"
            @keydown.escape.window="showEnroll = false; $wire.closeEnrollModal()">

            <div
                x-show="showEnroll"
                x-transition
                class="bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop>

                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 id="enroll-modal-title" class="text-xl font-bold text-slate-900">
                                Ghi danh học sinh
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                @if($lop) Lớp: <strong>{{ $lop->name }}</strong> @endif
                            </p>
                        </div>
                        <button type="button"
                            @click="showEnroll = false; $wire.closeEnrollModal()"
                            class="flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit flex-wrap">

                        {{-- Tab 1: Học sinh có sẵn --}}
                        <button type="button"
                            wire:click="switchEnrollTab('existing')"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                   rounded-lg transition-all
                                   {{ $enrollTab === 'existing'
                                       ? 'bg-white text-primary-700 shadow-sm'
                                       : 'text-slate-500 hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Học sinh có sẵn
                        </button>

                        {{-- Tab 2: Tạo mới --}}
                        <a href="{{ route('students.create') }}{{ $selectedLop ? '?classId='.$selectedLop : '' }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                rounded-lg transition-all
                                {{ $enrollTab === 'new'
                                    ? 'bg-white text-primary-700 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Tạo mới
                        </a>

                        {{-- Tab 3: Import giáo dân --}}
                        <button type="button"
                            wire:click="switchEnrollTab('parishioner')"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                   rounded-lg transition-all
                                   {{ $enrollTab === 'parishioner'
                                       ? 'bg-white text-primary-700 shadow-sm'
                                       : 'text-slate-500 hover:text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Import giáo dân
                        </button>

                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @if($showEnrollNewModal)

                    @if($enrollTab === 'existing')
                    <div class="p-6 space-y-4">

                        {{-- Search + năm sinh --}}
                        <div class="flex items-center gap-2">
                            <input
                                wire:model.debounce.300ms="modalSearch"
                                type="text"
                                placeholder="Tìm kiếm học sinh..."
                                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">

                            <select
                                wire:model="birthYear"
                                class="w-44 px-3 py-2.5 rounded-xl border border-slate-200 bg-white
                                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                                <option value="">-- Tất cả năm sinh --</option>
                                @foreach($this->getQuickYearOptions() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>

                            @if($birthYear)
                            <button wire:click="clearBirthYearFilters" type="button"
                                class="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>

                        @if($availableStudents && $availableStudents->count() > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full border-separate border-spacing-0">
                                <thead class="bg-slate-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <input
                                                type="checkbox"
                                                wire:model="selectAllInModal"
                                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Mã HS</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Tên thánh</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Họ tên</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Ngày sinh</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Giáo họ</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($availableStudents as $student)
                                    <tr class="hover:bg-slate-50" wire:key="avail-{{ $student->id }}">
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                wire:model="studentsToAdd"
                                                value="{{ $student->id }}"
                                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-sm font-mono text-primary-600">
                                            {{ $student->student_code ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-900">
                                            {{ $student->saint->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                            {{ $student->last_name }} {{ $student->first_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ $student->birthday?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ $student->parishGroup->name ?? 'Chưa xác định' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <x-tooltip content="Xóa hồ sơ">
                                                <button wire:click="deleteProfile({{ $student->id }})"
                                                    wire:confirm="Bạn có chắc muốn xóa hồ sơ học sinh {{ $student->full_name }}? Hành động này không thể hoàn tác!"
                                                    class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition-all">
                                                    <x-icon name="trash" />
                                                </button>
                                            </x-tooltip>
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($availableStudents->hasPages())
                        <div class="mt-2">{{ $availableStudents->links() }}</div>
                        @endif

                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mt-4 text-lg text-slate-500">Không có học sinh nào để thêm</p>
                            <p class="mt-1 text-sm text-slate-400">Thử tab "Tạo mới" hoặc "Import giáo dân"</p>
                        </div>
                        @endif

                    </div>
                    @endif

                    {{-- ════════ TAB 3: Import giáo dân ════════ --}}
                    @if($enrollTab === 'parishioner')
                    <div class="p-6 space-y-4">

                        {{-- Search + filters --}}
                        <div class="space-y-3">
                            <input
                                wire:model.debounce.300ms="parishionerSearch"
                                type="text"
                                placeholder="Tìm kiếm giáo dân..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">

                            <div class="flex items-center gap-2 flex-wrap">
                                <select wire:model="parishionerBirthYear"
                                    class="flex-1 min-w-[140px] px-3 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                    <option value="">-- Tất cả năm sinh --</option>
                                    @foreach($this->getQuickYearOptions() as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>

                                @if($parishionerBirthYear)
                                <button wire:click="$set('parishionerBirthYear', null)" type="button"
                                    class="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                @endif

                                <input type="number" wire:model.lazy="ageFrom"
                                    placeholder="Tuổi từ"
                                    class="w-28 px-3 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">

                                <input type="number" wire:model.lazy="ageTo"
                                    placeholder="đến"
                                    class="w-28 px-3 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">

                                @if($ageFrom || $ageTo)
                                <button wire:click="$set('ageFrom', null); $set('ageTo', null);" type="button"
                                    class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                    ✕ Xóa tuổi
                                </button>
                                @endif
                            </div>
                        </div>

                        @if($availableParishioners && $availableParishioners->count() > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full border-separate border-spacing-0">
                                <thead class="bg-slate-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <input type="checkbox"
                                                wire:model="selectAllParishioners"
                                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Họ</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Tên</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Ngày sinh</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Tuổi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Giới tính</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Điện thoại</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($availableParishioners as $p)
                                    <tr class="hover:bg-slate-50" wire:key="parish-{{ $p->id }}">
                                        <td class="px-4 py-3">
                                            <input type="checkbox"
                                                wire:model="selectedParishioners"
                                                value="{{ $p->id }}"
                                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                            {{ $p->last_name }}
                                        </td>

                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                            {{ $p->first_name }}
                                        </td>

                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ $p->birthday?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            @if($p->birthday)
                                            {{ \Carbon\Carbon::parse($p->birthday)->age }} tuổi
                                            @else
                                            —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                         {{ $p->gender == 'male' || $p->gender == 1 ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                                {{ $p->gender == 'male' || $p->gender == 1 ? 'Nam' : 'Nữ' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ $p->phone ?? '—' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($availableParishioners->hasPages())
                        <div class="mt-2">{{ $availableParishioners->links() }}</div>
                        @endif

                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-4 text-lg text-slate-500">Không có giáo dân nào phù hợp</p>
                            <p class="mt-1 text-sm text-slate-400">Chỉ hiển thị giáo dân chưa có hồ sơ học sinh</p>
                        </div>
                        @endif

                    </div>
                    @endif

                    @endif
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50">
                    @if($enrollTab === 'existing')
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600">
                            Đã chọn: <span class="font-semibold text-primary-600">{{ count($studentsToAdd) }}</span> học sinh
                        </span>
                        <div class="flex gap-3">
                            <x-button variant="outline" @click="showEnroll = false; $wire.closeEnrollModal()">
                                Hủy
                            </x-button>
                            <x-button
                                variant="primary"
                                wire:click="addStudentsToClass"
                                :disabled="empty($studentsToAdd)"
                                :loading="true"
                                loading-target="addStudentsToClass">
                                <x-icon name="plus" />
                                Thêm vào lớp
                            </x-button>
                        </div>
                    </div>
                    @endif

                    @if($enrollTab === 'parishioner')
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600">
                            Đã chọn: <span class="font-semibold text-primary-600">{{ count($selectedParishioners) }}</span> giáo dân
                        </span>
                        <div class="flex gap-3">
                            <x-button variant="outline" @click="showEnroll = false; $wire.closeEnrollModal()">
                                Hủy
                            </x-button>
                            <x-button
                                variant="primary"
                                wire:click="importParishionersToStudents"
                                :disabled="empty($selectedParishioners)"
                                :loading="true"
                                loading-target="importParishionersToStudents">
                                <x-icon name="plus" />
                                Import{{ count($selectedParishioners) > 0 ? ' (' . count($selectedParishioners) . ')' : '' }}
                            </x-button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">Danh sách học sinh</span>
@endpush
