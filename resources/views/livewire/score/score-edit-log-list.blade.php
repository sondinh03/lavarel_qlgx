@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Kết quả học tập', 'url' => route('scores.index')],
        ['label' => 'Nhật ký sửa điểm'],
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Nhật ký sửa điểm"
                description="Theo dõi các lần thêm, sửa hoặc xóa điểm trong giáo xứ"
                icon-type="score">
                <x-slot name="actions">
                    <x-button as="a" href="{{ route('scores.index') }}" variant="outline">
                        ← Bảng điểm
                    </x-button>
                </x-slot>
            </x-page-header>

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <x-search-input
                    wire-model="search"
                    placeholder="Tìm theo học sinh, loại điểm, người sửa..."
                    debounce="400ms"
                    class="max-w-md" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header>Thời gian</x-table-header>
                            <x-table-header>Học sinh</x-table-header>
                            <x-table-header>Lớp</x-table-header>
                            <x-table-header>Loại điểm</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                            <x-table-header class="text-center">Cũ → Mới</x-table-header>
                            <x-table-header>Người sửa</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                        @php
                            $student = $log->studentClass?->student;
                            $studentName = $student
                                ? trim(($student->saint?->name ? $student->saint->name . ' ' : '') . ($student->full_name ?? ''))
                                : '—';
                        @endphp
                        <tr class="hover:bg-black/[0.02]" wire:key="log-{{ $log->id }}">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $studentName }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $log->studentClass?->catechismClass?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $log->scoreType?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $actionClass = match($log->action) {
                                        'created' => 'bg-emerald-50 text-emerald-700',
                                        'deleted' => 'bg-red-50 text-red-700',
                                        default   => 'bg-amber-50 text-amber-800',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold {{ $actionClass }}">
                                    {{ $log->actionLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums text-slate-700">
                                {{ $log->old_value !== null ? number_format((float) $log->old_value, 1) : '—' }}
                                →
                                {{ $log->new_value !== null ? number_format((float) $log->new_value, 1) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Chưa có nhật ký sửa điểm.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$logs" :per-page-options="[15, 25, 50, 100]" />
            </div>
            @endif
        </x-mac-panel>
    </div>
</div>
