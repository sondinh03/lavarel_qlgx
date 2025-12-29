<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Quản lý Khối học</h1>
        <p class="text-gray-600">Quản lý các khối học theo năm học</p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    {{-- Filters Section --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Năm học Selector --}}
            <div>
                <label for="selectedNamHoc" class="block text-sm font-medium text-gray-700 mb-2">
                    Năm học <span class="text-red-500">*</span>
                </label>
                <select wire:model="selectedNamHoc" id="selectedNamHoc"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Chọn năm học --</option>
                    @foreach($namHocs as $nh)
                        <option value="{{ $nh->id }}">{{ $nh->name }}</option>
                    @endforeach
                </select>
                @error('selectedNamHoc')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Search --}}
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Tìm kiếm
                </label>
                <input wire:model.debounce.500ms="search" type="text" id="search" placeholder="Tìm theo tên khối..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Per Page --}}
            <div>
                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-2">
                    Hiển thị
                </label>
                <select wire:model="perPage" id="perPage"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($this->getPerPageOptions() as $option)
                        <option value="{{ $option }}">{{ $option }} / trang</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-4 flex justify-between items-center">
            <button wire:click="create" 
                @if(!$selectedNamHoc) disabled @endif
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                <i class="las la-plus mr-1"></i> Thêm khối học
            </button>

            <div class="flex gap-2">
                <button wire:click="resetFilters"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="las la-redo mr-1"></i> Đặt lại
                </button>
                
                <button wire:click="handleRefresh"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="las la-sync mr-1"></i> Làm mới
                </button>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    @if($selectedNamHoc)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($blocks && $blocks->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên khối
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Năm học
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thứ tự
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($blocks as $index => $block)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $blocks->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $block->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $block->namHoc->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center gap-1">
                                            <button wire:click="moveUp({{ $block->id }})"
                                                class="text-blue-600 hover:text-blue-900" title="Di chuyển lên">
                                                <i class="las la-arrow-up text-lg"></i>
                                            </button>
                                            <span class="text-sm text-gray-700 px-2">{{ $block->weight }}</span>
                                            <button wire:click="moveDown({{ $block->id }})"
                                                class="text-blue-600 hover:text-blue-900" title="Di chuyển xuống">
                                                <i class="las la-arrow-down text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="toggleStatus({{ $block->id }})"
                                            class="px-3 py-1 text-xs font-semibold rounded-full {{ $block->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $block->status ? 'Hoạt động' : 'Tắt' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center gap-2">
                                            <button wire:click="edit({{ $block->id }})"
                                                class="text-blue-600 hover:text-blue-900" title="Sửa">
                                                <i class="las la-edit text-xl"></i>
                                            </button>
                                            
                                            @if($isAdmin)
                                                <button wire:click="delete({{ $block->id }})"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa khối học này?')"
                                                    class="text-red-600 hover:text-red-900" title="Xóa">
                                                    <i class="las la-trash text-xl"></i>
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