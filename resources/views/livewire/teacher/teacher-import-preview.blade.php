<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Skip link --}}
        <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('catechists.index')],
            ['label' => 'Quản lý Giáo lý viên', 'url' => route('catechists.index')],
            ['label' => 'Import danh sách']
        ]" />

        {{-- Toast Notifications --}}
        @if (session()->has('success'))
        <x-toast-notification type="success" :duration="3500">
            {{ session('success') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Page Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Import danh sách Giáo lý viên</h1>
                            <p class="text-sm text-slate-600 mt-1">Nhập danh sách từ file Excel hoặc CSV</p>
                        </div>
                    </div>

                    <a href="{{ route('catechists.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5
                               bg-white border border-slate-300 rounded-xl
                               text-slate-700 font-semibold hover:bg-slate-100
                               active:scale-95 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Quay lại</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Upload Section --}}
        <form wire:submit.prevent="preview" enctype="multipart/form-data" id="main-content">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                <div class="p-6">
                    <div class="flex items-center gap-4">

                        {{-- File Input --}}
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-slate-900 mb-2">
                                Chọn file Excel hoặc CSV
                            </label>
                            <input id="file-upload"
                                type="file"
                                wire:model="file"
                                accept=".xlsx,.csv"
                                class="block w-full text-sm text-slate-700
                                       file:mr-4 file:py-2.5 file:px-4
                                       file:rounded-xl file:border-0
                                       file:bg-primary-600
                                       file:text-white file:font-semibold
                                       file:shadow-sm
                                       hover:file:bg-primary-700
                                       file:cursor-pointer file:transition-all
                                       border border-slate-200 rounded-xl
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('file')
                            <p class="flex items-center gap-1.5 text-sm text-red-600 mt-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Download Template Button (disabled in development) --}}
                        <div class="pt-6">
                            <button type="button"
                                disabled
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                       bg-slate-200 text-slate-500
                                       cursor-not-allowed opacity-60">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Tải file mẫu
                            </button>
                            <p class="text-xs text-slate-500 mt-1 text-center">
                                (Đang phát triển)
                            </p>
                        </div>

                        {{-- Preview Button --}}
                        <div class="pt-6">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                                       bg-gradient-to-r from-primary-500 to-primary-600 
                                       text-white font-semibold
                                       hover:from-primary-600 hover:to-primary-700
                                       active:scale-[0.98] transition-all shadow-sm
                                       disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading>
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove>Xem trước</span>
                                <span wire:loading>Đang xử lý...</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </form>

        {{-- Validation Errors --}}
        @if (!empty($errors) && is_array($errors))
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl overflow-hidden">
            <div class="p-4 bg-red-100 border-b border-red-200">
                <h3 class="font-semibold text-red-900 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Phát hiện {{ count($errors) }} lỗi trong dữ liệu
                </h3>
            </div>
            <div class="p-4">
                <ul class="space-y-2">
                    @foreach ($errors as $error)
                    <li class="flex items-start gap-2 text-sm text-red-700">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $error }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Preview Table --}}
        @if (!empty($rows))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Table Header --}}
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Xem trước dữ liệu
                        </h3>
                        <p class="text-sm text-slate-600 mt-1">
                            Tìm thấy <span class="font-semibold text-slate-900">{{ count($rows) }}</span> bản ghi
                            @if($readyToImport)
                            <span class="text-green-600">• Sẵn sàng import</span>
                            @else
                            <span class="text-amber-600">• Có lỗi cần xử lý</span>
                            @endif
                        </p>
                    </div>

                    {{-- Stats --}}
                    <div class="flex items-center gap-4">
                        @php
                        $validCount = collect($rows)->where('duplicate', false)->count();
                        $duplicateCount = collect($rows)->where('duplicate', true)->count();
                        @endphp

                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $validCount }}</div>
                            <div class="text-xs text-slate-600">Hợp lệ</div>
                        </div>

                        @if($duplicateCount > 0)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $duplicateCount }}</div>
                            <div class="text-xs text-slate-600">Trùng lặp</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                #
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Tên thánh
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Họ tên
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Ngày sinh
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Số điện thoại
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Giáo họ
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Tạo TK
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                                Trạng thái
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rows as $index => $row)
                        <tr class="hover:bg-slate-50 transition-colors
                                   {{ $row['duplicate'] ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 text-sm text-slate-900 border-b border-slate-100">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 border-b border-slate-100">
                                {{ $row['ten_thanh'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 border-b border-slate-100">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $row['ho_ten'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 border-b border-slate-100">
                                {{ $row['ngay_sinh'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 border-b border-slate-100">
                                <span class="text-sm font-mono text-slate-900">
                                    {{ $row['so_dien_thoai'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 border-b border-slate-100">
                                {{ $row['giao_ho'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center border-b border-slate-100">
                                @if(strtolower($row['tao_tai_khoan']) === 'có' || strtolower($row['tao_tai_khoan']) === 'yes')
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-100 text-green-700 text-xs font-medium">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Có
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium">
                                    Không
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center border-b border-slate-100">
                                @if ($row['duplicate'])
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-semibold">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Trùng SĐT
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-green-100 text-green-700 text-xs font-semibold">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Hợp lệ
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Actions --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <p class="text-sm text-slate-600">
                        @if($readyToImport)
                        <span class="flex items-center gap-2 text-green-600 font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Dữ liệu hợp lệ, sẵn sàng import
                        </span>
                        @else
                        <span class="flex items-center gap-2 text-amber-600 font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Có lỗi trong dữ liệu, vui lòng kiểm tra lại
                        </span>
                        @endif
                    </p>

                    <div class="flex gap-3">
                        <a href="{{ route('catechists.index') }}"
                            class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl
                                   text-slate-700 font-semibold hover:bg-slate-100
                                   active:scale-95 transition-all">
                            Hủy bỏ
                        </a>

                        <button
                            wire:click="confirmImport"
                            @disabled(!$readyToImport)
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                                   {{ $readyToImport
                                        ? 'bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700'
                                        : 'bg-slate-300 cursor-not-allowed' }}
                                   text-white font-semibold
                                   active:scale-[0.98] transition-all shadow-sm
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span wire:loading.remove>Xác nhận import</span>
                            <span wire:loading>Đang import...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush