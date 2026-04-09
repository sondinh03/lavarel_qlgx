<?php $attributes = $attributes->exceptProps([
    'title' => '',
    'description' => '',
    'count' => null,
]); ?>
<?php foreach (array_filter(([
    'title' => '',
    'description' => '',
    'count' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="px-6 py-4 border-b border-slate-200 bg-white">
    <div class="flex items-center justify-between gap-4">
        
        
        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-slate-900 truncate">
                <?php echo e($title); ?>

                
                <?php if($count !== null): ?>
                    <span class="ml-2 text-sm font-normal text-slate-500">
                        (<?php echo e($count); ?>)
                    </span>
                <?php endif; ?>
            </h1>

            <?php if($description): ?>
                <p class="text-sm text-slate-500 mt-0.5">
                    <?php echo e($description); ?>

                </p>
            <?php endif; ?>
        </div>

        
        <div class="flex items-center gap-2 shrink-0">
            <?php echo e($actions ?? ''); ?>

        </div>

    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/page-header.blade.php ENDPATH**/ ?>