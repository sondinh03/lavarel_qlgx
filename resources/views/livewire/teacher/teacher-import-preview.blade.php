@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Giáo lý viên', 'url' => route('catechists.index')],
    ['label' => 'Import danh sách'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Import Giáo lý viên từ Excel"
                description="Tải lên file Excel để thêm giáo lý viên hàng loạt"
                icon-type="students">
                <x-slot name="actions">
                    <div class="flex items-center gap-4 flex-wrap justify-end">
                        <x-button as="a"
                            href="{{ asset('templates/teacher_import_template.xlsx') }}?v={{ filemtime(public_path('templates/teacher_import_template.xlsx')) }}"
                            variant="primary"
                            size="sm">
                            <x-icon name="download" />
                            Tải file mẫu
                        </x-button>
                        <x-button as="a" href="{{ route('catechists.index') }}" variant="secondary" size="sm">
                            Quay lại
                        </x-button>
                    </div>
                </x-slot>
            </x-page-header>

            {{-- Hướng dẫn + download template --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-b space-y-3">
                <x-inline-tip tone="amber">
                    <p class="font-semibold mb-1 text-sm">Yêu cầu file Excel</p>
                    <p class="text-amber-800/90">Dùng đúng file mẫu, giữ nguyên thứ tự các cột theo tiêu đề:</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['Họ đệm', 'Tên', 'Số điện thoại'] as $col)
                        <code class="px-2 py-0.5 bg-amber-100 text-amber-900 rounded text-xs">{{ $col }}</code>
                        @endforeach
                        @foreach(['Tên thánh', 'Ngày sinh', 'Giới tính', 'Email', 'Giáo họ', 'Tạo tài khoản', 'Mã GLV'] as $col)
                        <code class="px-2 py-0.5 bg-white/70 text-amber-800 border border-amber-200 rounded text-xs">{{ $col }}</code>
                        @endforeach
                    </div>
                    <p class="mt-2 text-amber-800/90">
                        • Nhập dữ liệu bắt đầu từ dòng ngay dưới dòng mô tả; không xoá / chèn thêm dòng tiêu đề<br>
                        • <strong>Họ đệm</strong> / <strong>Tên</strong>: nhập tách riêng — vd "Nguyễn Văn" | "An"<br>
                        • <strong>Giới tính</strong>: nam / nữ<br>
                        • <strong>Ngày sinh</strong>: định dạng dd/mm/yyyy<br>
                        • <strong>Tạo tài khoản</strong>: có / không — mật khẩu mặc định = chuỗi ngày sinh <code>ddmmyyyy</code> (vd: 15/08/2000 → 15082000)<br>
                        • <strong>Tên thánh</strong>, <strong>Giáo họ</strong>: phải khớp tên trong hệ thống (nếu không khớp sẽ bỏ trống)<br>
                        • <strong>Mã GLV</strong>: để trống khi thêm mới; điền mã đã có để <strong>cập nhật</strong> thông tin<br>
                        • <strong>Tạo tài khoản</strong> khi cập nhật: nếu GLV <strong>đã có TK</strong> → bỏ qua (không đổi mật khẩu); nếu <strong>chưa có TK</strong> và ghi «có» → tạo kèm cập nhật<br>
                        • Các cột viền trắng là tùy chọn
                    </p>
                </x-inline-tip>
            </div>

            <div class="p-4 lg:p-6">
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
                           border border-black/[0.06] rounded-xl p-2 shadow-mac-sm bg-white/80">

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

            @if(!empty($fileErrors))
            <div class="mt-4 bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-2">
                            Không thể import — vui lòng sửa file và upload lại
                        </p>
                        <ul class="space-y-1">
                            @foreach($fileErrors as $err)
                            <li class="text-sm text-red-700">{!! $err !!}</li>
                            @endforeach
                        </ul>
                        <x-button wire:click="resetUpload" variant="secondary" size="sm" class="mt-3">
                            Upload lại
                        </x-button>
                    </div>
                </div>
            </div>
            @endif
            </div>
        </x-mac-panel>

        {{-- Preview table --}}
        @if(!empty($rows))
        <x-mac-panel :overflow="true">

            {{-- Preview header --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Xem trước dữ liệu
                        <span class="ml-2 text-sm font-normal text-slate-500">
                            {{ count($rows) }} giáo lý viên
                        </span>
                    </h3>
                    @if(!empty($warnings))
                    <p class="text-xs text-amber-600 mt-0.5">
                        ⚠ {{ count($warnings) }} dòng có cảnh báo — các giá trị không khớp sẽ được bỏ trống khi import
                    </p>
                    @endif
                    @if($duplicateProfileCount > 0 || $duplicateInFileCount > 0 || $duplicateInvalidCount > 0)
                    <p class="text-xs text-red-600 mt-0.5">
                        @if($duplicateProfileCount > 0)
                        {{ $duplicateProfileCount }} người đã có hồ sơ trong giáo xứ.
                        @endif
                        @if($duplicateInFileCount > 0)
                        {{ $duplicateInFileCount }} dòng lặp trong file.
                        @endif
                        @if($duplicateInvalidCount > 0)
                        {{ $duplicateInvalidCount }} dòng lỗi mã GLV.
                        @endif
                        Các dòng này sẽ bị bỏ qua khi import.
                    </p>
                    @endif
                    @php $updateCount = collect($rows)->where('will_update', true)->count(); @endphp
                    @if($updateCount > 0)
                    <p class="text-xs text-primary-700 mt-0.5">
                        {{ $updateCount }} dòng sẽ được <strong>cập nhật</strong> theo mã GLV.
                    </p>
                    @endif
                </div>

                <x-button wire:click="resetUpload" variant="secondary" size="sm">
                    <x-icon name="refresh" />
                    Upload lại
                </x-button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Dòng</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Mã GLV</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tên thánh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Họ đệm</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tên</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Ngày sinh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">GT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">SĐT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Giáo họ</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Tạo TK</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">TT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $row)
                        <tr class="{{ $row['is_duplicate'] ? 'bg-red-50' : (($row['will_update'] ?? false) ? 'bg-primary-50/50' : ($row['has_warning'] ? 'bg-amber-50' : 'hover:bg-slate-50')) }}"
                            wire:key="preview-{{ $row['row_number'] }}">
                            <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $row['row_number'] }}</td>

                            {{-- Mã GLV --}}
                            <td class="px-4 py-3 text-sm font-mono text-slate-700">{{ $row['ma_giao_ly_vien'] ?: '—' }}</td>

                            {{-- Tên thánh --}}
                            <td class="px-4 py-3 text-sm text-slate-700">
                                @if($row['ten_thanh'] && isset($warnings[$row['row_number']]) && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Tên thánh')))
                                <span class="text-amber-600 line-through">{{ $row['ten_thanh'] }}</span>
                                @else
                                {{ $row['ten_thanh'] ?: '—' }}
                                @endif
                            </td>

                            {{-- Họ đệm --}}
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row['ho_dem'] ?: '—' }}</td>

                            {{-- Tên --}}
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                @if($row['ten'])
                                {{ $row['ten'] }}
                                @else
                                <span class="text-red-600">Thiếu tên</span>
                                @endif
                            </td>

                            {{-- Ngày sinh --}}
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ngay_sinh'] ?: '—' }}</td>

                            {{-- Giới tính --}}
                            <td class="px-4 py-3">
                                @php
                                $gt = mb_strtolower($row['gioi_tinh'] ?? '', 'UTF-8');
                                $isNam = in_array($gt, ['nam', 'male', 'm', '1']);
                                @endphp
                                @if(!empty($row['gioi_tinh']))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                 {{ $isNam ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $isNam ? 'Nam' : 'Nữ' }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Email --}}
                            <td class="px-4 py-3 text-sm text-slate-600">
                                @if($row['email'] && isset($warnings[$row['row_number']]) && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Email')))
                                <span class="text-amber-600 line-through">{{ $row['email'] }}</span>
                                @else
                                {{ $row['email'] ?: '—' }}
                                @endif
                            </td>

                            {{-- SĐT --}}
                            <td class="px-4 py-3 text-sm font-mono text-slate-700">{{ $row['so_dien_thoai'] ?: '—' }}</td>

                            {{-- Giáo họ --}}
                            <td class="px-4 py-3 text-sm text-slate-600">
                                @if($row['giao_ho'] && isset($warnings[$row['row_number']]) && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Giáo họ')))
                                <span class="text-amber-600 line-through">{{ $row['giao_ho'] }}</span>
                                @else
                                {{ $row['giao_ho'] ?: '—' }}
                                @endif
                            </td>

                            {{-- Tạo TK --}}
                            <td class="px-4 py-3 text-center">
                                @php $taotk = mb_strtolower(trim($row['tao_tai_khoan'] ?? ''), 'UTF-8'); @endphp
                                @if(in_array($taotk, ['có', 'co', 'yes', '1']))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Có</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Không</span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-4 py-3 text-center">
                                @if($row['is_duplicate'])
                                <span title="Trùng hồ sơ — dòng này sẽ bị bỏ qua"
                                    class="inline-flex items-center px-2 py-0.5
                                               bg-red-100 text-red-700 rounded-full cursor-help text-xs font-semibold">
                                    Bỏ qua
                                </span>
                                @elseif($row['will_update'] ?? false)
                                <span title="Sẽ cập nhật theo mã GLV"
                                    class="inline-flex items-center px-2 py-0.5
                                               bg-primary-100 text-primary-700 rounded-full cursor-help text-xs font-semibold">
                                    Cập nhật
                                </span>
                                @elseif($row['has_warning'])
                                <span title="{{ implode(', ', $warnings[$row['row_number']] ?? []) }}"
                                    class="inline-flex items-center justify-center w-6 h-6
                                               bg-amber-100 text-amber-600 rounded-full cursor-help text-xs font-bold">
                                    !
                                </span>
                                @else
                                <svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Warning detail --}}
            @if(!empty($warnings))
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                <p class="text-xs font-semibold text-amber-800 mb-2">Chi tiết cảnh báo:</p>
                <ul class="space-y-1">
                    @foreach($warnings as $rowNum => $rowWarnings)
                    @foreach($rowWarnings as $w)
                    <li class="text-xs text-amber-700">• Dòng {{ $rowNum }}: {!! $w !!}</li>
                    @endforeach
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Action footer --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-white/40 flex items-center justify-between gap-4 flex-wrap">
                @php
                    $skipCount = $duplicateProfileCount + $duplicateInFileCount + $duplicateInvalidCount;
                    $updateCount = collect($rows)->where('will_update', true)->count();
                    $newCount = count($rows) - $skipCount - $updateCount;
                @endphp
                <p class="text-sm text-slate-600">
                    @if($newCount > 0)
                    Thêm mới <span class="font-semibold text-slate-900">{{ $newCount }}</span>
                    @endif
                    @if($updateCount > 0)
                    @if($newCount > 0) · @endif
                    Cập nhật <span class="font-semibold text-primary-700">{{ $updateCount }}</span>
                    @endif
                    @if($skipCount > 0)
                    <span class="text-red-600">— bỏ qua {{ $skipCount }} dòng</span>
                    @endif
                </p>
                <div class="flex gap-4">
                    <x-button wire:click="resetUpload" variant="secondary">
                        Hủy
                    </x-button>
                    <x-button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport"
                        wire:loading.attr="disabled" wire:target="confirmImport">
                        <x-icon name="upload" />
                        Xác nhận import
                    </x-button>
                </div>
            </div>
        </x-mac-panel>
        @endif

    </div>
</div>

{{-- Loading overlay --}}
<div wire:loading.delay class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
    <div class="bg-white/90 backdrop-blur-xl rounded-2xl p-5 flex items-center gap-3 shadow-mac border border-black/[0.06]">
        <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-medium text-gray-700">Đang xử lý...</span>
    </div>
</div>