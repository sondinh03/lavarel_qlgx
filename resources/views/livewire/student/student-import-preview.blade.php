@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Học sinh', 'url' => route('students.index')],
        ['label' => 'Import học sinh'],
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Import học sinh từ Excel"
                :description="$this->className ? 'Lớp: ' . $this->className : 'Tải lên file Excel để ghi danh hàng loạt'"
                icon-type="students">
                <x-slot name="actions">
                    <div class="flex items-center gap-4 flex-wrap justify-end">
                        <x-button as="a"
                            href="{{ asset('templates/student_import_template.xlsx') }}?v={{ filemtime(public_path('templates/student_import_template.xlsx')) }}"
                            variant="primary"
                            size="sm">
                            <x-icon name="download" />
                            Tải file mẫu
                        </x-button>
                        <x-button as="a" href="{{ route('students.index') }}" variant="secondary" size="sm">
                            Quay lại
                        </x-button>
                    </div>
                </x-slot>
            </x-page-header>

            {{-- Hướng dẫn + download template --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-b space-y-3">
                <x-inline-tip tone="amber">
                    <p class="font-semibold mb-1 text-sm">Yêu cầu file Excel</p>
                    <p class="text-amber-800/90">File phải có các cột (thứ tự không quan trọng, tên cột khớp chính xác):</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['ho_ten_dem', 'ten', 'ngay_sinh', 'gioi_tinh'] as $col)
                        <code class="px-2 py-0.5 bg-amber-100 text-amber-900 rounded text-xs font-mono">{{ $col }}</code>
                        @endforeach
                        @foreach(['ten_thanh', 'giao_ho', 'ho_ten_bo', 'ho_ten_me', 'so_dien_thoai', 'email', 'ghi_chu'] as $col)
                        <code class="px-2 py-0.5 bg-white/70 text-amber-800 border border-amber-200 rounded text-xs font-mono">{{ $col }}</code>
                        @endforeach
                    </div>
                    <p class="mt-2 text-amber-800/90">
                        • <strong>gioi_tinh</strong>: nam / nữ (hoặc male / female)<br>
                        • <strong>ngay_sinh</strong>: định dạng dd/mm/yyyy<br>
                        • <strong>ten_thanh</strong>, <strong>giao_ho</strong>: phải khớp tên trong hệ thống (nếu không khớp sẽ bỏ trống)<br>
                        • <strong>so_dien_thoai</strong>: 9–11 chữ số<br>
                        • <strong>email</strong>: định dạng email hợp lệ<br>
                        • Các cột viền trắng là tùy chọn
                    </p>
                </x-inline-tip>

                @if(!$selectedLop)
                <x-inline-tip>
                    <strong>Bước 1 bắt buộc:</strong> chọn lớp ở bên dưới trước khi upload. Chưa chọn lớp thì không thể tải file.
                </x-inline-tip>
                @endif
            </div>

            <div class="p-4 lg:p-6 space-y-5">
                <div>
                    <p class="text-sm font-semibold text-slate-700 mb-3">
                        Bước 1: Chọn lớp cần import vào
                    </p>
                    <livewire:filters.filter-bar
                        :parish-id="$parishId"
                        :show-nam-hoc="true"
                        :show-khoi="true"
                        :show-lop="true"
                        :show-ky="false"
                        :selected-nam-hoc="$selectedNamHoc"
                        :selected-khoi="$selectedKhoi"
                        :selected-lop="$selectedLop" />

                    @error('selectedLop')
                    <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div class="mac-hairline-t pt-5">
                    <p class="text-sm font-semibold text-slate-700 mb-3">
                        Bước 2: Upload file Excel
                        @if(!$selectedLop)
                        <span class="text-slate-400 font-normal">(chọn lớp trước)</span>
                        @endif
                    </p>

                    <div class="relative">
                        <input
                            type="file"
                            wire:model="file"
                            accept=".xlsx,.csv"
                            @disabled(!$selectedLop)
                            class="block w-full text-sm text-slate-700
                                   file:mr-4 file:py-2.5 file:px-4
                                   file:rounded-xl file:border-0
                                   file:text-sm file:font-semibold
                                   file:bg-primary-50 file:text-primary-700
                                   hover:file:bg-primary-100 cursor-pointer
                                   border border-black/[0.06] rounded-xl p-2 shadow-mac-sm bg-white/80
                                   disabled:opacity-50 disabled:cursor-not-allowed">

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
            </div>
        </x-mac-panel>

        {{-- Lỗi file upload --}}
        @if(!empty($fileErrors))
        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-5">
                <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm">
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
            </div>
        </x-mac-panel>
        @endif

        {{-- Preview table --}}
        @if(!empty($rows))
        <x-mac-panel :overflow="true">

            {{-- Preview header --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Xem trước dữ liệu
                        <span class="ml-2 text-sm font-normal text-slate-500">
                            {{ count($rows) }} học sinh
                        </span>
                    </h3>
                    @if(!empty($warnings))
                    <p class="text-xs text-amber-600 mt-0.5">
                        ⚠ {{ count($warnings) }} dòng có cảnh báo
                        @if($this->realWarningCount > 0)
                            — {{ $this->realWarningCount }} dòng có giá trị không hợp lệ sẽ được bỏ trống khi import
                        @endif
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
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Dòng</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Mã học sinh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Tên thánh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Họ tên đệm</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Tên</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ngày sinh</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">GT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Giáo họ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Bố</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Mẹ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">SĐT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ghi chú</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide">TT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $row)
                        <tr class="{{ $row['is_duplicate'] ? 'bg-slate-100' : ($row['has_warning'] ? 'bg-amber-50' : 'hover:bg-slate-50/80') }} {{ $row['is_duplicate'] ? 'opacity-60' : '' }}"
                            wire:key="preview-{{ $row['row_number'] }}">

                            <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row['ma_hoc_sinh'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                @php
                                $tenThanhWarning = isset($warnings[$row['row_number']])
                                && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Tên thánh'));
                                @endphp
                                @if($row['ten_thanh'] && $tenThanhWarning)
                                <span class="text-amber-600 line-through">{{ $row['ten_thanh'] }}</span>
                                @else
                                {{ $row['ten_thanh'] ?: '—' }}
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $row['ho_ten_dem'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $row['ten'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ngay_sinh'] ?: '—' }}</td>

                            <td class="px-4 py-3">
                                @php
                                $gt = strtolower($row['gioi_tinh'] ?? '');
                                $isNam = in_array($gt, ['nam', 'male', 'm', '1']);
                                $gtWarning = isset($warnings[$row['row_number']])
                                && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Giới tính'));
                                @endphp
                                @if($gtWarning)
                                <span class="text-amber-600 text-xs">{{ $row['gioi_tinh'] }} (?)</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                         {{ $isNam ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $isNam ? 'Nam' : 'Nữ' }}
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                @php
                                $giaoHoWarning = isset($warnings[$row['row_number']])
                                && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Giáo họ'));
                                @endphp
                                @if($row['giao_ho'] && $giaoHoWarning)
                                <span class="text-amber-600 line-through">{{ $row['giao_ho'] }}</span>
                                @else
                                {{ $row['giao_ho'] ?: '—' }}
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ho_ten_bo'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ho_ten_me'] ?: '—' }}</td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                @php
                                $sdtWarning = isset($warnings[$row['row_number']])
                                && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Số điện thoại'));
                                @endphp
                                @if($row['so_dien_thoai'] && $sdtWarning)
                                <span class="text-amber-600 line-through">{{ $row['so_dien_thoai'] }}</span>
                                @else
                                {{ $row['so_dien_thoai'] ?: '—' }}
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                @php
                                $emailWarning = isset($warnings[$row['row_number']])
                                && collect($warnings[$row['row_number']])->contains(fn($w) => str_contains($w, 'Email'));
                                @endphp
                                @if($row['email'] && $emailWarning)
                                <span class="text-amber-600 line-through">{{ $row['email'] }}</span>
                                @else
                                {{ $row['email'] ?: '—' }}
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">{{ $row['ghi_chu'] ?: '—' }}</td>

                            <td class="px-4 py-3 text-center">
                                @if($row['is_duplicate'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                    text-xs font-medium bg-slate-200 text-slate-500">
                                    Bỏ qua
                                </span>
                                @elseif($row['will_update'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                    text-xs font-medium bg-primary-100 text-primary-700">
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

            {{-- Chi tiết cảnh báo --}}
            @if(!empty($warnings))
            <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-amber-50/60">
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
                <div class="text-sm text-slate-600 space-y-0.5">
                    Sẽ thêm mới
                    <span class="font-semibold text-slate-900">{{ $this->newCount }} học sinh</span>
                    @if($this->updateCount > 0)
                    và cập nhật <span class="font-semibold text-primary-700">{{ $this->updateCount }} học sinh</span>
                    @endif

                    @if($this->duplicateCount > 0)
                    <p class="text-xs text-slate-400">
                        Bỏ qua {{ $this->duplicateCount }} dòng
                        @if($duplicateProfileCount > 0)
                        ({{ $duplicateProfileCount }} đã có hồ sơ trong giáo xứ
                        @if($duplicateInvalidCount > 0), {{ $duplicateInvalidCount }} lỗi mã/không hợp lệ@endif)
                        @elseif($duplicateInvalidCount > 0)
                        ({{ $duplicateInvalidCount }} lỗi mã/không hợp lệ)
                        @endif
                        — xem chi tiết bên dưới
                    </p>
                    @endif
                </div>
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
