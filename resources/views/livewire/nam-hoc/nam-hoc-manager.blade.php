<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('dashboard'),
            ],
            [
                'label' => 'Quản lý năm học',
                'url' => route('school-years.index'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'
            ],
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite">
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

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Quản lý năm học"
                description="Danh sách các năm học của giáo xứ"
                :stat-value="$namHocs?->count()"
                stat-label="Năm học"
                icon-type="schoolYear">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex justify-end">
                    {{-- Create Button --}}
                    <x-action-button wire="create" icon="plus">
                        Thêm năm học
                    </x-action-button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên năm học</x-table-header>
                            <x-table-header class="text-center">Học kỳ I</x-table-header>
                            <x-table-header class="text-center">Học kỳ II</x-table-header>
                            <x-table-header class="text-center">HK hiện tại</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($namHocs as $i => $nh)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $nh->name }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center text-sm text-slate-600">
                                @if($nh->start_date_one && $nh->end_date_one)
                                <div class="inline-flex items-center gap-1">
                                    <span>{{ $nh->start_date_one->format('d/m/Y') }}</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span>{{ $nh->end_date_one->format('d/m/Y') }}</span>
                                </div>
                                @else
                                <span class="text-slate-400">Chưa thiết lập</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center text-sm text-slate-600">
                                @if($nh->start_date_two && $nh->end_date_two)
                                <div class="inline-flex items-center gap-1">
                                    <span>{{ $nh->start_date_two->format('d/m/Y') }}</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span>{{ $nh->end_date_two->format('d/m/Y') }}</span>
                                </div>
                                @else
                                <span class="text-slate-400">Chưa thiết lập</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($nh->current_semester)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold 
                                                   rounded-full bg-emerald-100 text-emerald-700">
                                    HK {{ $nh->current_semester }}
                                </span>
                                @else
                                <span class="text-slate-400 text-sm">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full {{ $nh->status_class }}">
                                    {{ $nh->status_label }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    {{-- Edit Button  --}}
                                    <x-table-action
                                        wire="edit({{ $nh->id }})"
                                        icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    {{-- Toggle Status Button --}}
                                    <x-table-action
                                        wire="toggleStatus({{ $nh->id }})"
                                        :icon="$nh->status ? 'archive' : 'check'"
                                        :color="$nh->status ? 'warning' : 'success'"
                                        :loading="true"
                                        debounce="500">
                                        {{ $nh->status ? 'Lưu trữ' : 'Kích hoạt' }}
                                    </x-table-action>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-empty-state
                                    icon="calendar"
                                    title="Chưa có năm học"
                                    description="Hãy tạo năm học đầu tiên cho giáo xứ">
                                    <x-action-button wire="create" icon="plus">
                                        Thêm năm học
                                    </x-action-button>
                                </x-empty-state>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Form --}}
        @if ($showForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="namhoc-modal-title"
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="namhoc-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật năm học' : 'Thêm năm học mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Thiết lập thông tin năm học và thời gian các học kỳ
                    </p>
                </div>

                {{-- Body - SCROLLABLE --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    {{-- ✅ ERROR SUMMARY - Hiển thị tất cả lỗi --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 animate-shake">
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

                    {{-- Tên năm học  --}}
                    <x-form-input label="Tên năm học"
                        name="name"
                        wire:model="name"
                        placeholder="Ví dụ: 2025 – 2026"
                        required />

                    {{-- Học kỳ I  --}}
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ I
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form-input label="Bắt đầu"
                                name="start_date_one"
                                type="date"
                                wire:model="start_date_one" />

                            <x-form-input label="Kết thúc"
                                name="end_date_one"
                                type="date"
                                wire:model="end_date_one" />
                        </div>
                    </div>

                    {{-- Học kỳ II --}}
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ II
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form-input label="Bắt đầu"
                                name="start_date_two"
                                type="date"
                                wire:model="start_date_two" />

                            <x-form-input label="Kết thúc"
                                name="end_date_two"
                                type="date"
                                wire:model="end_date_two" />
                        </div>
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