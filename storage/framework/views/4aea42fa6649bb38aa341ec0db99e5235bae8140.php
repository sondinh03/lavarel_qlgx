<?php $attributes = $attributes->exceptProps([
    'label' => '',
    'model' => '',
    'options' => [],
    'placeholder' => '-- Chọn --',
    'disabled' => false,
]); ?>
<?php foreach (array_filter(([
    'label' => '',
    'model' => '',
    'options' => [],
    'placeholder' => '-- Chọn --',
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'w-24'])); ?>> 
    <?php if($label): ?>
    <label class="block text-sm font-semibold text-slate-700 mb-2">
        <?php echo e($label); ?>

    </label>
    <?php endif; ?>
    
    <select 
        wire:model.live="<?php echo e($model); ?>"
        @disabled($disabled)
        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl 
               text-slate-900 text-sm
               focus:outline-none focus:ring-2 focus:ring-primary-500 
               disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed
               transition-colors">
        <option value=""><?php echo e($placeholder); ?></option>
        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/filter-select.blade.php ENDPATH**/ ?>