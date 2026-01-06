<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                [
                    'label' => 'Trang chủ',
                    'url' => route('home'),
                ],
                [
                    'label' => 'Quản lý lớp học',
                    'url' => route('ds-lop'),
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
                        {{-- Filter --}}
                        <div class="p-6 bg-slate-50">
                            <livewire:filters.filter-bar
                                :show-nam-hoc="true"
                                :show-khoi="true"
                                :show-lop="false"
                                :show-ky="false"
                                :selected-nam-hoc="$selectedNamHoc"
                                :selected-khoi="$selectedKhoi" />

                            <x-loading.overlay
                                wire-target="selectedNamHoc,selectedKhoi,resetFilters"
                                mode="inline" />
                        </div>

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
                            <x-table-header>Sĩ số</x-table-header>
                            <x-table-header>Giáo lý viên</x-table-header>
                            <x-table-header>Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($lops as $index => $lop)
                        <x-lop.row
                            :lop="$lop"
                            :index="$index"
                            :paginator="$lops" />
                        @empty
                        <x-empty-state
                            icon="class"
                            :colspan="7"
                            :title="$selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học'"
                            :description="!$selectedNamHoc
                                    ? 'Vui lòng chọn năm học để xem danh sách lớp'
                                    : ($selectedKhoi
                                        ? 'Không có lớp nào trong khối này'
                                        : 'Chưa có lớp học nào trong năm học này')">
                            @if($selectedNamHoc)
                            <a href="{{ route('lop.create') }}"
                                class="inline-flex items-center gap-2 px-6 py-2.5
                                              bg-gradient-to-r from-primary-500 to-primary-600
                                              hover:from-primary-600 hover:to-primary-700
                                              text-white rounded-xl font-semibold
                                              active:scale-[0.98] transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tạo lớp học mới
                            </a>
                            @endif
                        </x-empty-state>
                        @endforelse
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
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden"
                wire:click.stop>
                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="lop-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật lớp học' : 'Thêm lớp học mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Lớp học thuộc năm học đã chọn
                    </p>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
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

                    {{-- Mã lớp --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Mã lớp <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="symbol"
                            placeholder="Ví dụ: L01, L02..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('symbol')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tên lớp --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Tên lớp <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: Lớp 1A, Lớp 2B..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Khối học --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Khối học <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.defer="block"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn khối --</option>
                            @foreach($availableBlocks as $blk)
                            <option value="{{ $blk->id }}">{{ $blk->name }}</option>
                            @endforeach
                        </select>
                        @error('block')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div class="flex items-center gap-3 pt-1">
                        <input
                            id="lop-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300
                           text-primary-600 focus:ring-primary-500">
                        <label for="lop-status" class="text-sm text-slate-700">
                            Hoạt động
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <button
                        wire:click="closeModal"
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
                       disabled:opacity-60">
                        Lưu lớp
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