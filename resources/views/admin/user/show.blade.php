@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        $crud->entity_name_plural => url($crud->route),
        trans('backpack::crud.preview') => false,
    ];
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

    $roleLabels = [
        'super_admin'        => 'Quản trị hệ thống',
        'parish_admin'       => 'Quản trị giáo xứ',
        'catechism_admin'    => 'Quản trị giáo lý',
        'parishioner_admin'  => 'Quản trị giáo dân',
        'catechist'          => 'Giáo lý viên',
    ];

    $roleNames = $entry->getRoleNames();
    $roleDisplay = $roleNames->map(fn ($name) => $roleLabels[$name] ?? $name)->implode(', ') ?: '—';

    $isActive = (bool) $entry->is_active;
    $initials = collect(preg_split('/\s+/', trim((string) $entry->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    if ($initials === '') {
        $initials = '?';
    }

    $avatarUrl = method_exists($entry, 'avatarUrl') ? $entry->avatarUrl() : null;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="mb-0">
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small class="text-muted">{!! $crud->getSubheading() ?? 'Chi tiết tài khoản' !!}</small>
            @if ($crud->hasAccess('list'))
                <small>
                    <a href="{{ url($crud->route) }}" class="font-sm">
                        <i class="la la-angle-double-left"></i>
                        {{ trans('backpack::crud.back_to_all') }}
                        <span>{{ $crud->entity_name_plural }}</span>
                    </a>
                </small>
            @endif
        </h2>
        <div class="d-flex align-items-center gap-2">
            <a href="javascript:window.print();" class="btn btn-sm btn-outline-secondary" title="In">
                <i class="la la-print"></i>
            </a>
            @if ($crud->hasAccess('update'))
                <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-sm btn-primary">
                    <i class="la la-edit"></i> Sửa
                </a>
            @endif
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="{{ $crud->getShowContentClass() }}">

        {{-- Profile header --}}
        <div class="card mb-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                    <div class="mr-sm-4 mb-3 mb-sm-0 flex-shrink-0">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}"
                                alt="{{ $entry->name }}"
                                class="rounded-circle border"
                                style="width: 88px; height: 88px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center border"
                                style="width: 88px; height: 88px; background: #EAF7EF; color: #2AA14A; font-size: 1.75rem; font-weight: 700;">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center mb-1">
                            <h3 class="mb-0 mr-2 text-truncate" style="font-weight: 700; letter-spacing: -0.02em; color: #0f172a;">
                                {{ $entry->name }}
                            </h3>
                            @if ($isActive)
                                <span class="badge badge-pill px-3 py-1"
                                    style="background: #EAF7EF; color: #2AA14A; font-weight: 600;">
                                    Hoạt động
                                </span>
                            @else
                                <span class="badge badge-pill px-3 py-1"
                                    style="background: #FEE2E2; color: #B91C1C; font-weight: 600;">
                                    Ngưng
                                </span>
                            @endif
                        </div>
                        <div class="text-muted mb-2 text-truncate">
                            <i class="la la-envelope"></i> {{ $entry->email }}
                        </div>
                        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                            @foreach ($roleNames as $roleName)
                                <span class="badge badge-light border px-2 py-1"
                                    style="font-weight: 600; color: #334155; background: #f8fafc;">
                                    {{ $roleLabels[$roleName] ?? $roleName }}
                                </span>
                            @endforeach
                            @if ($roleNames->isEmpty())
                                <span class="text-muted small">Chưa gán vai trò</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                Thông tin tài khoản
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 user-show-table">
                    <tbody>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="width: 34%; font-weight: 500;">ID</th>
                            <td class="pr-4">{{ $entry->getKey() }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Họ tên</th>
                            <td class="pr-4 font-weight-semibold">{{ $entry->name }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Email</th>
                            <td class="pr-4">
                                <a href="mailto:{{ $entry->email }}">{{ $entry->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Giáo xứ</th>
                            <td class="pr-4">{{ $entry->parish?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Vai trò</th>
                            <td class="pr-4">{{ $roleDisplay }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Trạng thái</th>
                            <td class="pr-4">
                                @if ($isActive)
                                    <span style="color: #2AA14A; font-weight: 600;">Hoạt động</span>
                                @else
                                    <span style="color: #B91C1C; font-weight: 600;">Ngưng hoạt động</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Email đã xác minh</th>
                            <td class="pr-4">
                                {{ $entry->email_verified_at
                                    ? \Carbon\Carbon::parse($entry->email_verified_at)->format('d/m/Y H:i')
                                    : 'Chưa xác minh' }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted" style="font-weight: 500;">Ngày tạo</th>
                            <td class="pr-4">
                                {{ $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl-4 text-muted pb-3" style="font-weight: 500;">Cập nhật lần cuối</th>
                            <td class="pr-4 pb-3">
                                {{ $entry->updated_at ? $entry->updated_at->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($crud->buttons()->where('stack', 'line')->count())
            <div class="card">
                <div class="card-body d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                    <span class="text-muted mr-2">{{ trans('backpack::crud.actions') }}:</span>
                    @include('crud::inc.button_stack', ['stack' => 'line'])
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<style>
    .user-show-table tbody tr + tr {
        border-top: 1px solid #f1f5f9;
    }
    .user-show-table th,
    .user-show-table td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        vertical-align: middle;
    }
    @media print {
        .d-print-none, .btn { display: none !important; }
    }
</style>
@endsection

@section('after_scripts')
<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
@endsection
