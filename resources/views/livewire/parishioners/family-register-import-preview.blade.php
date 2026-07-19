@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Import Sổ Gia Đình'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Import Sổ Gia Đình"
                description="Nhập dữ liệu từ Sổ Gia Đình Công Giáo (2 sheet: hộ + thành viên)"
                icon-type="default">
            </x-page-header>

            <div class="px-4 lg:px-6 py-4 mac-hairline-b space-y-3">
                <x-inline-tip tone="amber">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                        <div class="flex-1">
                            <p class="font-semibold mb-1 text-sm">Cấu trúc file Excel (2 sheet)</p>
                            <p class="text-amber-800/90">Tên cột kỹ thuật ở <strong>dòng 5</strong>. Dữ liệu bắt đầu từ dòng 6.</p>
                            <ul class="mt-2 space-y-1 list-disc list-inside text-amber-800/90">
                                <li><strong>ho_gia_dinh</strong> — ma_ho_gia_dinh, ma_gia_dinh, ten_ho_gia_dinh, giao_ho, dia_chi, hôn phối (hon_phoi_*)…</li>
                                <li><strong>thanh_vien</strong> — ma_thanh_vien, ma_ho_gia_dinh, vai_tro, ho, ten, hoi_doan, bí tích (rua_toi_*, ruoc_le_*, them_suc_*)</li>
                            </ul>
                            <p class="mt-2 text-amber-800/90">
                                • <strong>ma_ho_gia_dinh</strong> / <strong>ma_thanh_vien</strong> là mã tạm để liên kết giữa 2 sheet<br>
                                • <strong>vai_tro</strong>: Chồng / Vợ / Con / Khác · <strong>gioi_tinh</strong>: Nam / Nữ<br>
                                • Hôn phối điền trên sheet <strong>ho_gia_dinh</strong>; hệ thống suy ra cặp chồng–vợ từ vai trò thành viên<br>
                                • Toàn bộ import chạy trong 1 transaction — lỗi sẽ rollback hết
                            </p>
                        </div>
                        <a href="{{ route('parishioners.import.template') }}"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2
                                   bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-semibold
                                   rounded-lg transition shadow-mac-sm">
                            <x-icon name="download" class="w-4 h-4" />
                            Tải file mẫu
                        </a>
                    </div>
                </x-inline-tip>
            </div>

            <div class="p-4 lg:p-6">
                <p class="text-sm font-semibold text-slate-700 mb-3">Upload file Excel</p>
                <input type="file" wire:model="file" accept=".xlsx,.csv"
                    class="block w-full text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4
                           file:rounded-xl file:border-0 file:text-sm file:font-semibold
                           file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100
                           cursor-pointer border border-black/[0.06] rounded-xl p-2 shadow-mac-sm bg-white/80">
                @error('file')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </x-mac-panel>

        @if(!empty($errors))
        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-5">
                <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm">
                    <p class="text-sm font-semibold text-red-800 mb-2">Lỗi — không thể import</p>
                    <ul class="space-y-1 max-h-60 overflow-y-auto">
                        @foreach($errors as $err)
                        <li class="text-sm text-red-700">{!! $err !!}</li>
                        @endforeach
                    </ul>
                    <x-button wire:click="resetUpload" variant="danger" size="sm" class="mt-3">Upload lại</x-button>
                </div>
            </div>
        </x-mac-panel>
        @endif

        @if(!empty($warnings))
        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-5">
                <div class="bg-amber-50/90 border border-amber-200/80 rounded-xl p-4 shadow-mac-sm">
                    <p class="text-sm font-semibold text-amber-800 mb-2">Cảnh báo (vẫn có thể import)</p>
                    <ul class="space-y-1 max-h-40 overflow-y-auto">
                        @foreach($warnings as $w)
                        <li class="text-sm text-amber-700">{!! $w !!}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-mac-panel>
        @endif

        @if(!empty($parishioners))
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40">
                <h3 class="text-base font-bold text-slate-900">Xem trước</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ count($families) }} hộ · {{ count($parishioners) }} giáo dân · {{ count($sacraments) }} bí tích · {{ count($marriages) }} hôn phối
                </p>
            </div>

            @if(!empty($families))
            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-slate-50/50">
                <p class="text-xs font-semibold text-slate-600 mb-2">Hộ gia đình</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($families as $family)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/80 border border-black/[0.06] text-xs shadow-mac-sm">
                        <span class="font-mono text-slate-500 mr-1.5">{{ $family['family_temp_id'] ?? '' }}</span>
                        {{ $family['name'] ?? '—' }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <x-table-header>temp_id</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header>Vai trò</x-table-header>
                            <x-table-header>GD tạm</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header>Hội đoàn</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($parishioners as $row)
                        <tr class="hover:bg-slate-50/80" wire:key="fr-{{ $row['temp_id'] }}">
                            <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ $row['temp_id'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold">{{ $row['last_name'] }} {{ $row['first_name'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ \App\Support\ParishionerEnumResolver::familyRoleLabel($row['family_role']) }}</td>
                            <td class="px-4 py-3 text-xs font-mono">{{ $row['family_temp_id'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['birthday'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['saint_name'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['association_name'] ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-slate-50/70 flex justify-between items-center gap-3">
                <x-button wire:click="resetUpload" variant="subtle">Hủy</x-button>
                <x-button wire:click="confirmImport" variant="primary"
                    wire:loading.attr="disabled" wire:target="confirmImport"
                    :disabled="!$readyToImport">
                    <x-icon name="upload" wire:loading.remove wire:target="confirmImport" />
                    Xác nhận import
                </x-button>
            </div>
        </x-mac-panel>
        @endif
    </div>
</div>

<div wire:loading.delay wire:target="confirmImport"
    class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
    <div class="bg-white/90 backdrop-blur-xl rounded-xl border border-black/[0.06] px-6 py-4 flex items-center gap-3 shadow-mac">
        <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span class="text-sm text-slate-700">Đang import...</span>
    </div>
</div>
