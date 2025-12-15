<?php $attributes = $attributes->exceptProps(['name', 'isChuNhiem' => false, 'size' => '8']); ?>
<?php foreach (array_filter((['name', 'isChuNhiem' => false, 'size' => '8']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
    <div class="w-<?php echo e($size); ?> h-<?php echo e($size); ?> <?php echo e($isChuNhiem ? 'bg-purple-500' : 'bg-slate-400'); ?> rounded-full flex items-center justify-center flex-shrink-0">
        <span class="text-white font-semibold text-xs"><?php echo e(mb_substr($name, 0, 2)); ?></span>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-medium text-slate-900 text-sm truncate"><?php echo e($name); ?></p>
        <?php if($isChuNhiem): ?>
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">Chủ nhiệm</span>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/teacher/badge.blade.php ENDPATH**/ ?>