{{-- Topbar: chuông thông báo in-app (database) --}}
@php
    $bpUser = backpack_user();
    $bpUnread = $bpUser ? $bpUser->unreadNotifications()->count() : 0;
    $bpRecent = $bpUser
        ? $bpUser->notifications()->latest()->limit(8)->get()
        : collect();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"
        title="Thông báo">
        <i class="la la-bell" style="font-size: 1.25rem;"></i>
        @if($bpUnread > 0)
            <span class="badge badge-pill badge-danger"
                style="position:relative; top:-8px; left:-6px; font-size:10px;">
                {{ $bpUnread > 99 ? '99+' : $bpUnread }}
            </span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-right" style="min-width: 320px; max-width: 360px;">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <strong>Thông báo</strong>
            @if($bpUnread > 0)
            <form method="POST" action="{{ route('backpack.notifications.mark-all-read') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0">Đọc hết</button>
            </form>
            @endif
        </div>
        <div class="dropdown-divider"></div>

        @forelse($bpRecent as $notification)
            @php
                $data = $notification->data ?? [];
                $unread = $notification->unread();
            @endphp
            <a class="dropdown-item py-2 {{ $unread ? 'font-weight-bold' : 'text-muted' }}"
                href="{{ route('backpack.notifications.open', $notification->id) }}"
                style="white-space: normal;">
                <div class="d-flex">
                    @if($unread)
                        <span class="badge badge-pill badge-danger mr-2 mt-1" style="height:8px;width:8px;padding:0;">&nbsp;</span>
                    @else
                        <span class="mr-2 mt-1" style="width:8px;"></span>
                    @endif
                    <div>
                        <div>{{ $data['title'] ?? 'Thông báo' }}</div>
                        @if(!empty($data['body']))
                            <small class="text-muted d-block" style="font-weight:400;">
                                {{ \Illuminate\Support\Str::limit($data['body'], 80) }}
                            </small>
                        @endif
                        <small class="text-muted" style="font-weight:400;">
                            {{ $notification->created_at?->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </a>
        @empty
            <div class="dropdown-item text-muted text-center py-3">Chưa có thông báo</div>
        @endforelse

        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="{{ route('backpack.notifications.index') }}">
            Xem tất cả thông báo
        </a>
    </div>
</li>
