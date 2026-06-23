@php
$formatDate = function (?string $value) {
    if (!$value) return '—';
    try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) return $value;
        return \Carbon\Carbon::parse($value)->format('d/m/Y');
    } catch (\Throwable) {
        return $value;
    }
};
@endphp

@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Rao hôn phối'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header class="rounded-t-2xl" title="Rao hôn phối" description="Quản lý hồ sơ rao hôn phối"
                :stat-value="$announcements->total()" stat-label="Hồ sơ" icon-type="default">
                <x-slot name="actions">
                    @if($canManage)
                    <x-button as="a" href="{{ route('marriage-announcements.create') }}" variant="primary">
                        <x-icon name="plus" /> Tạo mới
                    </x-button>
                    @endif
                </x-slot>
            </x-page-header>

            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 flex flex-col lg:flex-row gap-3 lg:items-end lg:justify-between">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="min-w-[140px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Trạng thái</label>
                        <select wire:model="statusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                            <option value="">Tất cả</option>
                            @foreach(config('marriage-announcement.status') as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[120px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Năm</label>
                        <select wire:model="yearFilter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                            <option value="">Tất cả</option>
                            @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-search-input wireModel="search" placeholder="Tìm tên đôi..." class="max-w-sm" />
            </div>
        </div>

        @if($announcements->count())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 w-12">STT</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Tên đôi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Linh mục</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Lần 1</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Lần 2</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Lần 3</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Trạng thái</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($announcements as $index => $row)
                        <tr class="hover:bg-slate-50" wire:key="ma-{{ $row->id }}">
                            <td class="px-4 py-3 text-slate-400">{{ ($announcements->firstItem() ?? 0) + $index }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('marriage-announcements.show', $row->id) }}" class="font-semibold text-primary-600 hover:text-primary-700">
                                    {{ $row->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $row->assignedPriest?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $formatDate($row->announcements_one) }}</td>
                            <td class="px-4 py-3">{{ $formatDate($row->announcements_two) }}</td>
                            <td class="px-4 py-3">{{ $formatDate($row->announcements_three) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $row->status_badge }}">
                                    {{ $row->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <a href="{{ route('marriage-announcements.show', $row->id) }}" class="px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Xem</a>
                                    @if($canManage)
                                    <a href="{{ route('marriage-announcements.edit', $row->id) }}" class="px-2 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded-lg">Sửa</a>
                                    @if((int)$row->status === 1)
                                    <a href="{{ route('marriage-announcements.create-marriage', $row->id) }}" class="px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 rounded-lg">Tạo HP</a>
                                    @endif
                                    <button type="button" wire:click="delete({{ $row->id }})"
                                        onclick="return confirm('Xóa hồ sơ này?')"
                                        class="px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg">Xóa</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($announcements->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $announcements->links() }}</div>
            @endif
        </div>
        @else
        <x-stats.page-empty tone="primary" title="Chưa có hồ sơ rao" description="Tạo hồ sơ rao hôn phối mới">
            @if($canManage)
            <x-action-button as="a" href="{{ route('marriage-announcements.create') }}" icon="plus">Tạo mới</x-action-button>
            @endif
        </x-stats.page-empty>
        @endif
    </div>
</div>
