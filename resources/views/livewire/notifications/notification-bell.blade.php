<div class="relative" data-layout-livewire="notification-bell"
    x-data="{ open: false }" @click.outside="open = false">
    <button type="button"
        @click="open = !open"
        class="relative p-2 rounded-xl text-slate-500 hover:bg-black/[0.04] hover:text-primary-600
            active:bg-black/[0.06] transition touch-feedback"
        aria-label="Thông báo"
        :aria-expanded="open.toString()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
        <span class="absolute top-1 right-1 min-w-[1.1rem] h-4 px-1 rounded-full
            bg-red-500 text-white text-[10px] font-bold leading-4 text-center
            ring-2 ring-white">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
        @endif
    </button>

    <div x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-1.5rem)]
            bg-white/95 backdrop-blur-xl border border-black/[0.06] rounded-2xl shadow-mac z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/[0.06] flex items-center justify-between gap-2">
            <div>
                <p class="text-sm font-semibold text-slate-900">Thông báo</p>
                @if ($unreadCount > 0)
                <p class="text-xs text-slate-500">{{ $unreadCount }} chưa đọc</p>
                @endif
            </div>
            @if ($unreadCount > 0)
            <button type="button"
                wire:click="markAllAsRead"
                class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">
                Đọc tất cả
            </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
            @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $unread = $notification->unread();
            @endphp
            <button type="button"
                wire:click="openNotification('{{ $notification->id }}')"
                @click="open = false"
                class="w-full text-left px-4 py-3 hover:bg-slate-50 transition
                    {{ $unread ? 'bg-primary-50/40' : '' }}">
                <div class="flex items-start gap-2.5">
                    @if ($unread)
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
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-500">
                Chưa có thông báo nào.
            </div>
            @endforelse
        </div>

        <div class="px-4 py-2.5 border-t border-black/[0.06] bg-slate-50/60">
            <a href="{{ route('notifications.index') }}"
                @click="open = false"
                class="block text-center text-xs font-semibold text-primary-600 hover:text-primary-700 transition">
                Xem tất cả thông báo
            </a>
        </div>
    </div>
</div>
