@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Phiên điểm danh', 'url' => route('session.index')]
    ]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal',  () => { showForm = true;  });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- ══════ HEADER CARD ══════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <x-page-header
                class="rounded-t-2xl"
                title="Quản lý phiên điểm danh"
                :count="$total">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-col gap-4">

                    {{-- Row 1: Filters --}}
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
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

                    {{-- Row 2: Search + Actions --}}
                    <div class="flex items-center justify-between gap-4">
                        <x-search-input
                            placeholder="Tìm kiếm theo tiêu đề phiên..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md" />

                        <x-tooltip content="Vui lòng chọn năm học trước" :show="!$selectedNamHoc">
                            <x-action-button
                                wire="create"
                                icon="plus"
                                :disabled="!$selectedNamHoc">
                                Tạo phiên mới
                            </x-action-button>
                        </x-tooltip>
                    </div>

                </div>
            </div>

            {{-- Năm học info bar --}}
            @if($currentNamHoc)
            <div class="px-6 py-3 bg-primary-50 border-b border-primary-100 rounded-b-2xl">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-primary-700">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold">{{ $currentNamHoc->name }}</span>
                    @if($currentNamHoc->start_date_one && $currentNamHoc->end_date_one)
                    <span class="text-primary-600">
                        HK1: {{ $currentNamHoc->start_date_one->format('d/m/Y') }}
                        – {{ $currentNamHoc->end_date_one->format('d/m/Y') }}
                    </span>
                    @endif
                    @if($currentNamHoc->start_date_two && $currentNamHoc->end_date_two)
                    <span class="text-primary-600">
                        HK2: {{ $currentNamHoc->start_date_two->format('d/m/Y') }}
                        – {{ $currentNamHoc->end_date_two->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- ══════ TABLE CARD ══════ --}}
        @if($selectedNamHoc)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            @if($sessions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
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
                            <x-table-header>Tiêu đề</x-table-header>
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
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sessions as $index => $session)
                        <tr class="hover:bg-slate-50 transition-colors"
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
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $session['type'] == 1
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'bg-purple-100 text-purple-700' }}">
                                    {{ $session['typeLabel'] }}
                                </span>
                            </td>

                            {{-- Tiêu đề --}}
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $session['title'] ?: '—' }}
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
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold
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
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row
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
            <div class="px-6 pb-6">
                <x-pagination :paginator="$sessions" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            {{-- Empty state khi đã chọn năm học nhưng không có phiên --}}
            <div class="text-center py-16">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                @if(!empty(trim($search)))
                <p class="mt-4 text-lg text-slate-500">Không tìm thấy phiên điểm danh nào</p>
                <p class="mt-1 text-sm text-slate-400">Thử thay đổi từ khóa tìm kiếm</p>
                @elseif(!$selectedClassId)
                <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để xem phiên điểm danh</p>
                @else
                <p class="mt-4 text-lg text-slate-500">Chưa có phiên điểm danh nào</p>
                <button wire:click="create" type="button"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2.5
                           bg-primary-600 text-white text-sm font-semibold
                           rounded-xl hover:bg-primary-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tạo phiên đầu tiên
                </button>
                @endif
            </div>
            @endif
        </div>

        @else
        {{-- Chưa chọn năm học --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn năm học để xem phiên điểm danh</p>
        </div>
        @endif

    </div>{{-- /max-w-7xl --}}

    {{-- ══════════════ MODAL TẠO PHIÊN ══════════════ --}}
    <div
        x-show="showForm"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center
               justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="session-modal-title"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()">

        <div
            x-show="showForm"
            x-transition
            x-data="{ createMode: @entangle('createMode') }"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-xl
                   max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 id="session-modal-title" class="text-xl font-bold text-slate-900">
                            Tạo phiên điểm danh
                        </h2>
                        <div class="mt-1 text-sm text-slate-600 flex items-center gap-2 flex-wrap">
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
                            <span class="text-xs text-slate-500">
                                ({{ count($this->resolveClassIds()) }} lớp)
                            </span>
                        </div>
                    </div>
                    <button wire:click="closeModal" type="button"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                {{-- Error summary --}}
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
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
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Loại điểm danh <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <x-radio-card wire:model="type" :value="1" label="Điểm danh đi học" :checked="$type == 1" />
                        <x-radio-card wire:model="type" :value="2" label="Điểm danh đi lễ"  :checked="$type == 2" />
                    </div>
                </div>

                {{-- Chế độ tạo --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chế độ tạo <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <x-radio-card wire:model="createMode" value="single" label="Theo ngày"   :checked="$createMode === 'single'" />
                        <x-radio-card wire:model="createMode" value="weekly" label="Theo tuần"   :checked="$createMode === 'weekly'" />
                        <x-radio-card wire:model="createMode" value="custom" label="Tùy chọn"    :checked="$createMode === 'custom'" />
                    </div>
                </div>

                {{-- Single: một ngày --}}
                <div x-show="createMode === 'single'" x-transition.opacity>
                    <x-form-input label="Ngày điểm danh" name="startDate" type="date"
                        wire:model.defer="startDate" required />
                </div>

                {{-- Weekly: khoảng + ngày trong tuần --}}
                <div x-show="createMode === 'weekly'" x-transition.opacity class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input label="Từ ngày" name="startDate" type="date"
                            wire:model.defer="startDate" required />
                        <x-form-input label="Đến ngày" name="endDate" type="date"
                            wire:model.defer="endDate" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày trong tuần <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([['0','CN'],['1','T2'],['2','T3'],['3','T4'],['4','T5'],['5','T6'],['6','T7']] as [$val, $lbl])
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors
                                {{ in_array($val, $weekDays)
                                    ? 'border-primary-500 bg-primary-50'
                                    : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="checkbox" wire:model="weekDays" value="{{ $val }}"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm font-medium
                                    {{ in_array($val, $weekDays) ? 'text-primary-700' : 'text-slate-700' }}">
                                    {{ $lbl }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Custom: chọn ngày thủ công --}}
                <div x-show="createMode === 'custom'" x-transition.opacity>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn ngày cụ thể <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                        <input type="date" wire:model.defer="startDate"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="mt-2 text-xs text-slate-500">
                            📌 Chọn nhiều ngày sẽ được bổ sung sau
                        </p>
                    </div>
                </div>

                {{-- Tiêu đề --}}
                <x-form-input
                    label="Tiêu đề (không bắt buộc)"
                    name="title"
                    wire:model.defer="title"
                    placeholder="VD: Tuần lễ Phục sinh, Thánh lễ khai giảng..." />

                {{-- Thời gian --}}
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="Giờ bắt đầu" name="startTime" type="time" wire:model.defer="startTime" />
                    <x-form-input label="Giờ kết thúc" name="endTime"   type="time" wire:model.defer="endTime"   />
                </div>

                {{-- Info notice --}}
                <div class="bg-primary-50 border-l-4 border-primary-500 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="text-sm text-primary-600 space-y-1">
                            <li>• Chỉ tạo phiên trong khoảng thời gian năm học</li>
                            <li>• Phiên đã tồn tại (cùng lớp, loại, ngày) sẽ bị bỏ qua</li>
                            <li>• Chế độ "Theo tuần" có thể tạo nhiều phiên cùng lúc</li>
                        </ul>
                    </div>
                </div>

            </div>{{-- /body --}}

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button wire="closeModal" variant="secondary">Hủy</x-action-button>
                <x-action-button wire="save" icon="save" :loading="true">Tạo phiên</x-action-button>
            </div>

        </div>{{-- /modal inner --}}
    </div>{{-- /modal overlay --}}

</div>{{-- /x-data --}}