@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Điểm danh', 'url' => route('attendance.show')],
        ['label' => 'Nhật ký điểm danh'],
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Nhật ký điểm danh"
                description="Mỗi dòng là một đợt gửi điểm danh. Bấm Chi tiết để xem từng học sinh."
                icon-type="attendance">
                <x-slot name="actions">
                    <x-button as="a" href="{{ route('attendance.show') }}" variant="outline">
                        ← Điểm danh
                    </x-button>
                </x-slot>
            </x-page-header>

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <x-search-input
                    wire-model="search"
                    placeholder="Tìm theo học sinh, lớp, người gửi..."
                    debounce="400ms"
                    class="max-w-md" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header class="w-12 text-center">STT</x-table-header>
                            <x-table-header>Thời gian</x-table-header>
                            <x-table-header>Người gửi</x-table-header>
                            <x-table-header>Lớp / buổi</x-table-header>
                            <x-table-header class="text-center">Số học sinh</x-table-header>
                            <x-table-header class="text-right"> </x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($batches as $batch)
                        @php
                            /** @var \App\Models\AttendanceEditLog|null $sample */
                            $sample = $samples[$batch->batch_id] ?? null;
                            $session = $sample?->session;
                            $className = $session?->catechismClass?->name ?? '—';
                            $sessionLabel = $session
                                ? $session->date?->format('d/m/Y')
                                : null;
                            if ($session) {
                                $sessionLabel .= $session->type == \App\Models\AttendanceSession::TYPE_CEREMONY
                                    ? ' · Lễ'
                                    : ' · Học';
                            }
                            $extraSessions = max(0, (int) $batch->session_count - 1);
                        @endphp
                        <tr class="hover:bg-black/[0.02]" wire:key="att-batch-{{ $batch->batch_id }}">
                            <td class="px-4 py-3 text-center text-slate-400 tabular-nums text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                {{ $batch->batch_at ? \Illuminate\Support\Carbon::parse($batch->batch_at)->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $sample?->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <span class="font-medium text-slate-800">{{ $className }}</span>
                                @if($sessionLabel)
                                <span class="text-slate-400"> · {{ $sessionLabel }}</span>
                                @endif
                                @if($extraSessions > 0)
                                <span class="text-xs text-slate-400">(+{{ $extraSessions }} buổi khác)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums font-semibold text-slate-800">
                                {{ (int) $batch->changes_count }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="openBatchDetail('{{ $batch->batch_id }}')"
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                                        text-primary-700 bg-primary-50/90 ring-1 ring-primary-100/70
                                        hover:bg-primary-100/80 transition-colors">
                                    Chi tiết
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Chưa có nhật ký điểm danh.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batches->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$batches" :per-page-options="[15, 25, 50, 100]" />
            </div>
            @endif
        </x-mac-panel>
    </div>

    @if($viewingBatchId && $detailLogs->isNotEmpty())
    @php
        $first = $detailLogs->first();
        $modalSession = $first?->session;
        $modalTitle = trim(
            ($modalSession?->catechismClass?->name ?? 'Đợt điểm danh')
            . ($modalSession?->date ? ' · ' . $modalSession->date->format('d/m/Y') : '')
        );
        $multiSession = $detailLogs->pluck('session_id')->unique()->count() > 1;
    @endphp
    {{-- Modal ngoài flow layout; chiều cao + scroll dùng style inline để chắc chắn --}}
    <div
        class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        style="overscroll-behavior: contain;"
        role="dialog"
        aria-modal="true"
        wire:key="att-batch-detail-{{ $viewingBatchId }}">
        <div class="absolute inset-0 bg-black/40" wire:click="closeBatchDetail"></div>

        <div
            class="relative w-full max-w-2xl flex flex-col bg-white rounded-2xl border border-black/[0.06] shadow-mac"
            style="max-height: min(85vh, calc(100vh - 2rem - var(--bottom-offset, 0px)));">
            <div class="flex-shrink-0 px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Chi tiết đợt gửi</p>
                    <h2 class="mt-0.5 text-base font-semibold text-slate-900 truncate">{{ $modalTitle }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $first?->user?->name ?? '—' }}
                        · {{ $first?->created_at?->format('d/m/Y H:i') }}
                        · {{ $detailLogs->count() }} học sinh
                    </p>
                </div>
                <button type="button" wire:click="closeBatchDetail"
                    class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center
                        text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    aria-label="Đóng">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div style="overflow-y: auto; -webkit-overflow-scrolling: touch; min-height: 0; flex: 1 1 auto;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-slate-500 w-12">STT</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500">Học sinh</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($detailLogs as $log)
                        @php
                            $student = $log->student;
                            $studentName = $student
                                ? trim(($student->saint?->name ? $student->saint->name . ' ' : '') . ($student->full_name ?? ''))
                                : '—';
                            $oldLabel = \App\Models\AttendanceEditLog::statusLabel($log->old_status);
                            $newLabel = \App\Models\AttendanceEditLog::statusLabel($log->new_status);
                        @endphp
                        <tr wire:key="att-detail-{{ $log->id }}">
                            <td class="px-3 py-2.5 text-center text-slate-400 tabular-nums text-xs align-top">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-slate-900 align-top">
                                {{ $studentName }}
                                @if($multiSession)
                                <span class="block text-[11px] font-normal text-slate-400">
                                    {{ $log->session?->date?->format('d/m') }}
                                    {{ $log->session?->type == \App\Models\AttendanceSession::TYPE_CEREMONY ? '· Lễ' : '· Học' }}
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 align-top">
                                @if($log->old_status === null)
                                <span class="tabular-nums">{{ $newLabel }}</span>
                                @else
                                <span class="tabular-nums text-slate-400">{{ $oldLabel }}</span>
                                <span class="mx-1 text-slate-300">→</span>
                                <span class="tabular-nums font-medium text-slate-800">{{ $newLabel }}</span>
                                @endif
                                @if(($log->old_note || $log->new_note) && (string) $log->old_note !== (string) $log->new_note)
                                <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">
                                    Ghi chú:
                                    @if($log->old_note)
                                        {{ $log->old_note }} → {{ $log->new_note ?: '—' }}
                                    @else
                                        {{ $log->new_note }}
                                    @endif
                                </p>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @elseif($viewingBatchId)
    <div class="fixed inset-0 z-[200]" wire:click="closeBatchDetail"></div>
    @endif
</div>
