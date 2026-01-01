<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#lop-list-main" class="sr-only focus:not-sr-only">
        Bỏ qua tới nội dung
    </a>

    <main id="lop-list-main" class="mx-auto max-w-7xl space-y-5">

        {{-- ===================== BREADCRUMB ===================== --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            [
                'label' => 'Quản lý lớp học',
                'icon'  => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>',
            ],
        ]" />

        {{-- ===================== TOAST ===================== --}}
        <div role="status" aria-live="polite" aria-atomic="true">
            @foreach (['message' => 'success', 'error' => 'error', 'warning' => 'warning'] as $key => $type)
                @if (session()->has($key))
                    <x-toast-notification :type="$type" :duration="3500">
                        {{ session($key) }}
                    </x-toast-notification>
                @endif
            @endforeach
        </div>

        {{-- ===================== HEADER + FILTER ===================== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4">
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white w-full">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900">
                                    Quản lý lớp học
                                </h1>
                                <p class="text-sm text-slate-600 mt-1">
                                    @if($selectedNamHoc)
                                        Quản lý {{ $lops?->total() ?? 0 }} lớp trong năm học
                                        <span class="font-semibold text-slate-900">
                                            {{ $namHocs[$selectedNamHoc] ?? '' }}
                                        </span>
                                    @else
                                        Chọn năm học để xem danh sách lớp
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($parish_id && $selectedNamHoc)
                            <a href="{{ route('lop.create') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5
                                      bg-gradient-to-r from-primary-500 to-primary-600
                                      hover:from-primary-600 hover:to-primary-700
                                      text-white rounded-xl font-semibold
                                      active:scale-[0.98] transition-all shadow-sm"
                               aria-label="Thêm lớp học mới">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="hidden sm:inline">Thêm lớp học</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="p-6 bg-slate-50">
                @livewire('class-filter-selector', [
                    'parish_id'      => $parish_id,
                    'selectedNamHoc' => $selectedNamHoc,
                    'selectedKhoi'   => $selectedKhoi,
                    'showLop'        => false,
                ])

                <x-loading.overlay
                    wire-target="selectedNamHoc,selectedKhoi,resetFilters"
                    mode="inline"
                />
            </div>
        </section>

        {{-- ===================== TABLE ===================== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <x-loading.overlay
                wire-target="selectedNamHoc,selectedKhoi,resetFilters"
                mode="centered">
                Đang tải danh sách lớp...
            </x-loading.overlay>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0"
                       aria-label="Danh sách lớp học">

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
                                :paginator="$lops"
                            />
                        @empty
                            <x-empty-state
                                icon="class"
                                :colspan="7"
                                :title="$selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học'"
                                :description="!$selectedNamHoc
                                    ? 'Vui lòng chọn năm học để xem danh sách lớp'
                                    : ($selectedKhoi
                                        ? 'Không có lớp nào trong khối này'
                                        : 'Chưa có lớp học nào trong năm học này')"
                            >
                                @if($isAdmin && $selectedNamHoc)
                                    <a href="{{ route('lop.create') }}"
                                       class="inline-flex items-center gap-2 px-6 py-2.5
                                              bg-gradient-to-r from-primary-500 to-primary-600
                                              hover:from-primary-600 hover:to-primary-700
                                              text-white rounded-xl font-semibold
                                              active:scale-[0.98] transition-all shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 4v16m8-8H4"/>
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
                <div class="border-t border-slate-200">
                    <x-pagination
                        :paginator="$lops"
                        :per-page-options="[10, 15, 25, 50]"
                    />
                </div>
            @endif
        </section>

    </main>
</div>
