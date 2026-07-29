@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => auth()->user()->usesCatechistLayout() ? route('catechist.dashboard') : route('parish-admin.dashboard')],
        ['label' => 'Phiên điểm danh', 'url' => route('session.index')]
    ]" />
@endsection

@php $isMobileUi = $isMobileUi ?? false; @endphp

<div class="min-h-screen bg-apple-gray {{ $isMobileUi ? 'p-0' : 'p-2 sm:p-4 lg:p-6' }}"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal',  () => { showForm = true;  });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto {{ $isMobileUi ? 'max-w-2xl' : 'max-w-7xl' }}">
        <x-mac-panel :overflow="true" class="{{ $isMobileUi ? '!rounded-none sm:!rounded-2xl border-0 sm:border' : '' }}">
            @if($isMobileUi)
            <div class="px-4 pt-5 pb-3 mac-hairline-b">
                <h1 class="text-2xl font-bold text-slate-800">Phiên điểm danh</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if($subjectTarget === 'teachers')
                        Buổi điểm danh giáo lý viên
                    @else
                        Tạo / khóa buổi theo lớp
                    @endif
                    @if($total > 0)
                        · {{ $total }} buổi
                    @endif
                </p>
            </div>
            @else
            <x-page-header
                title="Quản lý phiên điểm danh"
                :description="$subjectTarget === 'teachers'
                    ? 'Danh sách buổi điểm danh giáo lý viên theo năm học'
                    : 'Danh sách buổi điểm danh theo lớp và năm học'"
                icon-type="attendance"
                :count="$total" />
            @endif

            <div class="{{ $isMobileUi ? 'p-3' : 'p-4 lg:p-6' }} mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-3 {{ $isMobileUi ? '' : 'gap-4' }}">
                    @if(auth()->user()?->canCreateAttendanceSessions())
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit flex-shrink-0 min-w-0">
                            <button type="button" wire:click="switchSubjectTarget('students')"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                    {{ $subjectTarget !== 'teachers'
                                        ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                        : 'text-slate-600 hover:text-slate-900' }}">
                                Học sinh
                            </button>
                            <button type="button" wire:click="switchSubjectTarget('teachers')"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                    {{ $subjectTarget === 'teachers'
                                        ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                        : 'text-slate-600 hover:text-slate-900' }}">
                                Giáo lý viên
                            </button>
                        </div>

                        <x-tooltip content="Vui lòng chọn năm học trước" :show="!$selectedNamHoc">
                            <x-button wire:click="create" size="sm" :disabled="!$selectedNamHoc"
                                class="flex-shrink-0 whitespace-nowrap">
                                <x-icon name="plus" />
                                Tạo phiên mới
                            </x-button>
                        </x-tooltip>
                    </div>
                    @endif

                    <div class="flex items-end gap-3">
                        <div class="flex-1 min-w-0">
                            <livewire:filters.filter-bar
                                wire:key="session-filters-{{ $subjectTarget }}"
                                :parish-id="$parishId"
                                :show-nam-hoc="true"
                                :show-khoi="$subjectTarget !== 'teachers'"
                                :show-lop="$subjectTarget !== 'teachers'"
                                :show-ky="false"
                                :selected-nam-hoc="$selectedNamHoc"
                                :selected-khoi="$selectedKhoi"
                                :selected-lop="$selectedClassId" />
                        </div>
                    </div>

                    @if(! $isMobileUi && $subjectTarget !== 'teachers' && $selectedNamHoc)
                    <x-inline-tip>
                        Để tạo phiên <strong>đồng loạt toàn giáo xứ</strong>, để trống bộ lọc
                        <strong>Khối</strong> và <strong>Lớp</strong> (tức chọn tất cả), rồi bấm
                        <strong>Tạo phiên mới</strong> — hệ thống sẽ tạo cho mọi lớp trong năm học.
                        @if(!$selectedKhoi && !$selectedClassId)
                        <span class="block mt-1 text-primary-800">
                            Hiện đang áp dụng cho <strong>toàn bộ {{ count($this->resolveClassIds()) }} lớp</strong> của năm học.
                        </span>
                        @elseif($selectedKhoi && !$selectedClassId)
                        <span class="block mt-1">
                            Hiện đang áp dụng cho <strong>toàn khối</strong>
                            ({{ count($this->resolveClassIds()) }} lớp). Bỏ chọn khối nếu muốn tạo toàn xứ.
                        </span>
                        @endif
                    </x-inline-tip>
                    @endif

                    @if(! $isMobileUi && $subjectTarget !== 'teachers' && ($autoFinalizeEnabled ?? true))
                    <x-inline-tip>
                        Giáo xứ đang kết luận số liệu lúc
                        <strong>{{ $autoFinalizeTime ?? '20:00' }}</strong>:
                        với buổi đã có người điểm danh, phần còn lại được coi là vắng không phép.
                        Hệ thống không tạo bản ghi và không tự khóa; khóa buổi sẽ kết luận sớm.
                        @if($canManageParishSettings ?? false)
                        <a href="{{ route('parish.settings') }}" class="font-semibold text-primary-700 underline ml-1">Đổi giờ / tắt →</a>
                        @endif
                    </x-inline-tip>
                    @elseif(! $isMobileUi && $subjectTarget !== 'teachers' && ($canManageParishSettings ?? false))
                    <x-inline-tip tone="amber">
                        Kết luận theo giờ chốt đang <strong>tắt</strong>; chỉ buổi được khóa mới kết luận học sinh còn thiếu là vắng không phép.
                        <a href="{{ route('parish.settings') }}" class="font-semibold text-primary-700 underline ml-1">Bật lại tại Thông tin giáo xứ →</a>
                    </x-inline-tip>
                    @endif

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <x-search-input
                            placeholder="Tìm theo ngày (vd: 12/03/2026)..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md w-full" />
                    </div>
                </div>
            </div>

            @if($currentNamHoc)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-white/40 text-sm text-slate-700">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-semibold text-slate-900">{{ $currentNamHoc->name }}</span>
                    @if($currentNamHoc->start_date_one && $currentNamHoc->end_date_one)
                    <span class="text-slate-500">
                        HK1: {{ $currentNamHoc->start_date_one->format('d/m/Y') }}
                        – {{ $currentNamHoc->end_date_one->format('d/m/Y') }}
                    </span>
                    @endif
                    @if($currentNamHoc->start_date_two && $currentNamHoc->end_date_two)
                    <span class="text-slate-500">
                        HK2: {{ $currentNamHoc->start_date_two->format('d/m/Y') }}
                        – {{ $currentNamHoc->end_date_two->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
            </div>
            @endif

            @if($selectedNamHoc)
            @if($sessions->count() > 0)

            @if($isMobileUi)
            {{-- ── Mobile: card list ── --}}
            <div class="p-3 space-y-2.5">
                @foreach($sessions as $session)
                <div class="rounded-2xl bg-white/80 border border-black/[0.06] shadow-mac-sm p-3.5"
                    wire:key="session-card-{{ $session['id'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $session['dayName'] }} · {{ $session['fullDate'] }}
                            </p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                    {{ (int) $session['type'] === 1
                                        ? 'bg-primary-50 text-primary-700'
                                        : ((int) $session['type'] === 3
                                            ? 'bg-amber-50 text-amber-700'
                                            : 'bg-purple-50 text-purple-700') }}">
                                    {{ $session['typeLabel'] }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                    {{ $session['statusClass'] }}">
                                    @if($session['locked'])
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    @endif
                                    {{ $session['statusLabel'] }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">
                                @if($session['start_time'] || $session['end_time'])
                                    {{ $session['start_time'] ?? '--:--' }} – {{ $session['end_time'] ?? '--:--' }}
                                @else
                                    Chưa đặt giờ
                                @endif
                                · Có mặt {{ $session['stats']['present'] }}
                                · Vắng {{ ($session['stats']['absent_excused'] ?? 0) + ($session['stats']['absent_unexcused'] ?? 0) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        @if($subjectTarget === 'teachers')
                        <a href="{{ route('attendance.show', [
                                'target' => 'teachers',
                                'namHoc' => $selectedNamHoc,
                                'type'   => $session['type'],
                                'date'   => $session['dateStr'],
                            ]) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 h-10 rounded-xl
                                bg-primary-50 text-primary-700 text-sm font-semibold hover:bg-primary-100 transition">
                            Điểm danh
                        </a>
                        @else
                        <a href="{{ route('attendance.show', [
                                'classId' => $selectedClassId ?? '',
                                'type'    => $session['type'],
                                'date'    => $session['dateStr'],
                            ]) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 h-10 rounded-xl
                                bg-primary-50 text-primary-700 text-sm font-semibold hover:bg-primary-100 transition">
                            Điểm danh
                        </a>
                        @endif

                        <button type="button"
                            wire:click="toggleStatus({{ $session['id'] }})"
                            wire:loading.attr="disabled"
                            class="h-10 px-3 rounded-xl text-sm font-semibold border border-black/[0.06]
                                {{ $session['locked']
                                    ? 'bg-green-50 text-green-700'
                                    : 'bg-amber-50 text-amber-700' }}">
                            {{ $session['locked'] ? 'Mở' : 'Khóa' }}
                        </button>

                        @if($canDeleteSessions ?? false)
                        <button type="button"
                            wire:click="delete({{ $session['id'] }})"
                            wire:confirm="Xóa phiên {{ $session['fullDate'] }}?"
                            class="h-10 w-10 inline-flex items-center justify-center rounded-xl
                                bg-red-50 text-red-600">
                            <x-icon name="trash" />
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($sessions->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$sessions" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            {{-- ── Desktop: table ── --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="date"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Ngày
                            </x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="type"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Loại
                            </x-table-header>
                            <x-table-header>Thời gian</x-table-header>
                            <x-table-header class="text-center">Điểm danh</x-table-header>
                            <x-table-header
                                class="text-center"
                                :sortable="true" sort-field="status"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Trạng thái
                            </x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($sessions as $index => $session)
                        <tr class="hover:bg-black/[0.03] transition-colors"
                            wire:key="session-{{ $session['id'] }}">

                            <td class="px-4 py-3 text-sm font-semibold text-slate-500">
                                {{ ($sessions->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900 text-sm whitespace-nowrap">
                                    {{ $session['dayName'] }} – {{ $session['fullDate'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                    {{ (int) $session['type'] === 1
                                        ? 'bg-primary-50/80 text-primary-700'
                                        : ((int) $session['type'] === 3
                                            ? 'bg-amber-50/80 text-amber-700'
                                            : 'bg-purple-50/80 text-purple-700') }}">
                                    {{ $session['typeLabel'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
                                @if($session['start_time'] || $session['end_time'])
                                    {{ $session['start_time'] ?? '--:--' }} – {{ $session['end_time'] ?? '--:--' }}
                                @else
                                    <span class="text-slate-400">Chưa đặt</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3 text-xs">
                                    <span class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        <span class="text-green-700 font-semibold">{{ $session['stats']['present'] }}</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                                        <span class="text-yellow-700 font-semibold">{{ $session['stats']['absent_excused'] }}</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="text-red-700 font-semibold">{{ $session['stats']['absent_unexcused'] }}</span>
                                    </span>
                                </div>
                                @if(($session['stats']['total'] ?? 0) > 0)
                                <div class="text-center mt-1 text-xs text-slate-500">
                                    {{ number_format($session['stats']['present_rate'], 1) }}% có mặt
                                </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $session['statusClass'] }}">
                                    @if($session['locked'])
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    @endif
                                    {{ $session['statusLabel'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3 overflow-visible">
                                <div class="flex items-center justify-center gap-2">
                                    <x-tooltip content="Điểm danh">
                                        @if($subjectTarget === 'teachers')
                                        <a href="{{ route('attendance.show', [
                                                'target' => 'teachers',
                                                'namHoc' => $selectedNamHoc,
                                                'type'   => $session['type'],
                                                'date'   => $session['dateStr'],
                                            ]) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="clipboard" />
                                        </a>
                                        @else
                                        <a href="{{ route('attendance.show', [
                                                'classId' => $selectedClassId ?? '',
                                                'type'    => $session['type'],
                                                'date'    => $session['dateStr'],
                                            ]) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="clipboard" />
                                        </a>
                                        @endif
                                    </x-tooltip>

                                    <x-tooltip :content="$session['locked'] ? 'Mở lại phiên' : 'Khóa phiên'">
                                        <button
                                            wire:click="toggleStatus({{ $session['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="p-2 rounded-lg transition-all
                                                {{ $session['locked']
                                                    ? 'hover:bg-green-50 text-green-700'
                                                    : 'hover:bg-amber-50 text-amber-600' }}">
                                            @if($session['locked'])
                                            <x-icon name="check" />
                                            @else
                                            <x-icon name="archive" />
                                            @endif
                                        </button>
                                    </x-tooltip>

                                    @if($canDeleteSessions ?? false)
                                    <x-tooltip content="Xóa phiên">
                                        <button
                                            wire:click="delete({{ $session['id'] }})"
                                            wire:confirm="Xóa phiên điểm danh ngày {{ $session['fullDate'] }}?"
                                            class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-all">
                                            <x-icon name="trash" />
                                        </button>
                                    </x-tooltip>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 lg:px-6 py-3 mac-hairline-t bg-slate-50/40 flex flex-col sm:flex-row
                        items-start sm:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Có mặt
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Vắng có phép
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Vắng không phép
                    </span>
                </div>
            </div>

            @if($sessions->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$sessions" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @endif

            @else
            <div class="px-4 lg:px-6 py-3 mac-hairline-b">
                <x-inline-tip>
                    @if($subjectTarget === 'teachers')
                        Chưa có buổi điểm danh GLV. Bấm <strong>Tạo phiên mới</strong> (theo ngày / tuần / tùy chọn).
                    @elseif(!$selectedClassId)
                        Chọn <strong>khối → lớp</strong> ở bộ lọc, rồi bấm <strong>Tạo phiên mới</strong>.
                    @else
                        Lớp này chưa có buổi. Bấm <strong>Tạo phiên mới</strong> (theo ngày / tuần / tùy chọn).
                        <a href="{{ route('help.attendance') }}" class="font-semibold underline hover:text-primary-900 ml-1">Hướng dẫn điểm danh →</a>
                    @endif
                </x-inline-tip>
            </div>
            <x-stats.page-empty
                :panel="false"
                tone="primary"
                :title="!empty(trim($search))
                    ? 'Không tìm thấy phiên điểm danh'
                    : ($subjectTarget === 'teachers'
                        ? 'Chưa có buổi điểm danh GLV'
                        : (!$selectedClassId ? 'Vui lòng chọn lớp' : 'Chưa có phiên điểm danh'))"
                :description="!empty(trim($search))
                    ? 'Thử thay đổi từ khóa tìm kiếm'
                    : ($subjectTarget === 'teachers'
                        ? 'Tạo phiên điểm danh GLV đầu tiên cho năm học'
                        : (!$selectedClassId ? 'Chọn lớp ở bộ lọc phía trên' : 'Tạo phiên điểm danh đầu tiên cho lớp'))">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </x-slot>
                @if(empty(trim($search)) && ($subjectTarget === 'teachers' || $selectedClassId))
                <x-button wire:click="create" variant="primary">
                    <x-icon name="plus" />
                    Tạo phiên đầu tiên
                </x-button>
                @endif
            </x-stats.page-empty>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                tone="slate"
                title="Vui lòng chọn năm học"
                description="Chọn năm học ở bộ lọc phía trên để xem phiên điểm danh">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </x-slot>
            </x-stats.page-empty>
            @endif
        </x-mac-panel>

    </div>

    {{-- ══════════════ MODAL TẠO PHIÊN ══════════════ --}}
    <div
        x-show="showForm"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="session-modal-title"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()">

        <div
            x-show="showForm"
            x-transition
            class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
                w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 px-6 py-5 border-b border-black/[0.06]">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h2 id="session-modal-title" class="text-xl font-semibold tracking-tight text-slate-900">
                            {{ $subjectTarget === 'teachers' ? 'Tạo phiên điểm danh GLV' : 'Tạo phiên điểm danh' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 flex items-center gap-2 flex-wrap">
                            <span>Áp dụng:</span>
                            @if($subjectTarget === 'teachers')
                            <span class="font-semibold text-primary-700">
                                Toàn giáo xứ · {{ $currentNamHoc?->name ?? 'Năm học' }}
                            </span>
                            @elseif($selectedClassId)
                            <span class="font-semibold text-primary-700">
                                Lớp {{ $this->selectedClassName }}
                            </span>
                            @elseif($selectedKhoi)
                            <span class="font-semibold text-primary-700">
                                Khối {{ $this->selectedKhoiName }}
                            </span>
                            @else
                            <span class="font-semibold text-primary-700">
                                Toàn bộ năm học
                            </span>
                            @endif
                            @if($subjectTarget !== 'teachers')
                            <span class="text-xs text-slate-400">
                                ({{ count($this->resolveClassIds()) }} lớp)
                            </span>
                            @endif
                        </p>
                    </div>
                    <button wire:click="closeModal" type="button"
                        class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-black/[0.04] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                @if($errors->any())
                <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại</p>
                            <ul class="text-sm text-red-700 space-y-0.5">
                                @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Loại điểm danh --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-2 tracking-wide uppercase">
                        Loại điểm danh <span class="text-red-500 normal-case">*</span>
                    </label>
                    @if($subjectTarget === 'teachers')
                    <div class="grid grid-cols-3 gap-3">
                        <x-radio-card wire:model="type" :value="1" label="Đi dạy" :checked="$type == 1" />
                        <x-radio-card wire:model="type" :value="2" label="Đi lễ"  :checked="$type == 2" />
                        <x-radio-card wire:model="type" :value="3" label="Họp"    :checked="$type == 3" />
                    </div>
                    @else
                    <div class="grid grid-cols-2 gap-3">
                        <x-radio-card wire:model="type" :value="1" label="Điểm danh đi học" :checked="$type == 1" />
                        <x-radio-card wire:model="type" :value="2" label="Điểm danh đi lễ"  :checked="$type == 2" />
                    </div>
                    @endif
                </div>

                {{-- Chế độ tạo --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-2 tracking-wide uppercase">
                        Chế độ tạo <span class="text-red-500 normal-case">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <x-radio-card wire:model="createMode" value="single" label="Theo ngày"   :checked="$createMode === 'single'" />
                        <x-radio-card wire:model="createMode" value="weekly" label="Theo tuần"   :checked="$createMode === 'weekly'" />
                        <x-radio-card wire:model="createMode" value="custom" label="Tùy chọn"    :checked="$createMode === 'custom'" />
                    </div>
                </div>

                @if($createMode === 'single')
                {{-- Single: một ngày --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Ngày điểm danh <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="date" wire:model.defer="startDate"
                        class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                            text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                </div>
                @endif

                @if($createMode === 'weekly')
                {{-- Weekly: khoảng + ngày trong tuần --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                Từ ngày <span class="text-red-500 normal-case">*</span>
                            </label>
                            <input type="date" wire:model.defer="startDate"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                    text-slate-900 shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                Đến ngày
                            </label>
                            <input type="date" wire:model.defer="endDate"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                    text-slate-900 shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-2 tracking-wide uppercase">
                            Ngày trong tuần <span class="text-red-500 normal-case">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([['0','CN'],['1','T2'],['2','T3'],['3','T4'],['4','T5'],['5','T6'],['6','T7']] as [$val, $lbl])
                            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer transition-all shadow-mac-sm
                                {{ in_array($val, $weekDays)
                                    ? 'border-primary-300/60 bg-primary-50/80'
                                    : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                                <input type="checkbox" wire:model="weekDays" value="{{ $val }}"
                                    class="w-4 h-4 rounded border-black/[0.12] text-primary-600 focus:ring-primary-500/25">
                                <span class="text-sm font-medium
                                    {{ in_array($val, $weekDays) ? 'text-primary-700' : 'text-slate-700' }}">
                                    {{ $lbl }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($createMode === 'custom')
                {{-- Custom: chọn từng ngày --}}
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Chọn ngày cụ thể <span class="text-red-500 normal-case">*</span>
                    </label>
                    <div class="flex items-end gap-2">
                        <div class="flex-1 min-w-0">
                            <input type="date" wire:model="startDate"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                    text-slate-900 shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                        </div>
                        <x-button type="button" variant="outline" wire:click="addSelectedDate">
                            <x-icon name="plus" />
                            Thêm
                        </x-button>
                    </div>

                    @if(count($selectedDates) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($selectedDates as $date)
                        <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-lg text-xs font-semibold
                            bg-primary-50/80 text-primary-700 border border-primary-200/60">
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                            <button type="button" wire:click="removeSelectedDate('{{ $date }}')"
                                class="p-0.5 rounded-md hover:bg-primary-100/80 transition"
                                aria-label="Xóa ngày">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400">Chưa chọn ngày nào — chọn ngày rồi bấm Thêm.</p>
                    @endif
                </div>
                @endif

                {{-- Tiêu đề --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Tiêu đề (không bắt buộc)
                    </label>
                    <input type="text" wire:model.defer="title"
                        placeholder="VD: Tuần lễ Phục sinh, Thánh lễ khai giảng..."
                        class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                            text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                </div>

                {{-- Thời gian --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Giờ bắt đầu
                        </label>
                        <input type="time" wire:model.defer="startTime"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                text-slate-900 shadow-mac-sm
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Giờ kết thúc
                        </label>
                        <input type="time" wire:model.defer="endTime"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                text-slate-900 shadow-mac-sm
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                    </div>
                </div>

                {{-- Info notice --}}
                <div class="bg-primary-50/80 border border-primary-200/60 rounded-xl p-4 shadow-mac-sm">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="text-sm text-primary-700 space-y-1">
                            @if($subjectTarget === 'teachers')
                            <li>• Mỗi buổi áp dụng cho toàn bộ GLV của giáo xứ (không theo lớp)</li>
                            <li>• Buổi đã tồn tại (cùng năm học, loại, ngày) sẽ bị bỏ qua</li>
                            @else
                            <li>• Ngày ngoài HK1/HK2 (kỳ hè, nghỉ giữa kỳ) vẫn tạo được; semester để trống</li>
                            <li>• Phiên đã tồn tại (cùng lớp, loại, ngày) sẽ bị bỏ qua</li>
                            @endif
                            @if($createMode === 'single')
                            <li>• Tạo một phiên cho ngày đã chọn</li>
                            @elseif($createMode === 'weekly')
                            <li>• Tạo nhiều phiên theo các ngày trong tuần trong khoảng đã chọn</li>
                            @else
                            <li>• Tạo phiên cho từng ngày đã thêm vào danh sách</li>
                            @endif
                        </ul>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-black/[0.06] bg-slate-50/70 flex justify-end gap-3">
                <x-button type="button" variant="outline" wire:click="closeModal">
                    Hủy
                </x-button>
                <x-button type="button" variant="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                        <x-icon name="save" />
                        Tạo phiên
                    </span>
                    <span wire:loading wire:target="save">Đang tạo…</span>
                </x-button>
            </div>

        </div>
    </div>

</div>
