<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ', 
                'url' => route('dashboard')
            ],
            [
                'label' => 'Danh sách học sinh',
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                            <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' 
                            d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' />
                        </svg>'
            ]
        ]" separator="arrow" />

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
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Danh sách học sinh"
                :description="$lop ? 'Lớp: ' . $lop->name : 'Tất cả học sinh trong giáo xứ'"
                :stat-value="$total"
                stat-label="Học sinh"
                icon-type="students">
                {{-- Gender Stats --}}
                <div class="flex items-center gap-4 mt-2 text-sm">
                    @if($countnam > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-slate-600">Nam: <span class="font-semibold text-blue-600">{{ $countnam }}</span></span>
                    </div>
                    @endif

                    @if($countnu > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 bg-pink-500 rounded-full"></div>
                        <span class="text-slate-600">Nữ: <span class="font-semibold text-pink-600">{{ $countnu }}</span></span>
                    </div>
                    @endif
                </div>
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-col gap-4">
                    {{-- TOP ROW: FilterBar --}}
                    <div>
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

                    {{-- BOTTOM ROW: Search + Actions --}}
                    <div class="flex items-center justify-between gap-4">
                        {{-- Search --}}
                        <x-search-input
                            placeholder="Tìm kiếm theo tên thánh, họ tên, hoặc mã học sinh..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md" />

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            {{-- Thêm học sinh vào lớp --}}
                            @if($selectedLop)
                            <button type="button"
                                wire:click="openAddStudentsModal"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 
                                       text-white text-sm font-semibold rounded-xl hover:bg-green-700 
                                       active:scale-95 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Thêm học sinh
                            </button>
                            @endif

                            {{-- Điểm danh --}}
                            @if($lop)
                            <a href="{{ route('attendance.show', ['classId' => $lop->id]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 
                                       text-white text-sm font-semibold rounded-xl hover:bg-primary-700 
                                       active:scale-95 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Điểm danh
                            </a>
                            @endif

                            <button type="button"
                                wire:click="resetFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-900 
                                           text-sm font-semibold rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Đặt lại
                            </button>

                            <button type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-500 text-white 
                                           text-sm font-semibold rounded-xl hover:bg-green-600 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Student Table --}}
        @if($selectedNamHoc)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($students && $students->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Mã HS</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header>Họ & Tên đệm</x-table-header>
                            <x-table-header>Tên</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Bố</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $hocsinh)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="student-{{ $hocsinh->id }}">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                {{ $hocsinh->stt }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-blue-600 font-semibold">
                                {{ $hocsinh->mahv }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                {{ $hocsinh->holy_name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                {{ $hocsinh->last_name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                {{ $hocsinh->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $hocsinh->birthday }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $hocsinh->father ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ ($hocsinh->paid ?? '') === 'Nhà xứ' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $hocsinh->paid ?? 'Chưa xác định' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('students.show', $hocsinh->id) }}"
                                        class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg active:scale-95 transition-all"
                                        title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('students.edit', $hocsinh->id) }}"
                                        class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg active:scale-95 transition-all"
                                        title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            type="button"
                                            class="p-2 hover:bg-slate-100 rounded-lg active:scale-95 transition-all"
                                            title="Thêm">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>

                                        <div x-show="open"
                                            @click.outside="open = false"
                                            x-transition
                                            x-cloak
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden z-20">
                                            <a href="{{ $hocsinh->thugioithieu ?? '#' }}"
                                                class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">Thư giới thiệu</span>
                                            </a>

                                            <button type="button"
                                                class="w-full px-4 py-3 text-left hover:bg-red-50 transition-colors flex items-center gap-3">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-sm font-medium text-red-600">Xóa học sinh</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination
                    :paginator="$students"
                    :per-page-options="[10, 15, 25, 50, 100]" />
            </div>
            @endif
            @else
            <x-empty-state
                icon="students"
                :colspan="9"
                title="Không tìm thấy học sinh"
                description="Không có học sinh nào phù hợp với bộ lọc của bạn" />
            @endif
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn năm học để xem danh sách học sinh</p>
        </div>
        @endif

        {{-- Modal Thêm Học Sinh --}}
        @if ($showAddStudentsModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            wire:click="closeAddStudentsModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-green-50 to-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">
                                Thêm học sinh vào lớp
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                @if($lop)
                                {{ $lop->name }} - {{ $lop->block }}
                                @endif
                            </p>
                        </div>
                        <button
                            wire:click="closeAddStudentsModal"
                            type="button"
                            class="p-2 hover:bg-slate-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Search trong modal --}}
                    <div class="p-6 space-y-5">
                        <input
                            wire:model.debounce.300ms="modalSearch"
                            type="text"
                            placeholder="Tìm kiếm học sinh..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-green-500">

                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-slate-700">
                                Lọc theo năm sinh
                            </label>

                            <div class="flex items-center gap-2">
                                <!-- Quick select -->
                                <select
                                    wire:model="birthYear"
                                    class="flex-1 px-3 py-2 rounded-xl border border-slate-300
                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">-- Tất cả năm sinh --</option>
                                    @foreach($this->getQuickYearOptions() as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>

                                <!-- Clear button -->
                                @if($birthYear)
                                <button
                                    wire:click="clearBirthYearFilters"
                                    type="button"
                                    class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 
                   hover:bg-slate-100 rounded-xl transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                @endif
                            </div>

                            @error('birthYear')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Body - SCROLLABLE --}}
                <div class="flex-1 overflow-y-auto p-6">
                    @if($availableStudents && $availableStudents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-0">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            wire:model="selectAllInModal"
                                            class="w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Mã HS</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Tên thánh</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Họ tên</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Ngày sinh</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Giáo họ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($availableStudents as $student)
                                <tr class="hover:bg-slate-50" wire:key="available-{{ $student->id }}">
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            wire:model="studentsToAdd"
                                            value="{{ $student->id }}"
                                            class="w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono text-blue-600">
                                        {{ $student->mahv }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-900">
                                        {{ $student->holy_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                        {{ $student->last_name }} {{ $student->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $student->birthday }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $student->parish_children_name ?? 'Chưa xác định' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination cho modal --}}
                    @if($availableStudents->hasPages())
                    <div class="mt-4">
                        {{ $availableStudents->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p class="mt-4 text-lg text-slate-500">Không có học sinh nào để thêm</p>
                        <p class="mt-1 text-sm text-slate-400">Tất cả học sinh đã có trong lớp này</p>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            Đã chọn: <span class="font-semibold text-green-600">{{ count($studentsToAdd) }}</span> học sinh
                        </div>

                        <div class="flex gap-3">
                            <button
                                wire:click="closeAddStudentsModal"
                                type="button"
                                class="px-4 py-2.5 bg-slate-100 text-slate-900 text-sm font-semibold rounded-xl
                                       hover:bg-slate-200 active:scale-95 transition-all">
                                Hủy
                            </button>

                            <button
                                wire:click="addStudentsToClass"
                                type="button"
                                @disabled(empty($studentsToAdd))
                                class="px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white
                                       text-sm font-semibold rounded-xl hover:bg-green-700 active:scale-95 transition-all
                                       disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Thêm học sinh
                            </button>
                        </div>
                    </div>
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

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush