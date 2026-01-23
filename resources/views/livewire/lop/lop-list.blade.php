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
                    'label' => 'Quản lý lớp học',
                    'url' => route('classes.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\' />
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
                title="Quản lý lớp học"
                description="Danh sách các lớp học theo năm học và khối"
                :stat-value="$lops?->total()"
                stat-label="Lớp học"
                icon-type="class">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">
                    {{-- LEFT: Filters --}}
                    <div class="flex items-center gap-3">
                        {{-- Filter Bar --}}
                        <livewire:filters.filter-bar
                            :parish-id="$parishId"
                            :show-nam-hoc="true"
                            :show-khoi="true"
                            :show-lop="false"
                            :show-ky="false"
                            :selected-nam-hoc="$selectedNamHoc"
                            :selected-khoi="$selectedKhoi" />

                        {{-- Search --}}
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm kiếm lớp..."
                            class="w-56 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    {{-- RIGHT: Primary Action --}}
                    <x-action-button
                        wire="create"
                        icon="plus"
                        :disabled="!$selectedNamHoc">
                        Thêm lớp
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        @if($selectedNamHoc)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($lops && $lops->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Mã lớp</x-table-header>
                            <x-table-header>Tên lớp</x-table-header>
                            <x-table-header>Khối</x-table-header>
                            <x-table-header class="text-center">Sĩ số</x-table-header>
                            <x-table-header>Giáo lý viên</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($lops as $index => $lop)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="lop-{{ $lop->id }}">
                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($lops->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Mã lớp --}}
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-semibold text-purple-600">
                                    {{ $lop->symbol ?? '-' }}
                                </span>
                            </td>

                            {{-- Tên lớp --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $lop->name }}
                                </div>
                            </td>

                            {{-- Khối --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full 
                                             text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    {{ $lop->blockRelation->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Sĩ số --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="font-semibold">{{ $lop->active_students_count }}</span>
                                </div>
                            </td>

                            {{-- Giáo lý viên --}}
                            <td class="px-6 py-4">
                                @if($lop->has_teacher)
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                        class="flex items-center gap-2 text-sm font-medium text-slate-900 
                                               hover:text-purple-600 transition-colors">
                                        <span class="max-w-32 truncate">
                                            {{ $lop->teacher_names[0] ?? 'GLV' }}
                                        </span>
                                        @if(($lop->teacher_count ?? 0) > 1)
                                        <span class="inline-flex items-center justify-center w-5 h-5 
                                                     text-xs font-semibold text-purple-700 bg-purple-100 rounded-full">
                                            +{{ $lop->teacher_count - 1 }}
                                        </span>
                                        @endif
                                    </button>

                                    {{-- Tooltip với danh sách đầy đủ --}}
                                    <div x-show="open"
                                        x-transition
                                        x-cloak
                                        class="absolute left-0 top-full mt-2 w-auto min-w-48 max-w-xs 
                                                p-3 bg-white rounded-xl shadow-xl border border-slate-200 z-20">
                                        <div class="space-y-2">
                                            @foreach($lop->teacher_names ?? [] as $teacherName)
                                            <div class="flex items-center gap-2 text-sm">
                                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span class="text-slate-700 font-medium">{{ $teacherName }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full 
                                             text-xs font-medium bg-amber-50 text-amber-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Chưa có GLV
                                </span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                             {{ $lop->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $lop->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">
                                    {{-- Xem học sinh --}}
                                    <a href="{{ route('students.index', ['class' => $lop->id]) }}"
                                        class="text-green-600 hover:text-green-700 font-semibold text-sm inline-flex items-center gap-1"
                                        title="Danh sách học sinh">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Học sinh
                                    </a>

                                    <span class="text-slate-300">|</span>

                                    {{-- Sửa --}}
                                    <x-table-action
                                        wire="edit({{ $lop->id }})"
                                        icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <a href="{{ route('classes.catechists', ['lopId' => $lop->id]) }}"
                                        class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 
                                            font-semibold text-sm transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <!-- GLV -->
                                        ({{ $lop->teacher_count ?? 0 }})
                                    </a>

                                    <span class="text-slate-300">|</span>

                                    {{-- Toggle Status --}}
                                    <x-table-action
                                        wire="toggleStatus({{ $lop->id }})"
                                        :icon="$lop->status ? 'archive' : 'check'"
                                        :color="$lop->status ? 'warning' : 'success'"
                                        :loading="true"
                                        debounce="500">
                                        {{ $lop->status ? 'Tắt' : 'Bật' }}
                                    </x-table-action>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($lops->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination
                    :paginator="$lops"
                    :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @else
            <div class="text-center py-12">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có lớp học nào</p>
                <button wire:click="create"
                    @disabled(!$selectedNamHoc)
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700
                        disabled:bg-slate-300 disabled:cursor-not-allowed transition-all">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Thêm lớp học đầu tiên
                </button>
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn năm học để xem danh sách lớp</p>
        </div>
        @endif

        {{-- Form Modal --}}
        @if ($showForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="lop-modal-title"
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="lop-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật lớp học' : 'Thêm lớp học mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Thông tin cơ bản về lớp học
                    </p>
                </div>

                {{-- Body - SCROLLABLE --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
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

                    {{-- Mã lớp --}}
                    <x-form-input
                        label="Mã lớp"
                        name="symbol"
                        wire:model.defer="symbol"
                        placeholder="VD: L01, L02, A1..."
                        required
                        help-text="Mã định danh ngắn gọn của lớp" />

                    {{-- Tên lớp --}}
                    <x-form-input
                        label="Tên lớp"
                        name="name"
                        wire:model.defer="name"
                        placeholder="VD: Lớp 1A, Lớp Thiếu Nhi A..."
                        required />

                    {{-- Khối học --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Khối học <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.defer="block"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500
                                   {{ $availableBlocks->isEmpty() ? 'bg-slate-100 cursor-not-allowed' : '' }}">
                            <option value="">-- Chọn khối --</option>
                            @forelse($availableBlocks as $blk)
                            <option value="{{ $blk->id }}">{{ $blk->name }}</option>
                            @empty
                            <option value="" disabled>Chưa có khối học nào</option>
                            @endforelse
                        </select>

                        @if($availableBlocks->isEmpty())
                        <p class="mt-1 text-sm text-amber-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Vui lòng tạo khối học trước
                        </p>
                        @endif

                        @error('block')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <input
                                id="lop-status"
                                type="checkbox"
                                wire:model.defer="status"
                                class="mt-0.5 w-4 h-4 rounded border-slate-300
                                       text-primary-600 focus:ring-primary-500">
                            <div class="flex-1">
                                <label for="lop-status" class="text-sm font-semibold text-slate-900 cursor-pointer">
                                    Kích hoạt lớp học
                                </label>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Lớp đang hoạt động và có thể nhận học sinh
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- INFO: Phân công GLV sẽ làm sau --}}
                    @if($editingId)
                    <div class="bg-primary-50 border-l-4 border-primary-500 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-primary-700">
                                    Phân công Giáo lý viên
                                </h4>
                                <p class="text-sm text-primary-600 mt-1">
                                    Sau khi lưu, bạn có thể phân công GLV trong trang chi tiết lớp
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">
                        Hủy
                    </x-action-button>

                    <x-action-button
                        wire="save"
                        icon="save"
                        :loading="true"
                        :disabled="$availableBlocks->isEmpty()">
                        {{ $editingId ? 'Cập nhật' : 'Tạo lớp' }}
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