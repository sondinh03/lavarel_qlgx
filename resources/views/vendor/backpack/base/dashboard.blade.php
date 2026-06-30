@extends(backpack_view('blank'))

@section('content')
<div class="bp-dash-welcome">
    <h2>Xin chào, {{ backpack_user()->name ?? 'Super Admin' }}</h2>
    <p>Panel quản trị hệ thống — các role khác sử dụng giao diện Livewire.</p>
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
            <p class="bp-dash-desc">Tài khoản, phân quyền super_admin / parish_admin</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('parish-group') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-home"></i></div>
            <div class="bp-dash-title">Quản lý giáo họ</div>
            <p class="bp-dash-desc">Giáo họ thuộc từng giáo xứ</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('holymanagement') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-bible"></i></div>
            <div class="bp-dash-title">Danh mục tên thánh</div>
            <p class="bp-dash-desc">Dữ liệu dùng chung cho toàn hệ thống</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4">
        <a href="{{ backpack_url('diocese') }}" class="bp-dash-card">
            <div class="bp-dash-icon"><i class="la la-map"></i></div>
            <div class="bp-dash-title">Địa giáo hội</div>
            <p class="bp-dash-desc">Giáo phận, giáo hạt, giáo họ</p>
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

<div class="card mt-2">
    <div class="card-header">Liên kết nhanh</div>
    <div class="card-body">
        <a href="{{ backpack_url('setting') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="la la-cog"></i> Cài đặt
        </a>
        <a href="{{ backpack_url('role') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="la la-user-tag"></i> Vai trò
        </a>
        <a href="{{ backpack_url('permission') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="la la-key"></i> Quyền hạn
        </a>
        <a href="{{ backpack_url('ckfinder') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="la la-folder-open"></i> File manager
        </a>
        <a href="{{ backpack_url('parish-admin-registration') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="la la-user-check"></i> Đăng ký quản trị xứ
        </a>
    </div>
</div>
@endsection
