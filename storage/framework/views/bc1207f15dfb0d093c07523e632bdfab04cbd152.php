<?php $attributes = $attributes->exceptProps([
    'child',
    'class',
    'html',
]); ?>
<?php foreach (array_filter(([
    'child',
    'class',
    'html',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if(count($child) > 0): ?>
    <li <?php echo e($attributes->merge(['class' => 'px-1 dropdown'])); ?>>
        <div class="d-flex align-items-center">
            <?php echo e($slot); ?>

            <i class="bi bi-chevron-down ps-1 fs-9"></i>
        </div>
        <ul class="dropdown-menu">
            <?php $__currentLoopData = $child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.menu.item','data' => ['child' => $menu->child]]); ?>
<?php $component->withName('menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['child' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menu->child)]); ?>
                    <a href="<?php echo e($menu->link); ?>" class="text-decoration-none text-nowrap"><?php echo e($menu->name); ?></a>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </li>
<?php else: ?>
    <?php if($attributes['link'] == url()->current()): ?>
    	<li <?php echo e($attributes->merge(['class' => isset($attributes['class']) ? '' : 'py-2 px-1 active'])); ?>><?php echo e($slot); ?></li>
    <?php else: ?>
    	<li <?php echo e($attributes->merge(['class' => isset($attributes['class']) ? '' : 'py-2 px-2'])); ?>><?php echo e($slot); ?></li>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/menu/item.blade.php ENDPATH**/ ?>