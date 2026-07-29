{{-- Tab cấu hình loại điểm: hiện cả loại đã tắt để bật lại được --}}
@if(!$selectedNamHoc)
<x-stats.page-empty
    :panel="false"
    tone="slate"
    title="Vui lòng chọn năm học"
    description="Chọn năm học để cấu hình loại điểm" />
@else
@if(!$selectedLop)
<div class="mx-4 lg:mx-6 my-4 px-4 py-3 rounded-xl border border-amber-200/80 bg-amber-50/80 text-sm text-amber-800 shadow-mac-sm">
    Chưa chọn lớp cụ thể — loại điểm sẽ được tạo theo <strong>khối</strong> hoặc <strong>toàn xứ</strong>.
    Chọn lớp ở trên nếu muốn cấu hình riêng từng lớp.
</div>
@endif

@if($scoreTypes->isNotEmpty())
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50/50 mac-hairline-b">
            <tr>
                <x-table-header>Tên loại điểm</x-table-header>
                <x-table-header>Loại</x-table-header>
                <x-table-header class="text-center">Thứ tự</x-table-header>
                <x-table-header class="text-center">Hệ số</x-table-header>
                <x-table-header class="text-center">Điểm tối đa</x-table-header>
                <x-table-header class="text-center">Trạng thái</x-table-header>
                <x-table-header class="text-center">Thao tác</x-table-header>
            </tr>
        </thead>
        <tbody class="divide-y divide-black/[0.04]">
            @foreach($scoreTypes as $st)
            <tr class="hover:bg-black/[0.03] transition-colors" wire:key="st-{{ $st->id }}">
                <td class="px-4 py-3 font-semibold text-slate-900">{{ $st->name }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50/80 text-indigo-700">
                        {{ $st->type_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $st->order }}</td>
                <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $st->coefficient }}</td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $st->max_score }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-lg
                        {{ $st->is_active ? 'bg-emerald-50/80 text-emerald-700' : 'bg-slate-100/80 text-slate-500' }}">
                        {{ $st->is_active ? 'Đang dùng' : 'Tắt' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="inline-flex items-center gap-3">
                        <x-tooltip content="Chỉnh sửa">
                            <x-table-action wire="editScoreType({{ $st->id }})" icon="edit" />
                        </x-tooltip>
                        <span class="text-slate-300">|</span>
                        <x-tooltip content="Xóa">
                            <x-table-action
                                wire="delete({{ $st->id }})"
                                icon="trash"
                                color="danger"
                                :loading="true"
                                confirm="Bạn có chắc chắn muốn xóa loại điểm '{{ $st->name }}'?" />
                        </x-tooltip>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<x-stats.page-empty
    :panel="false"
    tone="primary"
    title="Chưa có loại điểm"
    description="Thêm loại điểm đầu tiên cho phạm vi đã chọn">
    <x-button wire:click="createScoreType" variant="primary">
        <x-icon name="plus" />
        Thêm loại điểm
    </x-button>
</x-stats.page-empty>
@endif
@endif
