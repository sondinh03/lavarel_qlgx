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
                            placeholder="Tìm kiếm theo tên thánh, họ tên, mã học sinh..."
                            wire-model="search"
                            debounce="500ms"
                            class="max-w-md" />

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 flex-wrap">

                            {{-- Ghi danh (gộp 3 flow) --}}
                            @if($selectedLop)
                            <button type="button"
                                wire:click="openEnrollModal('existing')"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                       bg-gradient-to-r from-primary-500 to-primary-600
                                       text-white text-sm font-semibold rounded-xl
                                       hover:from-primary-600 hover:to-primary-700
                                       active:scale-95 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Ghi danh
                            </button>
                            @endif

                            <p>{{ $selectedLop }}</p>

                            @if($selectedLop)
                            <a href="{{ route('students.import') }}?classId={{ $selectedLop }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                    bg-gradient-to-r from-indigo-500 to-indigo-600
                                    text-white text-sm font-semibold rounded-xl ...">
                                <svg ...> {{-- icon upload --}} </svg>
                                Import Excel
                            </a>
                            @endif

                            {{-- Điểm danh --}}
                            @if($lop)
                            <a href="{{ route('attendance.show', ['classId' => $lop->id]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                       bg-gradient-to-r from-amber-500 to-amber-600
                                       text-white text-sm font-semibold rounded-xl
                                       hover:from-amber-600 hover:to-amber-700
                                       active:scale-95 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Điểm danh
                            </a>
                            @endif

                            <a href="{{ route('students.import') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5
           bg-gradient-to-r from-indigo-500 to-indigo-600
           text-white text-sm font-semibold rounded-xl
           hover:from-indigo-600 hover:to-indigo-700
           active:scale-95 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Import Excel
                            </a>

                            {{-- Đặt lại --}}
                            <button type="button"
                                wire:click="resetFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                       bg-slate-100 text-slate-700 text-sm font-semibold
                                       rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Đặt lại
                            </button>

                            {{-- Export --}}
                            <button type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                       bg-emerald-500 text-white text-sm font-semibold
                                       rounded-xl hover:bg-emerald-600 active:scale-95 transition-all">
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
                            <x-table-header>
                                <input
                                    type="checkbox"
                                    wire:model="selectAll"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </x-table-header>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Mã HS</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header>Họ & Tên đệm</x-table-header>
                            <x-table-header>Tên</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Giới tính</x-table-header>
                            <x-table-header>Bố</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header>Hồ sơ giáo dân</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $index => $student)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="student-{{ $student->id }}">

                            {{-- Checkbox --}}
                            <td class="px-4 py-4">
                                <input
                                    type="checkbox"
                                    wire:model="selectedStudents"
                                    value="{{ $student->id }}"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </td>

                            {{-- STT --}}
                            <td class="px-4 py-4 text-sm font-semibold text-slate-500">
                                {{ ($students->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Mã HS --}}
                            <td class="px-4 py-4 text-sm font-mono font-semibold text-blue-600">
                                {{ $student->student_code ?? '—' }}
                            </td>

                            {{-- Tên thánh --}}
                            <td class="px-4 py-4 text-sm text-slate-900">
                                {{ $student->saint->name ?? '—' }}
                            </td>

                            {{-- Họ & tên đệm --}}
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">
                                {{ $student->last_name }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">
                                {{ $student->first_name }}
                            </td>

                            {{-- Ngày sinh --}}
                            <td class="px-4 py-4 text-sm text-slate-600">
                                {{ $student->birthday?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Giới tính --}}
                            <td class="px-4 py-4 text-sm text-slate-600">
                                {{ $student->gender_text }}
                            </td>

                            {{-- Bố --}}
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ $student->father_name ?? '—' }}
                            </td>

                            {{-- Giáo họ --}}
                            <td class="px-4 py-4">
                                @if($student->parishGroup)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                             bg-amber-100 text-amber-700">
                                    {{ $student->parishGroup->name }}
                                </span>
                                @else
                                <span class="text-slate-400 text-xs">Chưa xác định</span>
                                @endif
                            </td>

                            {{-- Cột Giáo dân --}}
                            <td class="px-6 py-4">
                                @if($student->parishioner_id)
                                {{-- Đã liên kết --}}
                                <a href="{{ route('parishioners.show', $student->parishioner_id) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                   text-xs font-semibold bg-green-100 text-green-700
                   hover:bg-green-200 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Xem hồ sơ
                                </a>
                                @else
                                {{-- Chưa liên kết --}}
                                <button wire:click="openLinkParishioner({{ $student->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                   text-xs font-semibold bg-amber-50 text-amber-700
                   hover:bg-amber-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.828 14.828a4 4 0 015.656 0l4-4a4 4 0 01-5.656-5.656l-1.102 1.101" />
                                    </svg>
                                    Chưa liên kết
                                </button>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Xem chi tiết --}}
                                    <a href="{{ route('students.show', $student->id) }}"
                                        class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg
                                               active:scale-95 transition-all"
                                        title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Chỉnh sửa --}}
                                    <a href="{{ route('students.edit', $student->id) }}"
                                        class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg
                                               active:scale-95 transition-all"
                                        title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <x-table-action
                                        wire="delete({{ $student->id }})"
                                        icon="trash"
                                        color="danger"
                                        confirm="Bạn có chắc chắn muốn xóa học sinh {{ $student->name }} khỏi lớp?">
                                        Xóa
                                    </x-table-action>

                                    {{-- More actions --}}
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            type="button"
                                            class="p-2 hover:bg-slate-100 rounded-lg active:scale-95 transition-all"
                                            title="Thêm tùy chọn">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>



                                        <div x-show="open"
                                            @click.outside="open = false"
                                            x-transition
                                            x-cloak
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl
                                                   border border-slate-200 shadow-lg overflow-hidden z-20">
                                            <button type="button"
                                                @click="if(confirm('Bạn chắc chắn muốn xóa học sinh này?')) $wire.delete({{ $student->id }}); open = false"
                                                class="w-full px-4 py-3 text-left hover:bg-red-50 transition-colors
                                                    flex items-center gap-3">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-sm font-medium text-red-600">Xóa học sinh</span>
                                            </button>
                                            <x-table-action
                                                wire="delete({{ $student->id }})"
                                                icon="trash"
                                                color="danger"
                                                confirm="Bạn có chắc chắn muốn xóa học sinh {{ $student->name }} khỏi lớp?">
                                                Xóa
                                            </x-table-action>
                                        </div>
                                    </div>

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
                <div class="flex items-center gap-2">
                    <button type="button"
                        wire:click="$set('selectedStudents', [])"
                        class="px-3 py-1.5 text-sm font-medium text-primary-600
                               hover:bg-primary-100 rounded-lg transition">
                        Bỏ chọn tất cả
                    </button>
                </div>
            </div>
            @endif

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
                :colspan="10"
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


        @if($showLinkModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true" wire:click="closeLinkModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[80vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-amber-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">Liên kết hồ sơ giáo dân</h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Tìm thấy {{ $suggestedParishioners->count() }} giáo dân có thể trùng khớp
                    </p>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-6">
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
                                    class="flex-shrink-0 px-4 py-2 bg-primary-600 text-white text-sm
                                   font-semibold rounded-xl hover:bg-primary-700 transition-colors">
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
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                    <x-action-button wire="skipLink" variant="secondary">
                        Bỏ qua
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

        {{-- ===================== MODAL GHI DANH — 3 TABS ===================== --}}
        @if($showEnrollNewModal)
        <div
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="enroll-modal-title"
            wire:click="closeEnrollModal">
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh]
                       overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- ── HEADER (cố định) ── --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 id="enroll-modal-title" class="text-xl font-bold text-slate-900">
                                Ghi danh học sinh
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                @if($lop) Lớp: <strong>{{ $lop->name }}</strong> @endif
                            </p>
                        </div>
                        <button wire:click="closeEnrollModal" type="button"
                            class="text-slate-400 hover:text-slate-600 transition p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Tab bar --}}
                    <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">

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
                        <button type="button"
                            wire:click="switchEnrollTab('new')"
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
                        </button>

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

                {{-- ── BODY (cuộn) ── --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- ════════ TAB 1: Học sinh có sẵn ════════ --}}
                    @if($enrollTab === 'existing')
                    <div class="p-6 space-y-4">

                        {{-- Search + năm sinh --}}
                        <div class="flex items-center gap-2">
                            <input
                                wire:model.debounce.300ms="modalSearch"
                                type="text"
                                placeholder="Tìm kiếm học sinh..."
                                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">

                            <select
                                wire:model="birthYear"
                                class="w-44 px-3 py-2.5 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
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
                                        <td class="px-4 py-3 text-sm font-mono text-blue-600">
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

                    {{-- ════════ TAB 2: Tạo mới ════════ --}}
                    @if($enrollTab === 'new')
                    <div class="p-6">

                        {{-- Error summary --}}
                        @if ($errors->any())
                        <div class="mb-5 bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại thông tin</p>
                                    <ul class="text-sm text-red-700 space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                            {{-- Họ & tên đệm --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Họ & tên đệm <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="enrollLastName" type="text"
                                    placeholder="Nguyễn Văn"
                                    class="w-full px-3 py-2.5 rounded-xl border text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           {{ $errors->has('enrollLastName') ? 'border-red-400 bg-red-50' : 'border-slate-300' }}">
                                @error('enrollLastName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tên --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Tên <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="enrollFirstName" type="text"
                                    placeholder="An"
                                    class="w-full px-3 py-2.5 rounded-xl border text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           {{ $errors->has('enrollFirstName') ? 'border-red-400 bg-red-50' : 'border-slate-300' }}">
                                @error('enrollFirstName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tên thánh --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tên thánh</label>
                                <select wire:model.defer="enrollSaintId"
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">-- Chọn tên thánh --</option>
                                    @foreach($availableSaints as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('enrollSaintId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Giới tính --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Giới tính <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-6 mt-2.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.defer="enrollGender" value="male"
                                            class="w-4 h-4 text-primary-600 border-slate-300 focus:ring-primary-500">
                                        <span class="text-sm text-slate-700">Nam</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.defer="enrollGender" value="female"
                                            class="w-4 h-4 text-primary-600 border-slate-300 focus:ring-primary-500">
                                        <span class="text-sm text-slate-700">Nữ</span>
                                    </label>
                                </div>
                                @error('enrollGender')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Ngày sinh --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Ngày sinh <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="enrollBirthday" type="date"
                                    class="w-full px-3 py-2.5 rounded-xl border text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           {{ $errors->has('enrollBirthday') ? 'border-red-400 bg-red-50' : 'border-slate-300' }}">
                                @error('enrollBirthday')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Giáo họ --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
                                <select wire:model.defer="enrollParishGroup"
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">-- Chọn giáo họ --</option>
                                    @foreach($availableParishGroups as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('enrollParishGroup')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Họ tên cha --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Họ tên cha</label>
                                <input wire:model.defer="enrollFatherName" type="text"
                                    placeholder="Họ và tên cha"
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            {{-- Họ tên mẹ --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Họ tên mẹ</label>
                                <input wire:model.defer="enrollMotherName" type="text"
                                    placeholder="Họ và tên mẹ"
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                        </div>
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
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-purple-500">

                            <div class="flex items-center gap-2 flex-wrap">
                                <select wire:model="parishionerBirthYear"
                                    class="flex-1 min-w-[140px] px-3 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
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
                                           focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">

                                <input type="number" wire:model.lazy="ageTo"
                                    placeholder="đến"
                                    class="w-28 px-3 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">

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
                                                class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Họ tên</th>
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
                                                class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                            {{ $p->last_name }} {{ $p->name }}
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
                                                         {{ $p->sex == 'male' || $p->sex == 1 ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                                {{ $p->sex == 'male' || $p->sex == 1 ? 'Nam' : 'Nữ' }}
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

                </div>{{-- /body --}}

                {{-- ── FOOTER (cố định, thay đổi theo tab) ── --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50">

                    {{-- Footer tab 1: Học sinh có sẵn --}}
                    @if($enrollTab === 'existing')
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">
                            Đã chọn: <span class="font-semibold text-primary-600">{{ count($studentsToAdd) }}</span> học sinh
                        </span>
                        <div class="flex gap-3">
                            <button wire:click="closeEnrollModal" type="button"
                                class="px-4 py-2.5 bg-slate-100 text-slate-900 text-sm font-semibold
                                       rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                                Hủy
                            </button>
                            <button wire:click="addStudentsToClass" type="button"
                                @disabled(empty($studentsToAdd))
                                class="px-4 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white
                                       text-sm font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700
                                       active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed
                                       inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Thêm vào lớp
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Footer tab 2: Tạo mới --}}
                    @if($enrollTab === 'new')
                    <div class="flex items-center justify-end gap-3">
                        <button wire:click="closeEnrollModal" type="button"
                            class="px-4 py-2.5 bg-slate-100 text-slate-900 text-sm font-semibold
                                   rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                            Hủy
                        </button>
                        <button wire:click="enrollNewStudent" type="button"
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white
                                   text-sm font-semibold rounded-xl hover:from-green-600 hover:to-green-700
                                   active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed
                                   inline-flex items-center gap-2">
                            <svg wire:loading wire:target="enrollNewStudent"
                                class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg wire:loading.remove wire:target="enrollNewStudent"
                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Ghi danh
                        </button>
                    </div>
                    @endif

                    {{-- Footer tab 3: Import giáo dân --}}
                    @if($enrollTab === 'parishioner')
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">
                            Đã chọn: <span class="font-semibold text-purple-600">{{ count($selectedParishioners) }}</span> giáo dân
                        </span>
                        <div class="flex gap-3">
                            <button wire:click="closeEnrollModal" type="button"
                                class="px-4 py-2.5 bg-slate-100 text-slate-900 text-sm font-semibold
                                       rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                                Hủy
                            </button>
                            <button wire:click="importParishionersToStudents" type="button"
                                @disabled(empty($selectedParishioners))
                                wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white
                                       text-sm font-semibold rounded-xl hover:from-purple-600 hover:to-purple-700
                                       active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed
                                       inline-flex items-center gap-2">
                                <svg wire:loading wire:target="importParishionersToStudents"
                                    class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg wire:loading.remove wire:target="importParishionersToStudents"
                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Import {{ count($selectedParishioners) > 0 ? count($selectedParishioners) : '' }} học sinh
                            </button>
                        </div>
                    </div>
                    @endif

                </div>{{-- /footer --}}
            </div>{{-- /modal inner --}}
        </div>{{-- /modal overlay --}}
        @endif
        {{-- ══════════════════════════════════════════════════════════════ --}}

    </div>
</div>

{{-- Loading Indicator --}}
<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[60]">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush