@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Import danh sách'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Import giáo dân từ Excel"
                description="Tải lên file Excel để thêm giáo dân hàng loạt"
                icon-type="default">
            </x-page-header>

            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-amber-50/60">
                <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                    <div class="flex items-start gap-3 flex-1">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-semibold mb-1">Yêu cầu file Excel</p>
                            <p>File phải có sheet <strong>GiaoDan</strong> (bắt buộc) và sheet <strong>BiTichHonPhoi</strong> (tùy chọn). Tên cột kỹ thuật ở dòng 5 phải khớp chính xác.</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(['ho_ten_dem', 'ten', 'gioi_tinh'] as $col)
                                <code class="px-2 py-0.5 bg-amber-100 text-amber-900 rounded text-xs font-mono">{{ $col }}</code>
                                @endforeach
                                @foreach(['ten_thanh', 'ngay_sinh', 'giao_ho', 'so_dien_thoai', 'email', 'cccd', 'ho_ten_bo', 'ho_ten_me', 'tinh_trang_hon_nhan', 'tan_tong', 'ghi_chu', 'que_quan', 'dia_chi_thuong_tru', 'tinh_thuong_tru', 'con_thu', 'dan_toc', 'nghe_nghiep', 'trinh_do_hoc_van', 'trinh_do_chuyen_mon', 'trinh_do_giao_ly', 'chuc_vu', 'cap_bac', 'xa_thuong_tru', 'dia_chi_tam_tru', 'tinh_tam_tru', 'ngay_gia_nhap', 'ngay_mat', 'so_so_mat', 'noi_an_tang', 'rua_toi_ngay', 'rua_toi_so', 'rua_toi_nguoi_ban', 'rua_toi_dau_dau', 'rua_toi_giao_xu', 'ruoc_le_ngay', 'ruoc_le_so', 'ruoc_le_nguoi_ban', 'ruoc_le_giao_xu', 'them_suc_ngay', 'them_suc_so', 'them_suc_nguoi_ban', 'them_suc_dau_dau', 'them_suc_giao_xu'] as $col)
                                <code class="px-2 py-0.5 bg-white/70 text-amber-800 border border-amber-200 rounded text-xs font-mono">{{ $col }}</code>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-amber-700">
                                • <strong>Bắt buộc</strong>: ho_ten_dem, ten, gioi_tinh<br>
                                • <strong>gioi_tinh</strong>: nam / nữ · <strong>ngay_sinh</strong>: dd/mm/yyyy<br>
                                • <strong>tinh_trang_hon_nhan</strong>: độc thân / đã kết hôn / góa / ly hôn · <strong>tan_tong</strong>: có / không<br>
                                • Sheet <strong>GiaoDan</strong>: bí tích rửa tội, rước lễ, thêm sức (cột rua_toi_*, ruoc_le_*, them_suc_*)<br>
                                • Sheet <strong>BiTichHonPhoi</strong> (tùy chọn): xức dầu và hôn phối — khớp theo ho_ten_dem + ten + ngay_sinh<br>
                                • Giáo dân trùng (CCCD, SĐT hoặc họ tên + ngày sinh) sẽ được bỏ qua khi import
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('parishioners.import.template') }}"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2
                               bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-semibold
                               rounded-lg transition">
                        <x-icon name="download" class="w-4 h-4" />
                        Tải file mẫu
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6">
            <p class="text-sm font-semibold text-slate-700 mb-3">Upload file Excel</p>

            <div class="relative">
                <input
                    type="file"
                    wire:model="file"
                    accept=".xlsx,.csv"
                    class="block w-full text-sm text-slate-700
                           file:mr-4 file:py-2.5 file:px-4
                           file:rounded-xl file:border-0
                           file:text-sm file:font-semibold
                           file:bg-primary-50 file:text-primary-700
                           hover:file:bg-primary-100 cursor-pointer
                           border border-slate-300 rounded-xl p-2">

                <div wire:loading wire:target="file"
                    class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-xl">
                    <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="ml-2 text-sm text-slate-600">Đang tải lên...</span>
                </div>
            </div>

            @error('file')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        @if(!empty($fileErrors))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <x-icon name="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-semibold text-red-800 mb-2">
                        Không thể import — vui lòng sửa file và upload lại
                    </p>
                    <ul class="space-y-1">
                        @foreach($fileErrors as $err)
                        <li class="text-sm text-red-700">{!! $err !!}</li>
                        @endforeach
                    </ul>
                    <x-button wire:click="resetUpload" variant="danger" size="sm" class="mt-3">
                        Upload lại
                    </x-button>
                </div>
            </div>
        </div>
        @endif

        @if(!empty($rows))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Xem trước dữ liệu
                        <span class="ml-2 text-sm font-normal text-slate-500">{{ count($rows) }} dòng</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Sẽ thêm mới <strong class="text-slate-800">{{ $this->newCount }}</strong> giáo dân
                        @if($this->duplicateCount > 0)
                        · Bỏ qua <strong class="text-red-600">{{ $this->duplicateCount }}</strong> trùng
                        @endif
                    </p>
                    @if(!empty($warnings))
                    <p class="text-xs text-amber-600 mt-0.5">
                        {{ count($warnings) }} dòng có cảnh báo — giá trị không khớp sẽ được bỏ trống khi import
                    </p>
                    @endif
                </div>

                <x-button wire:click="resetUpload" variant="subtle">
                    <x-icon name="refresh" />
                    Upload lại
                </x-button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>Dòng</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header class="text-center">GT</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header>SĐT</x-table-header>
                            <x-table-header>Email</x-table-header>
                            <x-table-header class="text-center">TT</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $row)
                        <tr class="{{ $row['is_duplicate'] ? 'bg-red-50' : ($row['has_warning'] ? 'bg-amber-50' : 'hover:bg-slate-50') }}"
                            wire:key="preview-{{ $row['row_number'] }}">
                            <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row['ten_thanh'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                {{ trim($row['ho_ten_dem'] . ' ' . $row['ten']) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ngay_sinh'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                $gt = mb_strtolower($row['gioi_tinh'] ?? '', 'UTF-8');
                                $isNam = in_array($gt, ['nam', 'male', 'm', '1'], true);
                                @endphp
                                @if(!empty($row['gioi_tinh']))
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $isNam ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $isNam ? 'Nam' : 'Nữ' }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['giao_ho'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-700">{{ $row['so_dien_thoai'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['email'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($row['is_duplicate'])
                                <span title="Giáo dân đã tồn tại"
                                    class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded-full cursor-help text-xs font-bold">!</span>
                                @elseif($row['has_warning'])
                                <span title="{{ implode(', ', array_map('strip_tags', $warnings[$row['row_number']] ?? [])) }}"
                                    class="inline-flex items-center justify-center w-6 h-6 bg-amber-100 text-amber-600 rounded-full cursor-help text-xs font-bold">!</span>
                                @else
                                <x-icon name="check" class="w-4 h-4 text-green-500 mx-auto" />
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!empty($warnings))
            <div class="px-4 lg:px-6 py-4 border-t border-amber-200 bg-amber-50">
                <p class="text-xs font-semibold text-amber-800 mb-2">Chi tiết cảnh báo:</p>
                <ul class="space-y-1 max-h-40 overflow-y-auto">
                    @foreach($warnings as $rowNum => $rowWarnings)
                    @foreach($rowWarnings as $w)
                    <li class="text-xs text-amber-700">• Dòng {{ $rowNum }}: {!! $w !!}</li>
                    @endforeach
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="px-4 lg:px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-slate-600">
                    Sẽ import <span class="font-semibold text-slate-900">{{ $this->newCount }} giáo dân mới</span>
                </p>
                <div class="flex gap-3">
                    <x-button wire:click="resetUpload" variant="subtle">Hủy</x-button>
                    <x-button wire:click="confirmImport"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="confirmImport"
                        :disabled="!$readyToImport || $this->newCount === 0">
                        <x-icon name="upload" wire:loading.remove wire:target="confirmImport" />
                        <x-icon name="refresh" class="animate-spin" wire:loading wire:target="confirmImport" />
                        Xác nhận import
                    </x-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<div wire:loading.delay wire:target="confirmImport"
    class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl px-6 py-4 flex items-center gap-3 shadow-lg">
        <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span class="text-sm text-slate-700">Đang import...</span>
    </div>
</div>
