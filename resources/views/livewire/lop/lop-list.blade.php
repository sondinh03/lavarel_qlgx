<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>
    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- ✅ BREADCRUMB ADDED --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ', 
                'url' => route('home')
            ],
            [
                'label' => 'Quản lý lớp học',
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>'
            ]
        ]" />

        {{-- Toast Notifications (standardized durations + live region) --}}
        <div role="status" aria-live="polite" aria-atomic="true">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="3500">
                {{ session('error') }}
            </x-toast-notification>
            @endif

            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="3500">
                {{ session('warning') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Combined Header + Filter + Stats Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header Section --}}
            <x-page-header
                title="Quản lý lớp học"
                description="{{ $selectedNamHoc ? 'Quản lý ' . ($lops ? $lops->total() : 0) . ' lớp học trong năm học ' . ($namHocs[$selectedNamHoc] ?? '') : 'Chọn năm học để xem danh sách lớp' }}"
                icon="class"
                gradient="purple"
                :count="$selectedNamHoc && $lops ? $lops->total() : null"
                count-label="Tổng số lớp" />

            @if($parish_id && $selectedNamHoc)
            <div class="flex-shrink-0">
                <a href="{{ route('lop.create') }}"
                    class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-purple-700 active:scale-95 transition-all shadow-lg hover:shadow-xl"
                    aria-label="Thêm lớp học mới">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Thêm lớp học</span>
                    <span class="sm:hidden">Thêm</span>
                </a>
            </div>
            @endif

            {{-- Filter Section: use shared ClassFilterSelector (hide lớp select here) --}}
            <div class="p-6 bg-slate-50">
                @livewire('class-filter-selector', [
                'parish_id' => $parish_id,
                'selectedNamHoc' => $selectedNamHoc,
                'selectedKhoi' => $selectedKhoi,
                'showLop' => false,
                ])

                {{-- keep inline loading indicator for page-level targets --}}
                <x-loading.overlay wire-target="selectedNamHoc,selectedKhoi,resetFilters" mode="inline" />
            </div>
        </div>

        {{-- Class Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-loading.overlay wire-target="selectedNamHoc,selectedKhoi,resetFilters" mode="centered">Đang tải danh sách lớp...</x-loading.overlay>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0" role="table" aria-label="Danh sách lớp học" aria-describedby="lop-list-caption">
                    <caption id="lop-list-caption" class="sr-only">
                        Danh sách {{ $lops ? $lops->total() : 0 }} lớp học
                        @if($selectedNamHoc) trong năm học {{ $namHocs[$selectedNamHoc] ?? '' }} @endif
                        @if($selectedKhoi) khối {{ $khois[$selectedKhoi] ?? '' }} @endif
                    </caption>
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr role="row">
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
                        @forelse($lops as $index => $lop)
                        <x-lop.row :lop="$lop" :index="$index" :paginator="$lops" />

                        @empty
                        <x-empty-state
                            icon="class"
                            :colspan="7"
                            :title="$selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học'"
                            :description="!$selectedNamHoc ? 'Vui lòng chọn năm học để xem danh sách lớp' : ($selectedKhoi ? 'Không có lớp nào trong khối này' : 'Chưa có lớp học nào trong năm học này')">
                            @if($isAdmin && $selectedNamHoc)
                            <a href="{{ route('lop.create') }}"
                                class="mt-4 bg-purple-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-purple-700 active:scale-95 transition-all flex items-center gap-2 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
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
            @if($lops->hasPages())
            <x-pagination :paginator="$lops" :per-page-options="[10, 15, 25, 50]" />
            @endif
        </div>
    </div>
</div>

{{-- Alpine is loaded in the main layout --}}