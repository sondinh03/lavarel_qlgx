{{-- Sidebar super_admin — panel vận hành hệ thống --}}

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}
    </a>
</li>

{{-- Hệ thống --}}
<li class="nav-title">Hệ thống</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon las la-user-shield"></i>
        <span>{{ __('backend.account_management') }}</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('user') }}">
                <i class="nav-icon las la-user"></i> {{ __('backend.user') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('parish-admin-registration') }}">
                <i class="nav-icon las la-user-check"></i> Đăng ký quản trị xứ
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('role') }}">
                <i class="nav-icon las la-user-tag"></i> {{ __('backend.role') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('permission') }}">
                <i class="nav-icon las la-key"></i> {{ __('backend.permission') }}
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('setting') }}">
        <i class="nav-icon la la-cog"></i> {{ __('backend.settings') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('backup') }}">
        <i class="nav-icon la la-hdd-o"></i> {{ __('backend.backups') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('log') }}">
        <i class="nav-icon la la-terminal"></i> {{ __('backend.logs') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('ckfinder') }}">
        <i class="nav-icon la la-folder-open"></i> {{ trans('backpack::crud.file_manager') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('redirect') }}">
        <i class="nav-icon la la-route"></i> {{ __('backend.redirects') }}
    </a>
</li>

{{-- Địa giáo hội --}}
<li class="nav-title">Địa giáo hội</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('parish-management') }}">
        <i class="nav-icon la la-church"></i> {{ __('backend.parish_management') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('parish-group') }}">
        <i class="nav-icon la la-home"></i> Giáo họ
    </a>
</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon la la-map"></i>
        <span>Phân cấp địa bàn</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('diocese') }}">
                <i class="nav-icon la la-globe"></i> {{ __('backend.dioceses') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('deanery') }}">
                <i class="nav-icon la la-sitemap"></i> {{ __('backend.deanerys') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('family-area') }}">
                <i class="nav-icon la la-layer-group"></i> {{ __('backend.family_areas') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('parish') }}">
                <i class="nav-icon la la-archive"></i> Giáo họ (legacy)
            </a>
        </li>
    </ul>
</li>

{{-- Danh mục --}}
<li class="nav-title">Danh mục dùng chung</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon la la-list"></i>
        <span>Danh mục tra cứu</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('holymanagement') }}">
                <i class="nav-icon la la-bible"></i> {{ __('backend.holymanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('ethnicmanagement') }}">
                <i class="nav-icon la la-users"></i> {{ __('backend.ethnicmanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('positionmanagement') }}">
                <i class="nav-icon la la-id-badge"></i> {{ __('backend.positionmanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('careermanagement') }}">
                <i class="nav-icon la la-briefcase"></i> {{ __('backend.careermanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('levelmanagement') }}">
                <i class="nav-icon la la-graduation-cap"></i> {{ __('backend.levelmanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('languagemanagement') }}">
                <i class="nav-icon la la-language"></i> {{ __('backend.languagemanagements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('sacrament-giver') }}">
                <i class="nav-icon la la-pray"></i> {{ __('backend.sacrament_givers') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('sponsor') }}">
                <i class="nav-icon la la-hands-helping"></i> {{ __('backend.sponsors') }}
            </a>
        </li>
    </ul>
</li>

{{-- Nội dung website --}}
<li class="nav-title">Nội dung website</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon la la-pager"></i>
        <span>{{ __('backend.layout_management') }}</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('menu') }}">
                <i class="nav-icon la la-stream"></i> Menus
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('page') }}">
                <i class="nav-icon la la-file-alt"></i> {{ __('backend.Pages') }}
            </a>
        </li>
    </ul>
</li>

{{-- CRUD legacy (parish_admin dùng Livewire; giữ cho super_admin) --}}
<li class="nav-title">CRUD legacy</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon las la-place-of-worship"></i>
        <span>Giáo dân &amp; gia đình</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('parishioners') }}">
                <i class="nav-icon la la-address-card"></i> {{ __('backend.parishioners') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('family') }}">
                <i class="nav-icon la la-campground"></i> {{ __('backend.families') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('marriage-announcement') }}">
                <i class="nav-icon la la-heart"></i> {{ __('backend.marriage_announcements') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('association') }}">
                <i class="nav-icon la la-users-cog"></i> {{ __('backend.associations') }}
            </a>
        </li>
    </ul>
</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon las la-graduation-cap"></i>
        <span>Giáo lý</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('student') }}">
                <i class="nav-icon la la-user-graduate"></i> {{ __('backend.students') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('lop') }}">
                <i class="nav-icon la la-school"></i> {{ __('backend.lops') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('grade-level') }}">
                <i class="nav-icon la la-cubes"></i> {{ __('backend.Block') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('nam-hoc') }}">
                <i class="nav-icon la la-calendar"></i> {{ __('backend.namhoc') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('teacher') }}">
                <i class="nav-icon la la-chalkboard-teacher"></i> {{ __('backend.teachers') }}
            </a>
        </li>
    </ul>
</li>
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle">
        <i class="nav-icon la la-tools"></i>
        <span>Công cụ đặc biệt</span>
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('decen') }}">
                <i class="nav-icon la la-key"></i> {{ __('backend.decens') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('set-admin') }}">
                <i class="nav-icon la la-user-cog"></i> Set admins
            </a>
        </li>
    </ul>
</li>

