<ul <?php echo e($attributes); ?> class="vertical-menu">
    <?php $__currentLoopData = $menus ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.menu-mobile.item','data' => ['child' => $menu->child,'class' => ''.e($menu->class).'','style' => ''.e($menu->style).'']]); ?>
<?php $component->withName('menu-mobile.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['child' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menu->child),'class' => ''.e($menu->class).'','style' => ''.e($menu->style).'']); ?>
            <a href="<?php echo e($menu->link); ?>" class="p-2 px-0 fs-6 fw-semibold text-uppercase <?php if($menu->class !=''): ?> <?php echo e($menu->class); ?> <?php else: ?> text-dark <?php endif; ?>"><?php if($menu->html !=''): ?><span class="me-2"><?php echo $menu->html; ?></span><?php endif; ?><?php echo e($menu->name); ?></a>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/menu-mobile/index.blade.php ENDPATH**/ ?>