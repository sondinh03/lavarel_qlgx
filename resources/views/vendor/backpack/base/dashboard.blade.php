@extends(backpack_view('blank'))

@section('content')
@php
    $overview = $stats['overview'] ?? [];
    $roles = $stats['roles'] ?? [];
    $regs = $stats['registrations'] ?? [];
    $pending = $stats['pending'] ?? collect();
    $weeks = collect($regs['weeks'] ?? []);
    $maxWeek = max(1, (int) $weeks->max(fn ($w) => max($w['submitted'] ?? 0, $w['approved'] ?? 0, $w['rejected'] ?? 0)));
    $roleLabels = [
        'super_admin'       => 'Super admin',
        'parish_admin'      => 'Quản trị xứ',
        'parishioner_admin' => 'Quản trị giáo dân',
        'catechism_admin'   => 'Quản trị giáo lý',
        'catechist'         => 'Giáo lý viên',
    ];
@endphp

<div class="bp-dash-welcome">
    <h2>Xin chào, {{ backpack_user()->name ?? 'Super Admin' }}</h2>
    <p>
        Tổng quan vận hành hệ thống
        @if(!empty($stats['generated_at']))
            · cập nhật {{ $stats['generated_at']->diffForHumans() }}
        @endif
    </p>
</div>

{{-- ===== 1. Tổng quan hệ thống ===== --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h3 class="h5 mb-0 font-weight-bold text-dark">1. Tổng quan hệ thống</h3>
</div>

<div class="row">
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Giáo xứ</div>
            <div class="bp-stat-value">{{ number_format($overview['parishes_total'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($overview['parishes_active'] ?? 0) }} đang hoạt động
                · {{ number_format($overview['parishes_inactive'] ?? 0) }} tắt
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Xứ có quản trị</div>
            <div class="bp-stat-value">{{ number_format($overview['parishes_with_admin'] ?? 0) }}</div>
            <div class="bp-stat-hint text-warning">
                {{ number_format($overview['parishes_without_admin'] ?? 0) }} xứ active chưa có admin
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Người dùng</div>
            <div class="bp-stat-value">{{ number_format($overview['users_total'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($overview['users_with_parish'] ?? 0) }} đã gắn giáo xứ
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Địa giáo hội</div>
            <div class="bp-stat-value">{{ number_format($overview['dioceses'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($overview['deaneries'] ?? 0) }} giáo hạt
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header font-weight-bold">Người dùng theo vai trò</div>
    <div class="card-body py-3">
        <div class="row">
            @foreach($roleLabels as $key => $label)
            <div class="col-6 col-md-4 col-xl mb-2 mb-xl-0">
                <a href="{{ backpack_url('user') }}" class="bp-role-pill d-block">
                    <span class="bp-role-count">{{ number_format($roles[$key] ?? 0) }}</span>
                    <span class="bp-role-name">{{ $label }}</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== 2. Đăng ký & kích hoạt ===== --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h3 class="h5 mb-0 font-weight-bold text-dark">2. Đăng ký &amp; kích hoạt quản trị xứ</h3>
    <a href="{{ backpack_url('parish-admin-registration') }}" class="btn btn-sm btn-outline-primary">
        Xem tất cả
    </a>
</div>

<div class="row">
    <div class="col-6 col-lg-3 mb-3">
        <a href="{{ backpack_url('parish-admin-registration?status=pending') }}" class="bp-stat-card bp-stat-card--link bp-stat-card--warn">
            <div class="bp-stat-label">Chờ duyệt</div>
            <div class="bp-stat-value">{{ number_format($regs['pending'] ?? 0) }}</div>
            <div class="bp-stat-hint">Cần xử lý</div>
        </a>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card bp-stat-card--ok">
            <div class="bp-stat-label">Đã duyệt</div>
            <div class="bp-stat-value">{{ number_format($regs['approved'] ?? 0) }}</div>
            <div class="bp-stat-hint">Tài khoản đã tạo</div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Từ chối</div>
            <div class="bp-stat-value">{{ number_format($regs['rejected'] ?? 0) }}</div>
            <div class="bp-stat-hint">Không kích hoạt</div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Tỷ lệ duyệt</div>
            <div class="bp-stat-value">
                @if($regs['approval_rate'] !== null)
                    {{ $regs['approval_rate'] }}%
                @else
                    —
                @endif
            </div>
            <div class="bp-stat-hint">Trên các yêu cầu đã xử lý</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header font-weight-bold">Xu hướng 8 tuần gần đây</div>
            <div class="card-body">
                @if($weeks->isEmpty())
                    <p class="text-muted mb-0">Chưa có dữ liệu đăng ký.</p>
                @else
                    <div class="bp-week-legend mb-3">
                        <span><i class="bp-dot bp-dot--submitted"></i> Gửi mới</span>
                        <span><i class="bp-dot bp-dot--approved"></i> Duyệt</span>
                        <span><i class="bp-dot bp-dot--rejected"></i> Từ chối</span>
                    </div>
                    <div class="bp-week-chart">
                        @foreach($weeks as $week)
                        <div class="bp-week-col">
                            <div class="bp-week-bars">
                                <div class="bp-week-bar bp-week-bar--submitted"
                                    style="height: {{ max(4, round(($week['submitted'] / $maxWeek) * 100)) }}%"
                                    title="Gửi: {{ $week['submitted'] }}"></div>
                                <div class="bp-week-bar bp-week-bar--approved"
                                    style="height: {{ max(4, round(($week['approved'] / $maxWeek) * 100)) }}%"
                                    title="Duyệt: {{ $week['approved'] }}"></div>
                                <div class="bp-week-bar bp-week-bar--rejected"
                                    style="height: {{ max(4, round(($week['rejected'] / $maxWeek) * 100)) }}%"
                                    title="Từ chối: {{ $week['rejected'] }}"></div>
                            </div>
                            <div class="bp-week-label">{{ $week['label'] }}</div>
                            <div class="bp-week-nums text-muted">
                                {{ $week['submitted'] }}/{{ $week['approved'] }}/{{ $week['rejected'] }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Chờ duyệt gần đây</strong>
                @if(($regs['pending'] ?? 0) > 0)
                    <span class="badge badge-warning">{{ $regs['pending'] }}</span>
                @endif
            </div>
            <div class="list-group list-group-flush">
                @forelse($pending as $item)
                <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="pr-2">
                            <div class="font-weight-bold">{{ $item['name'] }}</div>
                            <div class="small text-muted">{{ $item['email'] }}</div>
                            <div class="small">{{ $item['parish'] }} · {{ $item['roles'] }}</div>
                        </div>
                        <small class="text-muted text-nowrap">
                            {{ $item['created_at']?->diffForHumans() }}
                        </small>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted text-center py-4">
                    Không có yêu cầu chờ duyệt
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Quick links giữ lại --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h3 class="h5 mb-0 font-weight-bold text-dark">Truy cập nhanh</h3>
</div>

<div class="row">
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('parish-management') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-church"></i></div>
            <div class="bp-dash-title">Quản lý giáo xứ</div>
            <p class="bp-dash-desc">Tạo và cấu hình giáo xứ trên hệ thống</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('user') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-users"></i></div>
            <div class="bp-dash-title">Người dùng &amp; vai trò</div>
            <p class="bp-dash-desc">Tài khoản, phân quyền hệ thống</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ route('backpack.notifications.index') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-bell"></i></div>
            <div class="bp-dash-title">Thông báo</div>
            <p class="bp-dash-desc">
                @php $dashUnread = backpack_user()?->unreadNotifications()->count() ?? 0; @endphp
                @if($dashUnread > 0)
                    {{ $dashUnread }} thông báo chưa đọc
                @else
                    Hộp thư thông báo hệ thống
                @endif
            </p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('setting') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-cog"></i></div>
            <div class="bp-dash-title">Cài đặt</div>
            <p class="bp-dash-desc">Logo, hỗ trợ, cấu hình chung</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('backup') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-hdd-o"></i></div>
            <div class="bp-dash-title">Sao lưu</div>
            <p class="bp-dash-desc">Backup cơ sở dữ liệu định kỳ</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('log') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-terminal"></i></div>
            <div class="bp-dash-title">Nhật ký hệ thống</div>
            <p class="bp-dash-desc">Theo dõi lỗi và hoạt động server</p>
        </a>
    </div>
</div>
@endsection
