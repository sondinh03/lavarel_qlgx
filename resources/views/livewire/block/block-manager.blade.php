<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                [
                    'label' => 'Trang chủ',
                    'url' => route('home'),
                ],
                [
                    'label' => 'Quản lý khối học',
                    'url' => route('khoi-hoc'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M3 7h18M3 12h18M3 17h18\' />
                            </svg>',
                ],
            ]"
            separator="arrow" />

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý khối học"
                description="Danh sách các khối theo năm học"
                :stat-value="$blocks?->count()"
                stat-label="Khối học"
                icon-type="block">
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

                    {{-- LEFT: Filters --}}
                    <div class="flex items-center gap-3">

                        {{-- Năm học --}}
                        @livewire('class-filter-selector', [
                        'parish_id' => $parish_id,
                        'selectedNamHoc' => $selectedNamHoc,
                        'showKhoi' => false,
                        'showLop' => false,
                        ])

                        {{-- Search --}}
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm kiếm khối"
                            class="w-56 px-3 py-2 rounded-xl
                       border border-slate-300
                       text-sm
                       focus:ring-2 focus:ring-primary-500" />
                    </div>

                    {{-- RIGHT: Primary Action --}}
                    <button
                        wire:click="create"
                        @disabled(!$selectedNamHoc)
                        class="inline-flex items-center gap-2
                            px-5 py-2.5 rounded-xl
                            bg-gradient-to-r from-primary-500 to-primary-600
                            hover:from-primary-600 hover:to-primary-700
                            text-white text-sm font-semibold
                            active:scale-95
                            disabled:bg-slate-300 disabled:cursor-not-allowed
                            transition-all shadow-sm">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm khối
                    </button>

                </div>
            </div>
        </div>

        {{-- Table Section --}}
        @if($selectedNamHoc)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($blocks && $blocks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên khối</x-table-header>
                            <x-table-header>Năm học</x-table-header>
                            <x-table-header class="text-center">Thứ tự</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($blocks as $i => $block)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $i + 1}}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $block->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $block->namHoc->name ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                {{ $block->weight }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                    {{ $block->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $block->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button wire:click="edit({{ $block->id }})"
                                        class="text-primary-600 hover:text-primary-800">
                                        Sửa
                                    </button>

                                    @if($isAdmin)
                                    <button wire:click="delete({{ $block->id }})"
                                        onclick="return confirm('Xóa khối học?')"
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
            <div class="px-6 py-4 border-t border-gray-200">
                {{-- {{ $blocks->links() }} --}}
            </div>
            @else
            <div class="text-center py-12">
                <i class="las la-inbox text-6xl text-gray-300"></i>
                <p class="mt-2 text-gray-500">Chưa có khối học nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                    <i class="las la-plus mr-1"></i> Thêm khối học đầu tiên
                </button>
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <i class="las la-hand-point-up text-6xl text-gray-300"></i>
            <p class="mt-4 text-lg text-gray-500">Vui lòng chọn năm học để xem danh sách khối</p>
        </div>
        @endif

        {{-- Form Modal --}}
        @if ($showForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="block-modal-title"
            wire:click="$set('showForm', false)">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden"
                wire:click.stop>
                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="block-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật khối học' : 'Thêm khối học mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Khối học thuộc năm học đã chọn
                    </p>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
                    {{-- Tên khối --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Tên khối <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: Khối 1, Khối 2..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Thứ tự --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Thứ tự sắp xếp
                        </label>
                        <input
                            type="number"
                            min="0"
                            wire:model.defer="weight"
                            placeholder="0"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="mt-1 text-xs text-slate-500">
                            Số càng nhỏ sẽ hiển thị trước
                        </p>
                        @error('weight')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div class="flex items-center gap-3 pt-1">
                        <input
                            id="block-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300
                           text-primary-600 focus:ring-primary-500">
                        <label for="block-status" class="text-sm text-slate-700">
                            Hoạt động
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <button
                        wire:click="$set('showForm', false)"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-300
                       text-slate-700 font-semibold hover:bg-slate-100
                       active:scale-95 transition-all">
                        Huỷ
                    </button>

                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white
                       font-semibold hover:bg-primary-700
                       active:scale-95 transition-all
                       disabled:opacity-60">
                        Lưu khối
                    </button>
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