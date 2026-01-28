<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
?>
<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('dashboard')); ?>"><i class="la la-home nav-icon"></i> <?php echo e(trans('backpack::base.dashboard')); ?></a></li>
<li class="nav-item nav-dropdown">
	<a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-place-of-worship"></i>
        <span><?php echo e(__('backend.utilitiesmanagement')); ?></span>
    </a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('parishioners')); ?>'><i class='nav-icon la la-address-card'></i> <?php echo e(__('backend.parishioners')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('family')); ?>'><i class='nav-icon la la-campground'></i> <?php echo e(__('backend.families')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('marriage-announcement')); ?>'><i class='nav-icon la la-gratipay'></i> <?php echo e(__('backend.marriage_announcements')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('association')); ?>'><i class='nav-icon la la-skating'></i> <?php echo e(__('backend.associations')); ?></a></li>
    </ul>
</li>
<li class='nav-item nav-dropdown'>
	<a class='nav-link nav-dropdown-toggle' href='#'><i class='nav-icon las la-graduation-cap'></i> 
		<span><?php echo e(__('backend.DoctrinalManagements')); ?></span>
	</a>
    <ul class="nav-dropdown-items">
    	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('student')); ?>'><i class='nav-icon la la-briefcase'></i> <?php echo e(__('backend.students')); ?></a></li>
    	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('lop')); ?>'><i class='nav-icon la la-school'></i> <?php echo e(__('backend.lops')); ?></a></li>
    	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('block')); ?>'><i class='nav-icon la la-cubes'></i> <?php echo e(__('backend.Block')); ?></a></li>
    	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('nam-hoc')); ?>'><i class='nav-icon la la-handshake'></i> <?php echo e(__('backend.namhoc')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('teacher')); ?>'><i class='nav-icon la la-chalkboard-teacher'></i> <?php echo e(__('backend.teachers')); ?></a></li>
    </ul>
</li>
<li class="nav-item nav-dropdown">
	<a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-layer-group"></i>
        <span><?php echo e(__('backend.utilities')); ?></span>
    </a>
    <ul class="nav-dropdown-items">
    	<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('holymanagement')); ?>"><i class="nav-icon la la-bible"></i> <?php echo e(__('backend.holymanagements')); ?></a></li>
		<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('sacrament-giver')); ?>'><i class='nav-icon la la-graduation-cap'></i> <?php echo e(__('backend.sacrament_givers')); ?></a></li>
		<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('sponsor')); ?>'><i class='nav-icon la la-universal-access'></i> <?php echo e(__('backend.sponsors')); ?></a></li>
		
    	<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('ethnicmanagement')); ?>"><i class="nav-icon la la-address-card"></i> <?php echo e(__('backend.ethnicmanagements')); ?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('positionmanagement')); ?>"><i class="nav-icon la la-crosshairs"></i> <?php echo e(__('backend.positionmanagements')); ?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('careermanagement')); ?>"><i class="nav-icon la la-id-card-alt"></i> <?php echo e(__('backend.careermanagements')); ?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('levelmanagement')); ?>"><i class="nav-icon la la-level-up-alt"></i> <?php echo e(__('backend.levelmanagements')); ?></a></li>
		<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('languagemanagement')); ?>"><i class="nav-icon la la-sign-language"></i> <?php echo e(__('backend.languagemanagements')); ?></a></li>
		<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('family-area')); ?>'><i class='nav-icon la la-feather-alt'></i> <?php echo e(__('backend.family_areas')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('diocese')); ?>'><i class='nav-icon la la-industry'></i> <?php echo e(__('backend.dioceses')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('deanery')); ?>'><i class='nav-icon la la-home'></i> <?php echo e(__('backend.deanerys')); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('parish-management')); ?>"><i class="nav-icon la la-biohazard"></i> <?php echo e(__('backend.parish_management')); ?></a></li>
        <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('parish')); ?>'><i class='nav-icon la la-handshake'></i> <?php echo e(__('backend.parish')); ?></a></li>
        <?php
        $user = Auth::user();
        $user_array = User::where('id', '<=', 2)->get()->toArray();
        if(!empty($user)){
            foreach($user_array as $usex){
                if($usex['id'] == $user->id){
                    ?>
                    <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('decen')); ?>'><i class='nav-icon la la-key'></i> <?php echo e(__('backend.decens')); ?></a></li>
                    <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('set-admin')); ?>'><i class='nav-icon la la-question'></i> Set admins</a></li>
                    <?php
                }
            }
        }
        ?>      
    </ul>
</li>



<?php
$user = Auth::user();
$user_array = User::where('id', '<=', 2)->get()->toArray();
if(!empty($user)){
    foreach($user_array as $usex){
        if($usex['id'] == $user->id){
            ?>
            <li class="nav-item nav-dropdown">
                <a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon la la-th"></i>
                    <span><?php echo e(__('backend.layout_management')); ?></span></a>
                <ul class="nav-dropdown-items">
                	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('menu')); ?>'><i class='nav-icon la la-stream'></i> Menus</a></li>
                	<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('page')); ?>'><i class='nav-icon la la-pager'></i> <?php echo e(__('backend.Pages')); ?></a></li>
                </ul>
            </li>

            <!-- Users, Roles Permissions -->
            <li class="nav-item nav-dropdown">
                <a href="#" class="nav-link nav-dropdown-toggle no-underline"><i class="nav-icon las la-user-circle"></i>
                    <span><?php echo e(__('backend.account_management')); ?></span>
                </a>
                <ul class="nav-dropdown-items">
                	<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('user')); ?>"><i class="nav-icon las la-user"></i> <?php echo e(__('backend.user')); ?></a></li>
                	<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('role')); ?>"><i class="nav-icon las la-user-cog"></i> <?php echo e(__('backend.role')); ?></a></li>
                	<li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('permission')); ?>"><i class="nav-icon las la-key"></i> <?php echo e(__('backend.permission')); ?></a></li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(backpack_url('ckfinder')); ?>"><i class="nav-icon la la-files-o"></i> <span><?php echo e(trans('backpack::crud.file_manager')); ?></span></a></li>
            <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('backup')); ?>'><i class='nav-icon la la-hdd-o'></i> <?php echo e(__('backend.backups')); ?></a></li>
            <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('log')); ?>'><i class='nav-icon la la-terminal'></i> <?php echo e(__('backend.logs')); ?></a></li>
            <li class='nav-item'>
                <a class='nav-link' href='<?php echo e(backpack_url('redirect')); ?>'>
                    <i class="nav-icon la la-route"></i> <?php echo e(__('backend.redirects')); ?>

                </a>
            </li>
            <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('setting')); ?>'><i class='nav-icon la la-cog'></i> <span><?php echo e(__('backend.settings')); ?></span></a></li>
            <?php
        }
    }
}

?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/base/inc/sidebar_content.blade.php ENDPATH**/ ?>