<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item nav-dropdown">
	<a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-place-of-worship"></i>
        <span>{{ __('backend.utilitiesmanagement') }}</span>
    </a>
    <ul class="nav-dropdown-items">
    	<li class="nav-item"><a class="nav-link" href="{{ backpack_url('holymanagement') }}"><i class="nav-icon la la-bible"></i> {{ __('backend.holymanagements') }}</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('sacrament-giver') }}'><i class='nav-icon la la-graduation-cap'></i> {{ __('backend.sacrament_givers') }}</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('sponsor') }}'><i class='nav-icon la la-universal-access'></i> {{ __('backend.sponsors') }}</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('association') }}'><i class='nav-icon la la-skating'></i> {{ __('backend.associations') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('parishioners') }}'><i class='nav-icon la la-address-card'></i> {{ __('backend.parishioners') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('family') }}'><i class='nav-icon la la-campground'></i> {{ __('backend.families') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('marriage-announcement') }}'><i class='nav-icon la la-gratipay'></i> {{ __('backend.marriage_announcements') }}</a></li>
    </ul>
</li>
<li class='nav-item nav-dropdown'>
	<a class='nav-link nav-dropdown-toggle' href='#'><i class='nav-icon las la-graduation-cap'></i> 
		<span>{{__('backend.DoctrinalManagements')}}</span>
	</a>
    <ul class="nav-dropdown-items">
    	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('student') }}'><i class='nav-icon la la-briefcase'></i> {{__('backend.students')}}</a></li>
    	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('lop') }}'><i class='nav-icon la la-school'></i> {{__('backend.lops')}}</a></li>
    	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('block') }}'><i class='nav-icon la la-cubes'></i> {{__('backend.Block')}}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('teacher') }}'><i class='nav-icon la la-chalkboard-teacher'></i> {{__('backend.teachers')}}</a></li>
    </ul>
</li>
<li class="nav-item nav-dropdown">
	<a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-layer-group"></i>
        <span>{{ __('backend.utilities') }}</span>
    </a>
    <ul class="nav-dropdown-items">
    	<li class="nav-item"><a class="nav-link" href="{{ backpack_url('ethnicmanagement') }}"><i class="nav-icon la la-address-card"></i> {{ __('backend.ethnicmanagements') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('positionmanagement') }}"><i class="nav-icon la la-crosshairs"></i> {{ __('backend.positionmanagements') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('careermanagement') }}"><i class="nav-icon la la-id-card-alt"></i> {{ __('backend.careermanagements') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('levelmanagement') }}"><i class="nav-icon la la-level-up-alt"></i> {{ __('backend.levelmanagements') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('languagemanagement') }}"><i class="nav-icon la la-sign-language"></i> {{ __('backend.languagemanagements') }}</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('family-area') }}'><i class='nav-icon la la-feather-alt'></i> {{ __('backend.family_areas') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('diocese') }}'><i class='nav-icon la la-industry'></i> {{ __('backend.dioceses') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('deanery') }}'><i class='nav-icon la la-home'></i> {{ __('backend.deanerys') }}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('parish-management') }}"><i class="nav-icon la la-biohazard"></i> {{ __('backend.parish_management') }}</a></li>
        
    </ul>
</li>

<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon la la-th"></i>
        <span>{{ __('backend.layout_management') }}</span></a>
    <ul class="nav-dropdown-items">
    	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('menu') }}'><i class='nav-icon la la-stream'></i> Menus</a></li>
    	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon la la-pager'></i> {{ __('backend.Pages') }}</a></li>
    </ul>
</li>
<!-- Users, Roles Permissions -->
<li class="nav-item nav-dropdown">
    <a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-user-circle"></i>
        <span>{{ __('backend.account_management') }}</span>
    </a>
    <ul class="nav-dropdown-items">
    	<li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon las la-user"></i> {{ __('backend.user') }}</a></li>
    	<li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon las la-user-cog"></i> {{ __('backend.role') }}</a></li>
    	<li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon las la-key"></i> {{ __('backend.permission') }}</a></li>
    </ul>
</li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('ckfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('backup') }}'><i class='nav-icon la la-hdd-o'></i> {{ __('backend.backups') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> {{ __('backend.logs') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon la la-cog'></i> <span>{{ __('backend.settings') }}</span></a></li>
<li class='nav-item'>
    <a class='nav-link' href='{{ backpack_url('redirect') }}'>
        <i class="nav-icon la la-route"></i> {{ __('backend.redirects') }}
    </a>
</li>