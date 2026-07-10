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

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Rao hôn phối"
                description="Quản lý hồ sơ rao hôn phối"
                icon-type="default" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1 min-w-0">
                            <x-select-input
                                label="Trạng thái"
                                wire:model="statusFilter"
                                :value="$statusFilter"
                                :options="config('marriage-announcement.status')"
                                placeholder="Tất cả" />

                            <x-select-input
                                label="Năm"
                                wire:model="yearFilter"
                                :value="$yearFilter"
                                :options="collect($years)->mapWithKeys(fn ($y) => [$y => $y])->all()"
                                placeholder="Tất cả" />
                        </div>

                        <div class="flex-shrink-0 pb-0.5">
                            <x-button wire:click="resetFilters" variant="subtle">
                                <x-icon name="refresh" />
                                Đặt lại
                            </x-button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            wire-model="search"
                            placeholder="Tìm tên đôi..."
                            debounce="500ms"
                            class="max-w-md" />

                        @if($canManage)
                        <x-button as="a" href="{{ route('marriage-announcements.create') }}" variant="primary">
                            <x-icon name="plus" />
                            Tạo mới
                        </x-button>
                        @endif
                    </div>
                </div>
            </div>

            @if($announcements->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-separate border-spacing-0">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header class="w-12">STT</x-table-header>
                            <x-table-header>Tên đôi</x-table-header>
                            <x-table-header>Linh mục</x-table-header>
                            <x-table-header>Lần 1</x-table-header>
                            <x-table-header>Lần 2</x-table-header>
                            <x-table-header>Lần 3</x-table-header>
                            <x-table-header>Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($announcements as $index => $row)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="ma-{{ $row->id }}">
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
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $row->status_badge }}">
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
            <div class="mac-hairline-t">
                <x-pagination :paginator="$announcements" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @else
            <x-stats.page-empty :panel="false" tone="primary" title="Chưa có hồ sơ rao" description="Tạo hồ sơ rao hôn phối mới">
                @if($canManage)
                <x-button as="a" href="{{ route('marriage-announcements.create') }}" variant="primary">
                    <x-icon name="plus" />
                    Tạo mới
                </x-button>
                @endif
            </x-stats.page-empty>
            @endif
        </x-mac-panel>
    </div>
</div>
