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