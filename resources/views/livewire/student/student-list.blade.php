<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ✅ BREADCRUMB ADDED --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ', 
                'url' => route('home')
            ],
            [
                'label' => 'Quản lý lớp học',
                'url' => route('ds-lop'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>'
            ],
            [
                'label' => $lop->name ?? 'Chi tiết lớp'
            ]
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        @if (session()->has('message'))
        <x-toast-notification type="success">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Loading Overlay --}}
        <x-loading-indicator target="search,perPage" message="Đang tải dữ liệu học sinh..." />

        {{-- Class Header Card --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">{{ $lop->name }}</h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600 mt-1">
                            <span class="font-medium">{{ $lop->symbol }}</span>
                            <span>•</span>
                            <span>{{ $lop->schoolyear }}</span>
                            <span>•</span>
                            <span class="font-semibold">{{ $total }} học sinh</span>
                            @if($countnam)<span class="text-blue-600">({{ $countnam }} nam)</span>@endif
                            @if($countnu)<span class="text-pink-600">({{ $countnu }} nữ)</span>@endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $lop->slug }}/lophoc={{ $lop->id }}" class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 text-white text-sm font-semibold rounded-xl hover:bg-blue-600 active:scale-95 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Điểm danh
                    </a>
                    <a href="{{ $lop->slug }}/dile={{ $lop->id }}" class="flex items-center gap-2 px-4 py-2.5 bg-green-500 text-white text-sm font-semibold rounded-xl hover:bg-green-600 active:scale-95 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Kết quả
                    </a>
                    <a href="{{ $lop->slug }}/khaokinh={{ $lop->id }}" class="flex items-center gap-2 px-4 py-2.5 bg-orange-500 text-white text-sm font-semibold rounded-xl hover:bg-orange-600 active:scale-95 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        QR
                    </a>
                    <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-purple-500 text-white text-sm font-semibold rounded-xl hover:bg-purple-600 active:scale-95 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- Search and Filter Card --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Search Input --}}
                <x-search-input
                    placeholder="Tìm kiếm theo tên thánh, họ tên, hoặc mã học sinh..."
                    wire-model="search"
                    debounce="500ms" />

                {{-- Filter Button (Placeholder) --}}
                <div class="relative">
                    <button type="button"
                        class="flex items-center gap-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 active:scale-[0.98] transition-all whitespace-nowrap">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="font-semibold text-slate-900">Lọc</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Student Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
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
                            <x-table-header>Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $hocsinh)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- Checkbox --}}
                            <!-- <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $hocsinh->id }}" class="w-4 h-4 rounded border-slate-300 text-blue-500 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td> -->

                            {{-- STT --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $hocsinh->stt }}
                            </td>

                            {{-- Mã HS --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-blue-600 font-semibold">
                                {{ $hocsinh->mahv }}
                            </td>

                            {{-- Tên thánh --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $hocsinh->holy }}
                            </td>

                            {{-- Họ --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $hocsinh->last_name }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $hocsinh->name }}
                            </td>

                            {{-- Ngày sinh --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $hocsinh->birthday }}
                            </td>

                            {{-- Cha --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                {{ $hocsinh->father ?? '-' }}
                            </td>

                            {{-- Giáo họ --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ ($hocsinh->paid ?? '') === 'Nhà xứ' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $hocsinh->paid ?? 'Chưa xác định' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    {{-- Xem chi tiết --}}
                                    <a href="{{ $hocsinh->slug }}" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg active:scale-95 transition-all" title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Sửa --}}
                                    <a href="{{ $hocsinh->edit ?? '#' }}" class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg active:scale-95 transition-all" title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- More Menu --}}
                                    <div class="relative" x-data="{ open: false, menuStyle: {}, toggleMenu(){ this.open = !this.open; if(this.open){ this.$nextTick(()=>{ const btn = this.$refs.toggle; const menu = this.$refs.menu; const rect = btn.getBoundingClientRect(); // ensure menu is visible to measure
                                                        menu.style.display = 'block'; const menuH = menu.offsetHeight || 220; const menuW = menu.offsetWidth || 220; const spaceBelow = window.innerHeight - rect.bottom; const spaceAbove = rect.top; let top = rect.bottom; if(spaceBelow < menuH && spaceAbove > menuH){ top = rect.top - menuH; } let left = rect.right - menuW; if(left < 8) left = rect.left; this.menuStyle = { position: 'fixed', top: top + 'px', left: left + 'px', zIndex: 9999, minWidth: menuW + 'px' }; }); } }, closeMenu(){ this.open = false; this.menuStyle = {}; } }">
                                        <button @click="toggleMenu()" x-ref="toggle" type="button" class="p-2 hover:bg-slate-100 rounded-lg active:scale-95 transition-all" title="Thêm" aria-haspopup="true" :aria-expanded="open">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>

                                        <div x-ref="menu" x-show="open" @click.outside="closeMenu()" x-transition x-cloak :style="menuStyle" class="hidden bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">
                                            <button type="button" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 4v1m-6-4H5m3.5-7.5L12 3l3.5 3.5M12 21l-3.5-3.5L12 14l3.5 3.5L12 21z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">QR Code</span>
                                            </button>

                                            <button type="button" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">Nhập điểm</span>
                                            </button>

                                            <button type="button" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">Kết quả</span>
                                            </button>

                                            <a href="{{ $hocsinh->thugioithieu ?? '#' }}" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">Thư giới thiệu</span>
                                            </a>

                                            <button type="button" class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors flex items-center gap-3 border-b border-slate-100">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm font-medium text-slate-900">Xuất báo cáo</span>
                                            </button>

                                            <button type="button" class="w-full px-4 py-3 text-left hover:bg-red-50 transition-colors flex items-center gap-3">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-sm font-medium text-red-600">Xóa học sinh</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        {{-- @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="font-medium">Không tìm thấy học sinh nào</p>
                            </td>
                        </tr>
                        @endforelse --}}

                        @empty
                        <x-empty-state
                            icon="students"
                            :colspan="10"
                            title="Không tìm thấy học sinh"
                            description="Không có học sinh nào phù hợp với tìm kiếm của bạn" />
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- <div class="px-6 py-4 bg-white border-t border-slate-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-slate-600">
                        Hiển thị <span class="font-semibold">{{ $students->firstItem() ?? 0 }}</span>
            đến <span class="font-semibold">{{ $students->lastItem() ?? 0 }}</span>
            trong tổng số <span class="font-semibold">{{ $students->total() }}</span> học sinh
        </div>

        <div class="flex items-center gap-2">
            @if ($students->onFirstPage())
            <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                ← Trước
            </span>
            @else
            <button wire:click="previousPage" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                ← Trước
            </button>
            @endif

            <div class="flex items-center gap-1">
                @foreach ($students->getUrlRange(1, $students->lastPage()) as $page => $url)
                @if ($page == $students->currentPage())
                <span class="px-3 py-2 text-sm font-bold text-white bg-blue-500 rounded-lg">
                    {{ $page }}
                </span>
                @else
                <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    {{ $page }}
                </button>
                @endif
                @endforeach
            </div>

            @if ($students->hasMorePages())
            <button wire:click="nextPage" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Sau →
            </button>
            @else
            <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                Sau →
            </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-600">Hiển thị:</label>
            <select wire:model.live="perPage" class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div> --}}

{{-- Pagination --}}
<x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50, 100]" />
</div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
