@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Quản lý khối học'],
]" />
@endsection

<div
    class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite" class="mb-4">
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

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý khối học"
                description="Danh sách các khối theo năm học"
                icon-type="block" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1 min-w-0">
                            <livewire:filters.filter-bar
                                :parish-id="$parishId"
                                :show-nam-hoc="true"
                                :show-khoi="false"
                                :show-lop="false"
                                :show-ky="false"
                                :selected-nam-hoc="$selectedNamHoc" />
                        </div>

                        <div class="flex-shrink-0 pb-0.5">
                            <x-button wire:click="resetFilters" variant="subtle">
                                <x-icon name="refresh" />
                                Đặt lại
                            </x-button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            wire-model="search"
                            placeholder="Tìm kiếm khối..."
                            debounce="500ms"
                            class="max-w-md" />

                        <x-button wire:click="create" variant="primary" :disabled="!$selectedNamHoc">
                            <x-icon name="plus" />
                            Thêm khối
                        </x-button>
                    </div>
                </div>
            </div>

            @if($selectedNamHoc)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-slate-100/80 text-sm text-slate-700">
                Đang xem khối học trong năm học đã chọn
            </div>

            @if($blocks && $blocks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên khối</x-table-header>
                            <x-table-header class="text-center">Thứ tự</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($blocks as $i => $block)
                        <tr class="hover:bg-black/[0.03] transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ $i + 1 }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ $block->name }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ $block->weight }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $block->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $block->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex gap-3">
                                    <button wire:click="edit({{ $block->id }})"
                                        class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                        Sửa
                                    </button>

                                    @if($isAdmin)
                                    <button wire:click="delete({{ $block->id }})"
                                        onclick="return confirm('Xóa khối học?')"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
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
            @else
            <x-stats.page-empty
                :panel="false"
                tone="primary"
                title="Chưa có khối học nào"
                description="Thêm khối học đầu tiên cho năm học đã chọn">
                <x-button wire:click="create" variant="primary">
                    <x-icon name="plus" />
                    Thêm khối học
                </x-button>
            </x-stats.page-empty>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                tone="slate"
                title="Chọn năm học"
                description="Vui lòng chọn năm học để xem danh sách khối" />
            @endif
        </x-mac-panel>

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
