<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                ['label' => 'Trang chủ', 'url' => route('home')],
                [
                    'label' => 'Quản lý giáo viên',
                    'url' => route('teacher.show'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' />
                            </svg>',
                ],
            ]"
            separator="arrow" />

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý giáo viên"
                description="Danh sách giáo viên giáo lý"
                :stat-value="$teachers->total()"
                stat-label="Giáo viên"
                icon-type="teacher">
            </x-page-header>

            {{-- Toast Messages --}}
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

            {{-- Filters Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">

                    {{-- LEFT: Search & Filters --}}
                    <div class="flex items-center gap-3">
                        {{-- Search --}}
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm theo tên hoặc SĐT"
                            class="w-64 px-3 py-2 rounded-xl
                                   border border-slate-300
                                   text-sm
                                   focus:ring-2 focus:ring-primary-500
                                   focus:border-transparent" />
                    </div>

                    {{-- RIGHT: Primary Action --}}
                    <button
                        wire:click="create"
                        class="inline-flex items-center gap-2
                               px-5 py-2.5 rounded-xl
                               bg-gradient-to-r from-primary-500 to-primary-600
                               hover:from-primary-600 hover:to-primary-700
                               text-white text-sm font-semibold
                               active:scale-95
                               transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm giáo viên
                    </button>

                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($teachers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Số điện thoại</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($teachers as $i => $teacher)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $teachers->firstItem() + $i }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $teacher->name }}
                                </div>
                                @if($teacher->year)
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Năm {{ $teacher->year }}
                                </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($teacher->birthday)
                                {{ $teacher->birthday->format('d/m/Y') }}
                                <span class="text-xs text-slate-400 ml-1">
                                    ({{ $teacher->birthday->age }} tuổi)
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($teacher->phone_number)
                                <a href="tel:{{ $teacher->phone_number }}"
                                    class="hover:text-primary-600 transition-colors">
                                    {{ $teacher->phone_number }}
                                </a>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs">
                                @if($teacher->parish_child_name )
                                <div class="truncate" title="{{ $teacher->parish_child_name }}">
                                    {{ $teacher->parish_child_name }}
                                </div>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="toggleStatus({{ $teacher->id }})"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-full
                                           transition-all hover:scale-105
                                           {{ $teacher->status ? 'bg-primary-100 text-primary-700 hover:bg-primary-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    {{ $teacher->status ? 'Hoạt động' : 'Tắt' }}
                                </button>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button
                                        wire:click="edit({{ $teacher->id }})"
                                        class="text-primary-600 hover:text-primary-800 font-medium text-sm
                                               transition-colors">
                                        Sửa
                                    </button>

                                    @if($isAdmin)
                                    <button
                                        wire:click="delete({{ $teacher->id }})"
                                        onclick="return confirm('Xác nhận xóa giáo viên {{ $teacher->name }}?')"
                                        class="text-red-600 hover:text-red-800 font-medium text-sm
                                               transition-colors">
                                        Xóa
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($teachers->hasPages())
            <div class="border-t border-slate-200">
                <x-pagination
                    :paginator="$teachers"
                    :per-page-options="$this->getPerPageOptions()" />
            </div>
            @endif
            @else
            <div class="text-center py-16">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có giáo viên nào</p>
                <button
                    wire:click="create"
                    class="mt-4 px-5 py-2.5 bg-primary-600 text-white rounded-xl
                           hover:bg-primary-700 font-semibold
                           active:scale-95 transition-all">
                    <i class="las la-plus mr-1"></i> Thêm giáo viên đầu tiên
                </button>
            </div>
            @endif
        </div>

        {{-- Form Modal --}}
        @if ($showForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="teacher-modal-title"
            wire:click="$set('showForm', false)">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] overflow-y-auto"
                wire:click.stop>
                {{-- Header --}}
                <div class="sticky top-0 z-10 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="teacher-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo viên' : 'Thêm giáo viên mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Nhập thông tin giáo viên giáo lý
                    </p>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
                    {{-- Tên giáo viên --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Họ tên <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: Nguyễn Văn A"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Row: Ngày sinh & Số điện thoại --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Ngày sinh --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Ngày sinh
                            </label>
                            <input
                                type="date"
                                wire:model.defer="birthday"
                                max="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('birthday')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Số điện thoại
                            </label>
                            <input
                                type="tel"
                                wire:model.defer="phoneNumber"
                                placeholder="0123456789"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('phoneNumber')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Ghi chú
                        </label>
                        <textarea
                            wire:model.defer="note"
                            rows="3"
                            placeholder="Thông tin bổ sung về giáo viên..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500
                                   resize-none"></textarea>
                        @error('note')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Optional Fields (có thể ẩn nếu không dùng) --}}
                    <div class="pt-2 border-t border-slate-200">
                        <details class="group">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-600 hover:text-slate-900">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Thông tin bổ sung (tùy chọn)
                                </span>
                            </summary>
                            <div class="mt-4 space-y-4">
                                {{-- Year --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Năm
                                    </label>
                                    <input
                                        type="number"
                                        wire:model.defer="year"
                                        placeholder="2024"
                                        min="1900"
                                        max="2100"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    @error('year')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </details>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="flex items-center gap-3 pt-1">
                        <input
                            id="teacher-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300
                                   text-primary-600 focus:ring-primary-500">
                        <label for="teacher-status" class="text-sm text-slate-700">
                            Hoạt động
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="sticky bottom-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <button
                        wire:click="cancel"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-300
                               text-slate-700 font-semibold hover:bg-slate-100
                               active:scale-95 transition-all">
                        Hủy
                    </button>

                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white
                               font-semibold hover:bg-primary-700
                               active:scale-95 transition-all
                               disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">Lưu giáo viên</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Loading Indicator --}}
<div wire:loading.flex class="fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3 shadow-xl">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700 font-medium">Đang xử lý...</span>
    </div>
</div>