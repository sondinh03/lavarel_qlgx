<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                ['label' => 'Trang chủ', 'url' => route('home')],
                [
                    'label' => 'Quản lý giáo viên',
                    'url' => route('catechists.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' />
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
                title="Quản lý giáo viên"
                description="Danh sách giáo viên giáo lý"
                :stat-value="$teachers?->total()"
                stat-label="Giáo viên"
                icon-type="teacher">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">

                    {{-- LEFT: Search --}}
                    <div class="flex items-center gap-3">
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm theo tên hoặc SĐT"
                            class="w-64 px-3 py-2 rounded-xl
                                   border border-slate-300
                                   text-sm focus:outline-none
                                   focus:ring-2 focus:ring-primary-500" />
                    </div>

                    {{-- RIGHT: Primary Action --}}
                    <x-action-button wire="create" icon="plus">
                        Thêm giáo viên
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($teachers && $teachers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
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

                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($teacher->holyName)
                                <span class="font-medium text-primary-600">
                                    {{ $teacher->holyName }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $teacher->name }}
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

                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($teacher->parishChild)
                                <div class="truncate max-w-xs" title="{{ $teacher->parishChild->name }}">
                                    {{ $teacher->parishChild->name }}
                                </div>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $teacher->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $teacher->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button
                                        wire:click="edit({{ $teacher->id }})"
                                        class="text-primary-600 hover:text-primary-800">
                                        Sửa
                                    </button>

                                    @if($isAdmin)
                                    <button
                                        wire:click="delete({{ $teacher->id }})"
                                        onclick="return confirm('Xác nhận xóa giáo viên {{ $teacher->name }}?')"
                                        class="text-red-600 hover:text-red-800">
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
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $teachers->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-12">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-2 text-gray-500">Chưa có giáo viên nào</p>
                <button
                    wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
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
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="teacher-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo viên' : 'Thêm giáo viên mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Nhập thông tin giáo viên giáo lý
                    </p>
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

                    {{-- Row: Tên thánh & Họ tên --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Tên thánh (1/3 width) --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Tên thánh
                            </label>
                            <select
                                wire:model.defer="holy_id"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Chọn --</option>
                                @foreach($holyNames as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('holy_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Họ tên (2/3 width) --}}
                        <div class="sm:col-span-2">
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
                    </div>

                    {{-- Giáo họ --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Giáo họ
                        </label>
                        <select
                            wire:model.defer="paid"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn giáo họ --</option>
                            @foreach($parishChildren as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('paid')
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
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire="save" icon="save" :loading="true">
                        Lưu
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