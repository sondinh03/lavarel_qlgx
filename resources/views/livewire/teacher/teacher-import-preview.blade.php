<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-700">
            Import danh sách Giáo lý viên
        </h1>

        <a href="{{ route('catechists.index') }}"
            class="text-sm text-slate-500 hover:text-slate-700">
            ← Quay lại danh sách
        </a>
    </div>

    {{-- Upload --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <label class="block text-sm font-semibold text-slate-700">
            Chọn file Excel
        </label>

        {{-- <input type="file"
            wire:model="file"
            accept=".xlsx,.csv"
            class="block w-full text-sm
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0
                      file:bg-primary-600 file:text-white
                      hover:file:bg-primary-700"> --}}
        <form wire:submit.prevent="preview"
            enctype="multipart/form-data"
            class="space-y-4">

            <input type="file"
                wire:model="file"
                accept=".xlsx,.csv">

            <button type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded">
                Xem trước
            </button>
        </form>


        @error('file')
        <p class="text-sm text-red-500">{{ $message }}</p>
        @enderror

        <p class="text-sm text-slate-500">
            File Excel phải có các cột:
            <code class="bg-slate-100 px-1 rounded">
                ten_thanh, ho_ten, ngay_sinh, so_dien_thoai, tao_tai_khoan
            </code>
        </p>
    </div>

    {{-- Errors --}}
    @if (!empty($errors))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <h3 class="font-semibold text-red-700 mb-2">
            Phát hiện lỗi
        </h3>

        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach ($errors as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Preview Table --}}
    @if (!empty($rows))
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full border border-slate-200">
            <thead class="bg-slate-100">
                <tr class="text-left text-sm text-slate-600">
                    <th class="px-3 py-2 border">#</th>
                    <th class="px-3 py-2 border">Tên thánh</th>
                    <th class="px-3 py-2 border">Họ tên</th>
                    <th class="px-3 py-2 border">Ngày sinh</th>
                    <th class="px-3 py-2 border">Số điện thoại</th>
                    <th class="px-3 py-2 border">Giáo họ</th>
                    <th class="px-3 py-2 border">Tạo TK</th>
                    <th class="px-3 py-2 border">Trạng thái</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($rows as $index => $row)
                <tr class="text-sm {{ $row['duplicate'] ? 'bg-red-50' : '' }}">
                    <td class="px-3 py-2 border">
                        {{ $index + 1 }}
                    </td>

                    <td class="px-3 py-2 border">
                        {{ $row['ten_thanh'] }}
                    </td>

                    <td class="px-3 py-2 border font-medium">
                        {{ $row['ho_ten'] }}
                    </td>

                    <td class="px-3 py-2 border">
                        {{ $row['ngay_sinh'] }}
                    </td>

                    <td class="px-3 py-2 border">
                        {{ $row['so_dien_thoai'] }}
                    </td>

                    <td class="px-3 py-2 border">
                        {{ $row['giao_ho'] }}
                    </td>

                    <td class="px-3 py-2 border text-center">
                        {{ $row['tao_tai_khoan'] }}
                    </td>

                    <td class="px-3 py-2 border text-center">
                        @if ($row['duplicate'])
                        <span class="text-red-600 font-semibold">
                            Trùng SĐT
                        </span>
                        @else
                        <span class="text-green-600 font-semibold">
                            OK
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Actions --}}
    @if (!empty($rows))
    <div class="flex justify-end gap-3">
        <a href="{{ route('catechists.index') }}"
            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600">
            Hủy
        </a>

        <button
            wire:click="confirmImport"
            @disabled(!$readyToImport)
            class="px-5 py-2 rounded-lg text-white
                       {{ $readyToImport
                            ? 'bg-primary-600 hover:bg-primary-700'
                            : 'bg-slate-400 cursor-not-allowed' }}">
            Xác nhận import
        </button>
    </div>
    @endif

</div>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                ['label' => 'Trang chủ', 'url' => route('dashboard')],
                ['label' => 'Quản lý giáo viên', 'url' => route('catechists.index')],
                ['label' => 'Import từ Excel', 'url' => route('catechists.import')],
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

            @if (session()->has('info'))
            <x-toast-notification type="info" :duration="3500">
                {{ session('info') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Import danh sách Giáo lý viên
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Tải lên file Excel để import hàng loạt giáo lý viên
                    </p>
                </div>

                <a href="{{ route('catechists.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                           border border-slate-300 text-slate-600 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại
                </a>
            </div>
        </div>

        {{-- Upload Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn file Excel
                    </label>

                    <input type="file"
                        wire:model="file"
                        accept=".xlsx,.csv"
                        class="block w-full text-sm text-slate-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-primary-50 file:text-primary-700
                              hover:file:bg-primary-100
                              cursor-pointer" />

                    @error('file')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="file" class="mt-2">
                        <div class="flex items-center gap-2 text-sm text-primary-600">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>Đang tải và kiểm tra file...</span>
                        </div>
                    </div>
                </div>

                {{-- Info box --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-blue-800 mb-2">
                                Yêu cầu định dạng file Excel
                            </h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• <strong>Cột bắt buộc:</strong> <code class="bg-blue-100 px-1 rounded">ho_ten</code>, <code class="bg-blue-100 px-1 rounded">so_dien_thoai</code></li>
                                <li>• <strong>Cột tùy chọn:</strong> <code class="bg-blue-100 px-1 rounded">ten_thanh</code>, <code class="bg-blue-100 px-1 rounded">ngay_sinh</code>, <code class="bg-blue-100 px-1 rounded">tao_tai_khoan</code></li>
                                <li>• <strong>Ngày sinh:</strong> Định dạng <code class="bg-blue-100 px-1 rounded">dd/mm/yyyy</code></li>
                                <li>• <strong>Tạo tài khoản:</strong> Ghi "có", "yes", "1" hoặc "x"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errors Section --}}
        @if (!empty($errors->all()))
        <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-red-800 mb-2">
                        Phát hiện {{ count($errors->all()) }} lỗi
                    </h4>
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Preview Table --}}
        @if (!empty($rows))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-lg font-semibold text-slate-900">
                    Xem trước dữ liệu ({{ count($rows) }} dòng)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Tên thánh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Họ tên</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Ngày sinh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">SĐT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Giá họ</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Tạo TK</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rows as $row)
                        <tr class="hover:bg-slate-50 {{ $row['duplicate'] ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ $row['row_number'] }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $row['ten_thanh'] ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                {{ $row['ho_ten'] }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $row['ngay_sinh'] ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $row['so_dien_thoai'] ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $row['giao_ho'] ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if(in_array(strtolower($row['tao_tai_khoan']), ['co', 'có', 'yes', '1', 'x']))
                                <span class="inline-flex items-center px-2 py-1 rounded-full
                                             text-xs font-semibold bg-green-100 text-green-700">
                                    Có
                                </span>
                                @else
                                <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($row['duplicate'])
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full
                                             text-xs font-semibold bg-red-100 text-red-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Trùng SĐT
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full
                                             text-xs font-semibold bg-green-100 text-green-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    OK
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <button
                wire:click="reset"
                class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600
                       hover:bg-slate-50 transition">
                Tải lại file khác
            </button>

            <button
                wire:click="confirmImport"
                @disabled(!$readyToImport)
                wire:loading.attr="disabled"
                wire:target="confirmImport"
                class="px-5 py-2 rounded-xl text-white font-semibold
                       transition-all inline-flex items-center gap-2
                       {{ $readyToImport
                            ? 'bg-primary-600 hover:bg-primary-700'
                            : 'bg-slate-400 cursor-not-allowed' }}">
                
                <span wire:loading.remove wire:target="confirmImport">
                    Xác nhận import
                </span>
                
                <span wire:loading wire:target="confirmImport" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Đang import...
                </span>
            </button>
        </div>
        @endif

    </div>
</div>