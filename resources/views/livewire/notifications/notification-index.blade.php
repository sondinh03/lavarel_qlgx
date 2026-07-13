@section('title', 'Thông báo')

<div class="relative min-h-[calc(100vh-8rem)] py-4 sm:py-6 px-3 sm:px-4 lg:px-6">
    <div class="mx-auto max-w-3xl space-y-5">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Thông báo"
                :description="$unreadCount > 0 ? ($unreadCount . ' thông báo chưa đọc') : 'Tất cả thông báo đã đọc'">
                <x-slot name="actions">
                    @if ($unreadCount > 0)
                    <button type="button"
                        wire:click="markAllAsRead"
                        class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl
                            bg-white/80 border border-black/[0.08] text-slate-700 text-xs font-semibold
                            shadow-mac-sm hover:bg-slate-50 active:scale-[0.98] transition-all">
                        Đọc tất cả
                    </button>
                    @endif
                </x-slot>
            </x-page-header>

            <div class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $unread = $notification->unread();
                @endphp
                <button type="button"
                    wire:click="openNotification('{{ $notification->id }}')"
                    class="w-full text-left px-4 lg:px-6 py-4 hover:bg-slate-50/80 transition
                        {{ $unread ? 'bg-primary-50/30' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0
                            {{ $unread ? 'bg-primary-500' : 'bg-slate-200' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $data['title'] ?? 'Thông báo' }}
                                </p>
                                <p class="text-[11px] text-slate-400 flex-shrink-0">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>
                            <p class="text-sm text-slate-600 mt-1">
                                {{ $data['body'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </button>
                @empty
                <div class="px-6 py-12 text-center text-sm text-slate-500">
                    Chưa có thông báo nào.
                </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
            <div class="px-4 lg:px-6 py-4 border-t border-black/[0.06]">
                {{ $notifications->links() }}
            </div>
            @endif
        </x-mac-panel>
    </div>
</div>
