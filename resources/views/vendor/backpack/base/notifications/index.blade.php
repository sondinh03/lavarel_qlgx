@extends(backpack_view('blank'))

@section('header')
<section class="container-fluid">
    <h2>
        <span class="text-capitalize">Thông báo</span>
        @if($unreadCount > 0)
            <span class="badge badge-danger">{{ $unreadCount }} chưa đọc</span>
        @endif
    </h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <strong>Hộp thư thông báo</strong>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                    @if($unreadCount > 0)
                    <form method="POST" action="{{ route('backpack.notifications.mark-all-read') }}" class="mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="la la-check-double"></i> Đánh dấu tất cả đã đọc
                        </button>
                    </form>
                    @endif
                    @if($notifications->total() > 0)
                    <form method="POST" action="{{ route('backpack.notifications.destroy-all') }}" class="mb-0"
                        onsubmit="return confirm('Xóa tất cả thông báo? Hành động không thể hoàn tác.');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="la la-trash"></i> Xóa tất cả
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $unread = $notification->unread();
                        $level = $data['level'] ?? 'info';
                        $badgeClass = match ($level) {
                            'success' => 'badge-success',
                            'warning' => 'badge-warning',
                            'danger', 'error' => 'badge-danger',
                            default => 'badge-primary',
                        };
                    @endphp
                    <div class="d-flex align-items-start px-3 py-3 border-bottom {{ $unread ? 'bg-light' : '' }}">
                        <div class="mr-3 mt-1">
                            @if($unread)
                                <span class="badge badge-pill badge-danger" title="Chưa đọc">&nbsp;</span>
                            @else
                                <span class="badge badge-pill badge-secondary" style="opacity:.35" title="Đã đọc">&nbsp;</span>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center flex-wrap mb-1">
                                <strong class="mr-2">{{ $data['title'] ?? 'Thông báo' }}</strong>
                                <span class="badge {{ $badgeClass }}">{{ $level }}</span>
                                <small class="text-muted ml-auto">{{ $notification->created_at?->diffForHumans() }}</small>
                            </div>
                            @if(!empty($data['body']))
                                <div class="text-muted mb-2">{{ $data['body'] }}</div>
                            @endif
                            <div>
                                <a href="{{ route('backpack.notifications.open', $notification->id) }}"
                                    class="btn btn-sm btn-primary">
                                    Mở
                                </a>
                                @if($unread)
                                <form method="POST"
                                    action="{{ route('backpack.notifications.read', $notification->id) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link">Đánh dấu đã đọc</button>
                                </form>
                                @endif
                                <form method="POST"
                                    action="{{ route('backpack.notifications.destroy', $notification->id) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Xóa thông báo này?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-danger">Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted">
                        Chưa có thông báo nào.
                    </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
