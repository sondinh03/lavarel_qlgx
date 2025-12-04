<ul <?php echo e($attributes->merge(['class' => 'primary-menu navbar d-none d-md-flex align-items-center justify-content-center list-unstyled ps-0 py-0 mb-0'])); ?>>
    <?php $__currentLoopData = $menus ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.menu.item','data' => ['child' => $menu->child,'class' => ''.e($menu->class).'','style' => ''.e($menu->style).'','link' => ''.e($menu->link).'']]); ?>
<?php $component->withName('menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['child' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menu->child),'class' => ''.e($menu->class).'','style' => ''.e($menu->style).'','link' => ''.e($menu->link).'']); ?>
            <a href="<?php echo e($menu->link); ?>" class="text-decoration-none fw-semibold py-2 text-uppercase fs-8 <?php if($menu->class !=''): ?> <?php echo e($menu->class); ?> <?php else: ?> <?php endif; ?>"><span class="me-2"><?php echo $menu->html; ?></span><?php echo e($menu->name); ?></a>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/menu/index.blade.php ENDPATH**/ ?>