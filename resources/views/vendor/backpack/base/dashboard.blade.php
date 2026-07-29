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
        'super_admin'       => 'Quản trị hệ thống',
        'parish_admin'      => 'Quản trị xứ',
        'parishioner_admin' => 'Quản trị giáo dân',
        'catechism_admin'   => 'Quản trị giáo lý',
        'catechist'         => 'Giáo lý viên',
    ];
@endphp

<div class="bp-dash-welcome">
    <h2>Xin chào, {{ backpack_user()->name ?? 'Quản trị hệ thống' }}</h2>
    <p>
        Tổng quan vận hành hệ thống
        @if(!empty($stats['generated_at']))
            · cập nhật {{ $stats['generated_at']->diffForHumans() }}
        @endif
    </p>
</div>

{{-- ===== Cảnh báo vận hành ===== --}}
@php
    $alerts = collect($stats['alerts'] ?? []);
    $attention = collect($stats['attention'] ?? []);
    $attentionCounts = $stats['attention_counts'] ?? [];
    $health = $stats['health'] ?? [];
@endphp

@if($alerts->isNotEmpty())
<div class="d-flex align-items-center justify-content-between mb-2">
    <h3 class="h5 mb-0 font-weight-bold text-dark">Cần xử lý hôm nay</h3>
    <span class="badge badge-warning">{{ $alerts->count() }} cảnh báo</span>
</div>
<div class="row mb-3">
    @foreach($alerts as $alert)
    <div class="col-md-6 col-xl-4 mb-3">
        <a href="{{ $alert['url'] }}" class="bp-alert-card bp-alert-card--{{ $alert['level'] }} d-block h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="bp-alert-title">{{ $alert['title'] }}</div>
                    <div class="bp-alert-detail">{{ $alert['detail'] }}</div>
                </div>
                @if(($alert['count'] ?? 0) > 0)
                    <span class="badge badge-{{ $alert['level'] === 'danger' ? 'danger' : ($alert['level'] === 'info' ? 'info' : 'warning') }}">
                        {{ number_format($alert['count']) }}
                    </span>
                @endif
            </div>
        </a>
    </div>
    @endforeach
</div>
@endif

{{-- ===== Công cụ hỗ trợ ===== --}}
<div class="card mb-4" id="support-tools">
    <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span>Công cụ hỗ trợ</span>
        <span class="small text-muted font-weight-normal">Tìm user / giáo xứ theo tên, email, mã, SĐT</span>
    </div>
    <div class="card-body">
        <form method="get" action="{{ backpack_url('dashboard') }}" class="form-inline flex-wrap">
            <div class="input-group mr-2 mb-2" style="min-width: 280px; flex: 1;">
                <input type="search" name="q" value="{{ $support['query'] ?? request('q') }}"
                    class="form-control" placeholder="vd: nguyen@..., Giáo xứ X, 090..."
                    minlength="2" required>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Tìm</button>
                </div>
            </div>
            <a href="{{ backpack_url('user') }}" class="btn btn-outline-secondary mb-2 mr-2">Người dùng</a>
            <a href="{{ backpack_url('parish-management') }}" class="btn btn-outline-secondary mb-2 mr-2">Giáo xứ</a>
            <a href="{{ backpack_url('set-admin') }}" class="btn btn-outline-secondary mb-2">Gán admin</a>
        </form>

        @if(!empty($support))
            <hr>
            @if(($support['users'] ?? collect())->isEmpty() && ($support['parishes'] ?? collect())->isEmpty())
                <p class="text-muted mb-0">Không tìm thấy kết quả cho “{{ $support['query'] }}”.</p>
            @else
                <div class="row">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="font-weight-bold mb-2">Người dùng ({{ ($support['users'] ?? collect())->count() }})</div>
                        <div class="list-group list-group-flush border rounded">
                            @forelse($support['users'] as $u)
                            <a href="{{ $u['url'] }}" class="list-group-item list-group-item-action">
                                <div class="font-weight-bold">{{ $u['name'] }}
                                    @unless($u['is_active'])
                                        <span class="badge badge-secondary">tắt</span>
                                    @endunless
                                </div>
                                <div class="small text-muted">{{ $u['email'] }} · {{ $u['roles'] }}</div>
                                <div class="small">{{ $u['parish'] ?: 'Chưa gắn xứ' }}
                                    @if($u['last_login_at'])
                                        · login {{ $u['last_login_at']->diffForHumans() }}
                                    @endif
                                </div>
                            </a>
                            @empty
                            <div class="list-group-item text-muted">Không có user khớp</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="font-weight-bold mb-2">Giáo xứ ({{ ($support['parishes'] ?? collect())->count() }})</div>
                        <div class="list-group list-group-flush border rounded">
                            @forelse($support['parishes'] as $p)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ $p['url'] }}" class="font-weight-bold">{{ $p['name'] }}</a>
                                    <span class="badge badge-{{ $p['status'] ? 'success' : 'secondary' }}">
                                        {{ $p['status'] ? 'active' : 'tắt' }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    {{ $p['code'] ?: '—' }}
                                    @if($p['phone']) · {{ $p['phone'] }} @endif
                                    · {{ $p['diocese'] ?: '—' }}
                                </div>
                                <div class="small">
                                    {{ number_format($p['students']) }} HS · {{ number_format($p['teachers']) }} GLV · {{ number_format($p['users']) }} user
                                    · <a href="{{ $p['edit_url'] }}">Sửa</a>
                                </div>
                            </div>
                            @empty
                            <div class="list-group-item text-muted">Không có giáo xứ khớp</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- ===== Giáo xứ bất thường ===== --}}
<div class="d-flex align-items-center justify-content-between mb-2" id="attention-parishes">
    <h3 class="h5 mb-0 font-weight-bold text-dark">Giáo xứ cần chú ý</h3>
    <span class="small text-muted">
        {{ number_format($attentionCounts['total_flagged'] ?? 0) }} xứ ·
        thiếu admin {{ number_format($attentionCounts['no_admin'] ?? 0) }} ·
        im lặng {{ number_format($attentionCounts['silent_30d'] ?? 0) }} ·
        chưa HS {{ number_format($attentionCounts['no_students'] ?? 0) }}
    </span>
</div>
<div class="card mb-4">
    <div class="table-responsive mb-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Giáo xứ</th>
                    <th>Lý do</th>
                    <th class="text-right">HS / GLV / User</th>
                    <th>Login gần nhất</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($attention as $parish)
                <tr>
                    <td>
                        <a href="{{ $parish['url'] }}" class="font-weight-bold">{{ $parish['name'] }}</a>
                        <div class="small text-muted">{{ $parish['diocese'] ?: '—' }}</div>
                    </td>
                    <td>
                        @foreach($parish['reasons'] as $reason)
                            <span class="badge badge-{{ $reason['code'] === 'no_admin' ? 'warning' : ($reason['code'] === 'silent_30d' ? 'secondary' : 'light') }} mr-1">
                                {{ $reason['label'] }}
                            </span>
                        @endforeach
                    </td>
                    <td class="text-right text-nowrap">
                        {{ number_format($parish['students']) }} /
                        {{ number_format($parish['teachers']) }} /
                        {{ number_format($parish['users']) }}
                    </td>
                    <td class="small text-muted text-nowrap">
                        {{ $parish['last_login_at']?->diffForHumans() ?? 'Chưa có' }}
                    </td>
                    <td class="text-right text-nowrap">
                        <a href="{{ $parish['edit_url'] }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                        @unless($parish['has_admin'])
                            <a href="{{ $parish['set_admin_url'] }}" class="btn btn-sm btn-outline-primary">Gán admin</a>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Không có giáo xứ active bất thường
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(($attentionCounts['total_flagged'] ?? 0) > $attention->count())
        <div class="card-footer small text-muted">
            Đang hiện {{ $attention->count() }} / {{ number_format($attentionCounts['total_flagged']) }} xứ cần chú ý (ưu tiên thiếu admin).
            <a href="{{ backpack_url('parish-management') }}">Xem tất cả giáo xứ</a>
        </div>
    @endif
</div>

{{-- ===== 1. Tổng quan hệ thống ===== --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h3 class="h5 mb-0 font-weight-bold text-dark">1. Tổng quan hệ thống</h3>
    @if(!empty($health['backup_latest_at']))
        <span class="small text-muted">
            Backup gần nhất: {{ $health['backup_latest_at']->diffForHumans() }}
            ({{ number_format($health['backup_count'] ?? 0) }} file)
        </span>
    @endif
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

{{-- ===== 1b. Quy mô dữ liệu ===== --}}
@php $scale = $stats['scale'] ?? []; @endphp
<div class="d-flex align-items-center justify-content-between mb-2 mt-1">
    <h3 class="h5 mb-0 font-weight-bold text-dark">Quy mô dữ liệu</h3>
    <span class="small text-muted">Học sinh · lớp năm active · GLV · giáo dân</span>
</div>

<div class="row">
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Học sinh</div>
            <div class="bp-stat-value">{{ number_format($scale['students_total'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($scale['students_active'] ?? 0) }} đang active
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Lớp (năm active)</div>
            <div class="bp-stat-value">{{ number_format($scale['classes_active_year'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($scale['classes_total'] ?? 0) }} mọi năm
                · {{ number_format($scale['school_years_active'] ?? 0) }} năm học active
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Giáo lý viên</div>
            <div class="bp-stat-value">{{ number_format($scale['teachers_total'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($scale['teachers_active'] ?? 0) }} đang active
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Giáo dân</div>
            <div class="bp-stat-value">{{ number_format($scale['parishioners_total'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($scale['parishioners_active'] ?? 0) }} đang sinh hoạt
            </div>
        </div>
    </div>
</div>

{{-- ===== 1c. Sử dụng & hiệu năng ===== --}}
@php
    $usage = $stats['usage'] ?? [];
    $perf = $stats['performance'] ?? [];
    $loginTrend = collect($usage['trend_7d'] ?? []);
    $maxLogins = max(1, (int) $loginTrend->max(fn ($d) => $d['logins'] ?? 0));
@endphp
<div class="d-flex align-items-center justify-content-between mb-2 mt-1">
    <h3 class="h5 mb-0 font-weight-bold text-dark">Sử dụng &amp; hiệu năng</h3>
    <span class="small text-muted">Đăng nhập · người dùng hoạt động · tốc độ web</span>
</div>

<div class="row">
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Đăng nhập hôm nay</div>
            <div class="bp-stat-value">{{ number_format($usage['logins_today'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($usage['unique_users_today'] ?? 0) }} người dùng
                · {{ number_format($usage['failed_logins_today'] ?? 0) }} thất bại
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Đăng nhập 7 ngày</div>
            <div class="bp-stat-value">{{ number_format($usage['logins_7d'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                {{ number_format($usage['unique_users_7d'] ?? 0) }} người dùng khác nhau
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Người dùng hoạt động</div>
            <div class="bp-stat-value">{{ number_format($usage['active_users_7d'] ?? 0) }}</div>
            <div class="bp-stat-hint">
                7 ngày · {{ number_format($usage['active_users_30d'] ?? 0) }} trong 30 ngày
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="bp-stat-card">
            <div class="bp-stat-label">Thời gian TB hôm nay</div>
            <div class="bp-stat-value">
                @if(($perf['avg_ms_today'] ?? null) !== null)
                    {{ number_format($perf['avg_ms_today']) }}<span class="h6 text-muted"> ms</span>
                @else
                    —
                @endif
            </div>
            <div class="bp-stat-hint">
                {{ number_format($perf['requests_today'] ?? 0) }} request
                · chậm (≥{{ $perf['slow_threshold_ms'] ?? 1000 }}ms): {{ number_format($perf['slow_requests_today'] ?? 0) }}
                @if(($perf['slow_rate_today'] ?? null) !== null)
                    ({{ $perf['slow_rate_today'] }}%)
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header font-weight-bold">Đăng nhập 7 ngày gần đây</div>
            <div class="card-body">
                @if($loginTrend->isEmpty() || $loginTrend->sum(fn ($d) => $d['logins'] ?? 0) === 0)
                    <p class="text-muted mb-0">Chưa có dữ liệu đăng nhập (sau khi migrate sẽ bắt đầu ghi nhận).</p>
                @else
                    <div class="bp-week-chart">
                        @foreach($loginTrend as $day)
                        <div class="bp-week-col">
                            <div class="bp-week-bars">
                                <div class="bp-week-bar bp-week-bar--submitted"
                                    style="height: {{ max(4, round((($day['logins'] ?? 0) / $maxLogins) * 100)) }}%"
                                    title="Đăng nhập: {{ $day['logins'] ?? 0 }}"></div>
                            </div>
                            <div class="bp-week-label">{{ $day['label'] }}</div>
                            <div class="bp-week-nums text-muted">{{ $day['logins'] ?? 0 }}</div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header font-weight-bold">Hiệu năng 7 ngày</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Request</span>
                    <strong>{{ number_format($perf['requests_7d'] ?? 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Thời gian TB</span>
                    <strong>
                        @if(($perf['avg_ms_7d'] ?? null) !== null)
                            {{ number_format($perf['avg_ms_7d']) }} ms
                        @else
                            —
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Request chậm</span>
                    <strong>
                        {{ number_format($perf['slow_requests_7d'] ?? 0) }}
                        @if(($perf['slow_rate_7d'] ?? null) !== null)
                            <span class="text-muted font-weight-normal">({{ $perf['slow_rate_7d'] }}%)</span>
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Lỗi server (5xx)</span>
                    <strong class="{{ ($perf['server_errors_7d'] ?? 0) > 0 ? 'text-danger' : '' }}">
                        {{ number_format($perf['server_errors_today'] ?? 0) }} hôm nay
                        · {{ number_format($perf['server_errors_7d'] ?? 0) }} / 7 ngày
                    </strong>
                </div>
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
