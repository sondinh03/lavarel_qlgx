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
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-indigo-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Quản lý khối học</h1>
                            <p class="text-sm text-slate-600 mt-1">
                                Quản lý các khối theo năm học
                            </p>
                        </div>
                    </div>

                    @if($blocks)
                    <div class="text-right pl-6 border-l border-slate-200">
                        <div class="text-3xl font-bold text-indigo-600">
                            {{ $blocks->total() }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium">Khối</div>
                    </div>
                    @endif
                </div>
            </div>

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

            {{-- Filters Section --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Năm học --}}
                    <select wire:model="selectedNamHoc"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Chọn năm học --</option>
                        @foreach($namHocs as $nh)
                        <option value="{{ $nh->id }}">{{ $nh->name }}</option>
                        @endforeach
                    </select>

                    {{-- Search --}}
                    <input wire:model.debounce.500ms="search"
                        placeholder="Tìm theo tên khối..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">

                    {{-- Per page --}}
                    <select wire:model="perPage"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        @foreach($this->getPerPageOptions() as $opt)
                        <option value="{{ $opt }}">{{ $opt }} / trang</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        wire:click="create"
                        @disabled(!$selectedNamHoc)
                        class="inline-flex items-center gap-2
               px-4 py-2 rounded-xl
               bg-indigo-600 text-white text-sm font-semibold
               hover:bg-indigo-700 active:scale-95
               disabled:bg-slate-300 disabled:cursor-not-allowed
               transition-all">

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
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($blocks && $blocks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>#</x-table-header>
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
                                {{ $blocks->firstItem() + $i }}
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
                    {{ $block->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $block->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button wire:click="edit({{ $block->id }})"
                                        class="text-indigo-600 hover:text-indigo-800">
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
                {{ $blocks->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <i class="las la-inbox text-6xl text-gray-300"></i>
                <p class="mt-2 text-gray-500">Chưa có khối học nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="las la-plus mr-1"></i> Thêm khối học đầu tiên
                </button>
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="las la-hand-point-up text-6xl text-gray-300"></i>
            <p class="mt-4 text-lg text-gray-500">Vui lòng chọn năm học để xem danh sách khối</p>
        </div>
        @endif

        {{-- Form Modal --}}
        @if($showForm)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                {{-- Modal Header --}}
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Sửa khối học' : 'Thêm khối học mới' }}
                    </h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <i class="las la-times text-2xl"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="mt-4">
                    {{-- Tên khối --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Tên khối <span class="text-red-500">*</span>
                        </label>
                        <input wire:model.defer="name" type="text" id="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ví dụ: Khối 1, Khối 2...">
                        @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Thứ tự --}}
                    <div class="mb-4">
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                            Thứ tự sắp xếp
                        </label>
                        <input wire:model.defer="weight" type="number" id="weight" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0">
                        @error('weight')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Số càng nhỏ càng ưu tiên hiển thị trước</p>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input wire:model.defer="status" type="checkbox" value="1"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Hoạt động</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-2 mt-6 pt-3 border-t">
                    <button wire:click="cancel"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Hủy
                    </button>
                    <button wire:click="save"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="las la-save mr-1"></i> Lưu
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
        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>