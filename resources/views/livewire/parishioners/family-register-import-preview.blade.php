@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Import Sổ Gia Đình'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Import Sổ Gia Đình"
                description="Nhập dữ liệu từ Sổ Gia Đình Công Giáo (3 sheet + temp_id)"
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
                            <p class="font-semibold mb-1">Cấu trúc file Excel</p>
                            <p>File gồm 3 sheet dữ liệu (tên cột kỹ thuật ở <strong>dòng 5</strong>):</p>
                            <ul class="mt-2 text-xs space-y-1 list-disc list-inside">
                                <li><strong>parishioners</strong> — temp_id, family_temp_id, family_role, last_name, first_name, gender, birthday, birth_place...</li>
                                <li><strong>sacraments</strong> — parishioner_temp_id, type, received_date...</li>
                                <li><strong>marriages</strong> — husband_temp_id, wife_temp_id, married_date, status...</li>
                            </ul>
                            <p class="mt-2 text-xs text-amber-700">
                                • Dùng <strong>temp_id</strong> (P001, P002...) để liên kết giữa các sheet<br>
                                • <strong>family_role</strong>: husband / wife / child / other<br>
                                • <strong>type</strong>: baptism / communion / confirmation / anointing / holy_orders<br>
                                • Toàn bộ import chạy trong 1 transaction — lỗi sẽ rollback hết
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('parishioners.import.family-register.template') }}"
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
            <input type="file" wire:model="file" accept=".xlsx,.csv"
                class="block w-full text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4
                       file:rounded-xl file:border-0 file:text-sm file:font-semibold
                       file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100
                       cursor-pointer border border-slate-300 rounded-xl p-2">
            @error('file')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        @if(!empty($errors))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
            <p class="text-sm font-semibold text-red-800 mb-2">Lỗi — không thể import</p>
            <ul class="space-y-1 max-h-60 overflow-y-auto">
                @foreach($errors as $err)
                <li class="text-sm text-red-700">{!! $err !!}</li>
                @endforeach
            </ul>
            <x-button wire:click="resetUpload" variant="danger" size="sm" class="mt-3">Upload lại</x-button>
        </div>
        @endif

        @if(!empty($warnings))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <p class="text-sm font-semibold text-amber-800 mb-2">Cảnh báo (vẫn có thể import)</p>
            <ul class="space-y-1 max-h-40 overflow-y-auto">
                @foreach($warnings as $w)
                <li class="text-sm text-amber-700">{!! $w !!}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($parishioners))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200">
                <h3 class="text-base font-bold text-slate-900">Xem trước</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ count($parishioners) }} giáo dân · {{ count($sacraments) }} bí tích · {{ count($marriages) }} hôn phối
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50">
                        <tr>
                            <x-table-header>temp_id</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header>Vai trò</x-table-header>
                            <x-table-header>GD tạm</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($parishioners as $row)
                        <tr class="hover:bg-slate-50" wire:key="fr-{{ $row['temp_id'] }}">
                            <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ $row['temp_id'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold">{{ $row['last_name'] }} {{ $row['first_name'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['family_role'] }}</td>
                            <td class="px-4 py-3 text-xs font-mono">{{ $row['family_temp_id'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['birthday'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['saint_name'] ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 lg:px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center gap-3">
                <x-button wire:click="resetUpload" variant="subtle">Hủy</x-button>
                <x-button wire:click="confirmImport" variant="primary"
                    wire:loading.attr="disabled" wire:target="confirmImport"
                    :disabled="!$readyToImport">
                    <x-icon name="upload" wire:loading.remove wire:target="confirmImport" />
                    Xác nhận import
                </x-button>
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
