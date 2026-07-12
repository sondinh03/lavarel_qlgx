@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Phiên điểm danh', 'url' => route('session.index')]
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal',  () => { showForm = true;  });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý phiên điểm danh"
                description="Danh sách buổi điểm danh theo lớp và năm học"
                icon-type="attendance"
                :count="$total" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
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
                                :selected-lop="$selectedClassId" />
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            placeholder="Tìm kiếm theo ngày (vd: 12/03/2026)..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md" />

                        <x-tooltip content="Vui lòng chọn năm học trước" :show="!$selectedNamHoc">
                            <x-button wire:click="create" :disabled="!$selectedNamHoc">
                                <x-icon name="plus" />
                                Tạo phiên mới
                            </x-button>
                        </x-tooltip>
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

                            {{-- STT --}}
                            <td class="px-4 py-3 text-sm font-semibold text-slate-500">
                                {{ ($sessions->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Ngày --}}
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900 text-sm whitespace-nowrap">
                                    {{ $session['dayName'] }} – {{ $session['fullDate'] }}
                                </span>
                            </td>

                            {{-- Loại --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $session['type'] == 1
                                        ? 'bg-primary-50/80 text-primary-700'
                                        : 'bg-purple-50/80 text-purple-700' }}">
                                    {{ $session['typeLabel'] }}
                                </span>
                            </td>

                            {{-- Thời gian --}}
                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
                                @if($session['start_time'] || $session['end_time'])
                                    {{ $session['start_time'] ?? '--:--' }} – {{ $session['end_time'] ?? '--:--' }}
                                @else
                                    <span class="text-slate-400">Chưa đặt</span>
                                @endif
                            </td>

                            {{-- Stats điểm danh --}}
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

                            {{-- Trạng thái --}}
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

                            {{-- Thao tác --}}
                            <td class="px-4 py-3 overflow-visible">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Điểm danh --}}
                                    <x-tooltip content="Điểm danh">
                                        <a href="{{ route('attendance.show', [
                                                'classId' => $selectedClassId ?? '',
                                                'type'    => $session['type'],
                                                'date'    => $session['dateStr'],
                                            ]) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="clipboard" />
                                        </a>
                                    </x-tooltip>

                                    {{-- Toggle trạng thái --}}
                                    <x-tooltip :content="$session['locked'] ? 'Mở lại phiên' : 'Khóa phiên'">
                                        <button
                                            wire:click="toggleStatus({{ $session['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="p-2 rounded-lg transition-all
                                                {{ $session['locked']
                                                    ? 'hover:bg-green-50 text-green-600'
                                                    : 'hover:bg-amber-50 text-amber-600' }}">
                                            @if($session['locked'])
                                            <x-icon name="check" />
                                            @else
                                            <x-icon name="archive" />
                                            @endif
                                        </button>
                                    </x-tooltip>

                                    {{-- Xóa --}}
                                    <x-tooltip content="Xóa phiên">
                                        <button
                                            wire:click="delete({{ $session['id'] }})"
                                            wire:confirm="Xóa phiên điểm danh ngày {{ $session['fullDate'] }}?"
                                            class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-all">
                                            <x-icon name="trash" />
                                        </button>
                                    </x-tooltip>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend + Pagination --}}
            <div class="px-4 lg:px-6 py-3 mac-hairline-t bg-slate-50/40 flex flex-col sm:flex-row
                        items-start sm:items-center justify-between gap-4">
                {{-- Legend --}}
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

            {{-- Pagination --}}
            @if($sessions->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$sessions" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                tone="primary"
                :title="!empty(trim($search)) ? 'Không tìm thấy phiên điểm danh' : (!$selectedClassId ? 'Vui lòng chọn lớp' : 'Chưa có phiên điểm danh')"
                :description="!empty(trim($search)) ? 'Thử thay đổi từ khóa tìm kiếm' : (!$selectedClassId ? 'Chọn lớp ở bộ lọc phía trên' : 'Tạo phiên điểm danh đầu tiên cho lớp')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </x-slot>
                @if(empty(trim($search)) && $selectedClassId)
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
                            Tạo phiên điểm danh
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 flex items-center gap-2 flex-wrap">
                            <span>Áp dụng:</span>
                            @if($selectedClassId)
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
                            <span class="text-xs text-slate-400">
                                ({{ count($this->resolveClassIds()) }} lớp)
                            </span>
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
                    <div class="grid grid-cols-2 gap-3">
                        <x-radio-card wire:model="type" :value="1" label="Điểm danh đi học" :checked="$type == 1" />
                        <x-radio-card wire:model="type" :value="2" label="Điểm danh đi lễ"  :checked="$type == 2" />
                    </div>
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
                            <li>• Chỉ tạo phiên trong khoảng thời gian năm học</li>
                            <li>• Phiên đã tồn tại (cùng lớp, loại, ngày) sẽ bị bỏ qua</li>
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