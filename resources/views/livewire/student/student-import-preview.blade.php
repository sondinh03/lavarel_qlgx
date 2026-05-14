@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Học sinh', 'url' => route('students.index')],
        ['label' => 'Import học sinh'],
    ]" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    <div class="mx-auto max-w-6xl space-y-5">
        {{-- Page header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Import học sinh từ Excel"
                :description="$this->className ? 'Lớp: ' . $this->className : 'Tải lên file Excel để ghi danh hàng loạt'"
                icon-type="students">
            </x-page-header>

            {{-- Hướng dẫn + download template --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-amber-50/60">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm text-amber-800">
                        <p class="font-semibold mb-1">Yêu cầu file Excel</p>
                        <p>File phải có các cột (theo thứ tự không quan trọng, tên cột phải khớp chính xác):</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            {{-- Cột bắt buộc --}}
                            @foreach(['ho_ten_dem', 'ten', 'ngay_sinh', 'gioi_tinh'] as $col)
                            <code class="px-2 py-0.5 bg-amber-100 text-amber-900 rounded text-xs font-mono">{{ $col }}</code>
                            @endforeach
                            {{-- Cột tùy chọn --}}
                            @foreach(['ten_thanh', 'giao_ho', 'ho_ten_bo', 'ho_ten_me', 'so_dien_thoai', 'email', 'ghi_chu'] as $col)
                            <code class="px-2 py-0.5 bg-white/70 text-amber-800 border border-amber-200 rounded text-xs font-mono">{{ $col }}</code>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-amber-700">
                            • <strong>gioi_tinh</strong>: nam / nữ (hoặc male / female)<br>
                            • <strong>ngay_sinh</strong>: định dạng dd/mm/yyyy<br>
                            • <strong>ten_thanh</strong>, <strong>giao_ho</strong>: phải khớp tên trong hệ thống (nếu không khớp sẽ bỏ trống)<br>
                            • <strong>so_dien_thoai</strong>: 9–11 chữ số<br>
                            • <strong>email</strong>: định dạng email hợp lệ<br>
                            • Các cột viền trắng là tùy chọn
                        </p>
                    </div>
                    <a href="{{ asset('templates/student_import_template.xlsx') }}?v={{ filemtime(public_path('templates/student_import_template.xlsx')) }}"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2
                               bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-semibold
                               rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Tải file mẫu
                    </a>
                </div>
            </div>
        </div>

        {{-- Upload form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">

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

            <hr class="border-slate-200">

            <div>
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
                               border border-slate-300 rounded-xl p-2
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

        {{-- Lỗi block --}}
        @if(!empty($errors))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
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
                        @foreach($errors as $err)
                        <li class="text-sm text-red-700">{!! $err !!}</li>
                        @endforeach
                    </ul>
                    <button wire:click="resetUpload" type="button"
                        class="mt-3 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition">
                        Upload lại
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Preview table --}}
        @if(!empty($rows))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Preview header --}}
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
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

                <button wire:click="resetUpload" type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-2
                               text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Upload lại
                </button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50">
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
                        <tr class="{{ $row['is_duplicate'] ? 'bg-slate-100' : ($row['has_warning'] ? 'bg-amber-50' : 'hover:bg-slate-50') }} {{ $row['is_duplicate'] ? 'opacity-60' : '' }}"
                            wire:key="preview-{{ $row['row_number'] }}">

                            <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row['ma_hoc_sinh'] ?: '—' }}</td>
                            {{-- Tên thánh --}}
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

                            {{-- Giới tính --}}
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

                            {{-- Giáo họ --}}
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

                            {{-- Số điện thoại --}}
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

                            {{-- Email --}}
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

                            {{-- Trạng thái --}}
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
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="text-sm text-slate-600 space-y-0.5">
                    Sẽ thêm mới
                    <span class="font-semibold text-slate-900">{{ $this->newCount }} học sinh</span>
                    @if($this->updateCount > 0)
                    và cập nhật <span class="font-semibold text-primary-700">{{ $this->updateCount }} học sinh</span>
                    @endif

                    @if($this->duplicateCount > 0)
                    <p class="text-xs text-slate-400">
                        Bỏ qua {{ $this->duplicateCount }} học sinh trùng hoặc không hợp lệ
                    </p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button wire:click="resetUpload" type="button"
                        class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold
                                   rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                        Hủy
                    </button>
                    <button wire:click="confirmImport" type="button"
                        @disabled(!$readyToImport)
                        wire:loading.attr="disabled"
                        class="px-4 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white
                                   text-sm font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700
                                   active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed
                                   inline-flex items-center gap-2">
                        <svg wire:loading wire:target="confirmImport"
                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="confirmImport"
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Xác nhận import
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>