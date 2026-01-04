<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('home'),
            ],
            [
                'label' => 'Quản lý năm học',
                'url' => route('nam-hoc'),
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
                    <button
                        wire:click="create"
                        class="inline-flex items-center gap-2
                             bg-primary-600 px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600
                             text-white text-sm font-semibold hover:from-primary-600 hover:to-primary-700 active:scale-95 
                             disabled:bg-slate-300 disabled:cursor-not-allowed transition-all shadow-sm"
                        aria-label="Thêm năm học mới">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm năm học
                    </button>
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
                                @if ($nh->status)
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full bg-primary-100 text-primary-700">
                                    Hoạt động
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full bg-slate-200 text-slate-600">
                                    Lưu trữ
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    {{-- Edit Button --}}
                                    <button
                                        wire:click="edit({{ $nh->id }})"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                   text-primary-600 hover:text-primary-800 transition-colors"
                                        aria-label="Sửa năm học {{ $nh->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Sửa
                                    </button>

                                    <span class="text-slate-300">|</span>

                                    {{-- Toggle Status Button --}}
                                    <button
                                        wire:click="toggleStatus({{ $nh->id }})"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                   text-orange-600 hover:text-orange-800 transition-colors"
                                        aria-label="{{ $nh->status ? 'Lưu trữ' : 'Kích hoạt' }} năm học {{ $nh->name }}">
                                        @if($nh->status)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                        Lưu trữ
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Kích hoạt
                                        @endif
                                    </button>

                                    {{-- Delete Button (Only for Admin) --}}
                                    @if($isAdmin)
                                    <span class="text-slate-300">|</span>
                                    <button
                                        wire:click="delete({{ $nh->id }})"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa năm học này?\n\nLưu ý: Chỉ có thể xóa năm học chưa có khối học hoặc lớp học.')"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                       text-red-600 hover:text-red-800 transition-colors"
                                        aria-label="Xóa năm học {{ $nh->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Xóa
                                    </button>
                                    @endif
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
                                    <button
                                        wire:click="create"
                                        class="inline-flex items-center gap-2 bg-primary-600 text-white 
                                                   px-4 py-2 rounded-xl text-sm font-semibold 
                                                   hover:bg-primary-700 transition-all mt-4">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Thêm năm học đầu tiên
                                    </button>
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
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="namhoc-modal-title"
            wire:click="$set('showForm', false)">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden"
                wire:click.stop>
                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="namhoc-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật năm học' : 'Thêm năm học mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Thiết lập thông tin năm học và thời gian các học kỳ
                    </p>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
                    {{-- ✅ ERROR SUMMARY - Hiển thị tất cả lỗi --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
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

                    {{-- Tên năm học --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Tên năm học <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: 2024 – 2025"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Học kỳ I  --}}
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ I
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-slate-600">Bắt đầu</label>
                                <input
                                    type="date"
                                    wire:model.defer="start_date_one"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                                @error('start_date_one')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Kết thúc</label>
                                <input
                                    type="date"
                                    wire:model.defer="end_date_one"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                                @error('end_date_one')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Học kỳ II --}}
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ II
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-slate-600">Bắt đầu</label>
                                <input
                                    type="date"
                                    wire:model.defer="start_date_two"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                                @error('start_date_two')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Kết thúc</label>
                                <input
                                    type="date"
                                    wire:model.defer="end_date_two"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                                @error('end_date_two')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
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
                        Lưu năm học
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>