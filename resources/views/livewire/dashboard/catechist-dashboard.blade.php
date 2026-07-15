@push('page-title')
<span>Trang chủ</span>
@endpush

<div class="min-h-screen bg-apple-gray p-3 sm:p-4"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-2xl space-y-4">

        <x-mac-panel class="px-5 py-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Giáo lý viên</p>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">
                Xin chào, {{ auth()->user()->name }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">{{ $todayLabel }}</p>
            @if($activeSchoolYear)
            <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-primary-700
                bg-primary-50/90 ring-1 ring-primary-100/70 rounded-lg px-2.5 py-1">
                Năm học {{ $activeSchoolYear->name }}
            </p>
            @endif
        </x-mac-panel>

        @if($pendingTodayCount > 0)
        <a href="{{ route('attendance.show') }}"
            class="flex items-start gap-3 px-4 py-4 rounded-2xl
                bg-amber-50/90 border border-amber-200/80 shadow-mac-sm
                hover:bg-amber-100/80 transition-all touch-feedback group">
            <div class="w-10 h-10 rounded-xl bg-amber-100/90 ring-1 ring-amber-200/70
                flex items-center justify-center flex-shrink-0 text-amber-700 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-amber-950">
                    Có {{ $pendingTodayCount }} phiên cần điểm danh hôm nay
                </p>
                <p class="text-xs text-amber-800/70 mt-0.5">
                    Nhấn để mở màn điểm danh
                </p>
            </div>
            <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-1 group-hover:text-amber-600"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        @endif

        {{-- Từ ban giáo lý --}}
        @if($boardAnnouncements->isNotEmpty())
        <section class="space-y-2.5">
            <div class="flex items-baseline justify-between px-1">
                <h2 class="text-xs font-semibold text-primary-700 uppercase tracking-wide">
                    Từ ban giáo lý
                </h2>
                <a href="{{ route('notifications.index') }}"
                    class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                    Xem tất cả →
                </a>
            </div>
            <div class="space-y-2">
                @foreach($boardAnnouncements as $notification)
                @php
                    $data = $notification->data ?? [];
                    $unread = $notification->unread();
                @endphp
                <button type="button"
                    wire:click="openHighlightedNotification('{{ $notification->id }}')"
                    class="w-full text-left flex items-start gap-3 px-4 py-3.5 rounded-2xl transition-all touch-feedback
                        {{ $unread
                            ? 'bg-primary-50/90 border border-primary-200/80 shadow-mac-sm'
                            : 'bg-white/75 border border-black/[0.06] shadow-mac-sm hover:bg-white' }}">
                    <div @class([
                        'w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 ring-1',
                        'bg-primary-100/90 ring-primary-200/70 text-primary-700' => $unread,
                        'bg-slate-50 ring-slate-100 text-slate-500' => ! $unread,
                    ])>
                        <svg class="w-4.5 h-4.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-slate-900 truncate">
                                {{ $data['title'] ?? 'Thông báo' }}
                            </p>
                            @if($unread)
                            <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                            {{ $data['body'] ?? '' }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ $notification->created_at?->diffForHumans() }}
                            @if(!empty($data['meta']['audience']))
                            · {{ $data['meta']['audience'] }}
                            @endif
                        </p>
                    </div>
                </button>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Thông báo mới nhất --}}
        @if($latestNotifications->isNotEmpty() || $boardAnnouncements->isEmpty())
        <section class="space-y-2.5">
            <div class="flex items-baseline justify-between px-1">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Thông báo mới nhất
                </h2>
                <a href="{{ route('notifications.index') }}"
                    class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                    Tất cả →
                </a>
            </div>

            @if($latestNotifications->isEmpty())
            <x-mac-panel class="px-4 py-4">
                <p class="text-sm text-slate-600">Chưa có thông báo nào.</p>
                <p class="mt-1 text-xs text-slate-400">
                    Khi ban giáo lý gửi thông tin, bạn sẽ thấy tại đây.
                </p>
            </x-mac-panel>
            @else
            <div class="rounded-2xl bg-white/75 backdrop-blur-xl border border-black/[0.06] shadow-mac overflow-hidden divide-y divide-slate-100">
                @foreach($latestNotifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $unread = $notification->unread();
                @endphp
                <button type="button"
                    wire:click="openHighlightedNotification('{{ $notification->id }}')"
                    class="w-full text-left px-4 py-3 hover:bg-slate-50/80 transition touch-feedback
                        {{ $unread ? 'bg-primary-50/30' : '' }}">
                    <div class="flex items-start gap-2.5">
                        @if($unread)
                        <span class="mt-1.5 w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></span>
                        @else
                        <span class="mt-1.5 w-2 h-2 rounded-full bg-transparent flex-shrink-0"></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900 truncate">
                                {{ $data['title'] ?? 'Thông báo' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                                {{ $data['body'] ?? '' }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ $notification->created_at?->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
            @endif
        </section>
        @endif

        @if($pendingTodayCount === 0 && $boardAnnouncements->isEmpty() && $latestNotifications->isEmpty())
        <x-mac-panel class="px-4 py-4">
            <p class="text-sm text-slate-600">
                Hôm nay không có phiên cần điểm danh.
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Dùng thanh điều hướng bên dưới để điểm danh, quét QR hoặc xem học sinh.
            </p>
        </x-mac-panel>
        @endif

    </div>
</div>
