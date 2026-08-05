@section('title', 'Thông báo')

<div class="relative min-h-[calc(100vh-8rem)] py-4 sm:py-6 px-3 sm:px-4 lg:px-6">
    <div class="mx-auto max-w-3xl space-y-5">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Thông báo"
                :description="$unreadCount > 0 ? ($unreadCount . ' thông báo chưa đọc') : 'Tất cả thông báo đã đọc'">
                <x-slot name="actions">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($unreadCount > 0)
                        <button type="button"
                            wire:click="markAllAsRead"
                            class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl
                                bg-white/80 border border-black/[0.08] text-slate-700 text-xs font-semibold
                                shadow-mac-sm hover:bg-slate-50 active:scale-[0.98] transition-all">
                            Đọc tất cả
                        </button>
                        @endif
                        @if ($notifications->total() > 0)
                        <button type="button"
                            class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl
                                bg-white/80 border border-red-200/80 text-red-600 text-xs font-semibold
                                shadow-mac-sm hover:bg-red-50 active:scale-[0.98] transition-all"
                            @click="$dispatch('open-confirm', {
                                message: 'Xóa tất cả thông báo? Hành động không thể hoàn tác.',
                                wireMethod: 'deleteAllNotifications',
                                componentId: @js($_instance->id)
                            })">
                            Xóa tất cả
                        </button>
                        @endif
                    </div>
                </x-slot>
            </x-page-header>

            <div class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $unread = $notification->unread();
                @endphp
                <div class="flex items-start gap-1 px-2 lg:px-4 py-1
                    {{ $unread ? 'bg-primary-50/30' : '' }}">
                    <button type="button"
                        wire:click="openNotification('{{ $notification->id }}')"
                        class="min-w-0 flex-1 text-left px-2 lg:px-2 py-3 rounded-xl hover:bg-slate-50/80 transition">
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
                    <button type="button"
                        class="flex-shrink-0 mt-3 p-2 rounded-xl text-slate-400
                            hover:text-red-600 hover:bg-red-50 transition"
                        title="Xóa thông báo"
                        aria-label="Xóa thông báo"
                        @click.stop="$dispatch('open-confirm', {
                            message: 'Xóa thông báo này? Hành động không thể hoàn tác.',
                            wireMethod: {{ \Illuminate\Support\Js::from('deleteNotification(\'' . $notification->id . '\')') }},
                            componentId: @js($_instance->id)
                        })">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
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
