@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Phiên điểm danh', 'url' => route('session.index')]
    ]" />
@endsection

<div class="min-h-screen bg-slate-50 p-6"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => {
                showForm = true;
            });
            Livewire.on('closeModal', () => {
                showForm = false;
            });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    {{-- Main Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Header --}}
        <x-page-header
            title="Quản lý phiên điểm danh - {{ $this->selectedClassName }}"
            description="Tạo và quản lý các phiên điểm danh cho lớp học"
            :stat-value="$sessions->count()"
            stat-label="Phiên điểm danh"
            icon-type="calendar">
        </x-page-header>

        {{-- Actions Bar --}}
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                {{-- LEFT: Filters --}}
                <div class="flex items-center gap-3 flex-1 w-full lg:w-auto">
                    <livewire:filters.filter-bar
                        :parish-id="$parishId"
                        :show-nam-hoc="true"
                        :show-khoi="true"
                        :show-lop="true"
                        :show-ky="false"
                        :selected-nam-hoc="$selectedNamHoc"
                        :selected-khoi="$selectedKhoi"
                        :selected-lop="$selectedClassId" />

                    <input
                        wire:model.live.debounce.500ms="search"
                        placeholder="Tìm phiên..."
                        class="w-56 px-3 py-2 rounded-xl border border-slate-300
                                text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>

                {{-- RIGHT: Actions --}}
                <div class="flex items-center gap-3">
                    <x-action-button
                        wire="create"
                        icon="plus">
                        Tạo phiên mới
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Năm học info --}}
        @if($currentNamHoc)
        <div class="px-6 py-3 bg-blue-50 border-b border-blue-200">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ $currentNamHoc->name }}</span>
                @if($currentNamHoc->start_date_one && $currentNamHoc->end_date_one)
                <span class="text-blue-600">
                    • HK1: {{ $currentNamHoc->start_date_one->format('d/m/Y') }} - {{ $currentNamHoc->end_date_one->format('d/m/Y') }}
                </span>
                @endif
                @if($currentNamHoc->start_date_two && $currentNamHoc->end_date_two)
                <span class="text-blue-600">
                    • HK2: {{ $currentNamHoc->start_date_two->format('d/m/Y') }} - {{ $currentNamHoc->end_date_two->format('d/m/Y') }}
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Sessions Table --}}
    @if($selectedClassId)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($sessions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <x-table-header>STT</x-table-header>
                        <x-table-header>Ngày</x-table-header>
                        <x-table-header>Loại</x-table-header>
                        <x-table-header>Tiêu đề</x-table-header>
                        <x-table-header>Thời gian</x-table-header>
                        <x-table-header class="text-center">Điểm danh</x-table-header>
                        <x-table-header class="text-center">Trạng thái</x-table-header>
                        <x-table-header class="text-center">Thao tác</x-table-header>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($sessions as $index => $session)
                    <tr class="hover:bg-slate-50 transition-colors" wire:key="session-{{ $session['id'] }}">
                        {{-- STT --}}
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $index + 1 }}
                        </td>

                        {{-- Ngày --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900">
                                    {{ $session['dayName'] }} - {{ $session['fullDate'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Loại --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $session['type'] == 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $session['typeLabel'] }}
                            </span>
                        </td>

                        {{-- Tiêu đề --}}
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-700">{{ $session['title'] ?: '-' }}</span>
                        </td>

                        {{-- Thời gian --}}
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($session['start_time'] || $session['end_time'])
                            {{ $session['start_time'] ?? '--:--' }} - {{ $session['end_time'] ?? '--:--' }}
                            @else
                            <span class="text-slate-400">Chưa đặt</span>
                            @endif
                        </td>

                        {{-- Điểm danh stats --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3 text-xs">
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-green-700 font-medium">{{ $session['stats']['present'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                                    <span class="text-yellow-700 font-medium">{{ $session['stats']['absent_excused'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span class="text-red-700 font-medium">{{ $session['stats']['absent_unexcused'] }}</span>
                                </div>
                            </div>
                            @if($session['stats']['total'] > 0)
                            <div class="text-center mt-1">
                                <span class="text-xs text-slate-500">
                                    {{ number_format($session['stats']['present_rate'], 1) }}% có mặt
                                </span>
                            </div>
                            @endif
                        </td>

                        {{-- Trạng thái --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $session['statusClass'] }}">
                                @if($session['locked'])
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                @endif
                                {{ $session['statusLabel'] }}
                            </span>
                        </td>

                        {{-- Thao tác --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3">
                                {{-- Điểm danh --}}
                                <a href="{{ route('attendance.show', ['classId' => $selectedClassId, 'type' => $session['type'], 'date' => $session['dateStr']]) }}"
                                    class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700
                                               font-semibold text-sm transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    Điểm danh
                                </a>

                                <span class="text-slate-300">|</span>

                                {{-- Toggle Status --}}
                                <x-table-action
                                    wire="toggleStatus({{ $session['id'] }})"
                                    :icon="$session['locked'] ? 'check' : 'archive'"
                                    :color="$session['locked'] ? 'success' : 'warning'"
                                    :loading="true">
                                    {{ $session['locked'] ? 'Mở' : 'Khóa' }}
                                </x-table-action>

                                <span class="text-slate-300">|</span>

                                {{-- Delete --}}
                                <x-table-action
                                    wire="delete({{ $session['id'] }})"
                                    icon="trash"
                                    color="danger"
                                    :loading="true"
                                    confirm="Xóa phiên này?">
                                    Xóa
                                </x-table-action>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span>Có mặt</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                    <span>Vắng có phép</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span>Vắng không phép</span>
                </div>
            </div>
        </div>
        @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Chưa có phiên điểm danh nào</p>
            <button
                wire:click="create"
                class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700
                        transition-all flex items-center gap-2 mx-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tạo phiên đầu tiên
            </button>
        </div>
        @endif
    </div>
    @else
    {{-- No Class Selected --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để quản lý phiên điểm danh</p>
    </div>
    @endif

    {{-- Form Modal --}}
    <div
        x-show="showForm"
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()"
        @keydown.enter.window="console.log('[Enter] showForm=', showForm, 'target=', $event.target.tagName); if(showForm) $wire.save()">

        <div
            x-show="showForm"
            x-transition
            x-data="{ createMode: '{{ $createMode }}' }"
            class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
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
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body - SCROLLABLE --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                {{-- Error Summary --}}
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">Vui lòng kiểm tra lại thông tin</h4>
                            <ul class="space-y-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
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
                        <x-radio-card
                            wire:model="type"
                            :value="1"
                            label="Điểm danh đi học"
                            :checked="$type == 1" />

                        <x-radio-card
                            wire:model="type"
                            :value="2"
                            label="Điểm danh đi lễ"
                            :checked="$type == 2" />
                    </div>
                </div>

                {{-- Chế độ tạo --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chế độ tạo <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3"
                        x-on:click="
                            createMode = $event.target.closest('[value]')?.getAttribute('value') ?? createMode;
                            console.log('[createMode changed]', createMode);
                        ">
                        <x-radio-card
                            wire:model="createMode"
                            value="single"
                            label="Theo ngày"
                            :checked="$createMode == 'single'" />

                        <x-radio-card
                            wire:model="createMode"
                            value="weekly"
                            label="Theo tuần"
                            :checked="$createMode == 'weekly'" />

                        <x-radio-card
                            wire:model="createMode"
                            value="custom"
                            label="Tùy chọn" 
                            :checked="$createMode == 'custom'" />

                    </div>
                </div>

                {{-- Single Mode --}}
                <div x-show="createMode === 'single'">
                    <x-form-input label="Ngày điểm danh" name="startDate" type="date"
                        wire:model.defer="startDate" required />
                </div>

                {{-- Weekly Mode --}}
                <div x-show="createMode === 'weekly'" x-transition.opacity class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input label="Từ ngày" name="startDate" type="date"
                            wire:model.defer="startDate" required />
                        <x-form-input label="Đến ngày" name="endDate" type="date"
                            wire:model.defer="endDate" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Chọn các ngày trong tuần <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([['0','CN'],['1','T2'],['2','T3'],['3','T4'],['4','T5'],['5','T6'],['6','T7']] as [$val, $label])
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer
                                    {{ in_array($val, $weekDays) ? 'border-primary-500 bg-primary-50' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="checkbox" wire:model="weekDays" value="{{ $val }}"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm font-medium {{ in_array($val, $weekDays) ? 'text-primary-700' : 'text-slate-700' }}">
                                    {{ $label }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Custom Mode --}}
                <div x-show="createMode === 'custom'">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn các ngày cụ thể <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-slate-300 rounded-xl p-4 bg-slate-50">
                        <input type="date" wire:model.defer="startDate"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300
                                    focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="mt-2 text-xs text-slate-500">
                            📌 Tính năng chọn nhiều ngày sẽ được bổ sung sau
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
                    <x-form-input label="Giờ kết thúc" name="endTime" type="time" wire:model.defer="endTime" />
                </div>

                {{-- Info notice --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="text-sm text-blue-600 space-y-1">
                            <li>• Chỉ tạo phiên trong khoảng thời gian năm học</li>
                            <li>• Phiên đã tồn tại sẽ bị bỏ qua</li>
                            <li>• Có thể tạo nhiều phiên cùng lúc ở chế độ "Theo tuần"</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button wire="closeModal" variant="secondary">Hủy</x-action-button>
                <x-action-button wire="save" icon="save" :loading="true">Tạo phiên</x-action-button>
            </div>
        </div>
    </div>
</div>