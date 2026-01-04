<?php $attributes = $attributes->exceptProps([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'hint' => '',
]); ?>
<?php foreach (array_filter(([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'hint' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div>
    <?php if($label): ?>
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        <?php echo e($label); ?>

        <?php if($required): ?> <span class="text-red-500">*</span> <?php endif; ?>
    </label>
    <?php endif; ?>

    <input
        type="<?php echo e($type); ?>"
        name="<?php echo e($name); ?>"
        placeholder="<?php echo e($placeholder); ?>"
        <?php echo e($attributes->merge([
            'class' => 'w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500'
        ])); ?>

        <?php if($attributes->has('wire:model')): ?> wire:model.defer="<?php echo e($attributes->get('wire:model')); ?>" <?php endif; ?>>

    <?php if($hint): ?>
    <p class="mt-1 text-xs text-slate-500"><?php echo e($hint); ?></p>
    <?php endif; ?>

    
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/form-input.blade.php ENDPATH**/ ?>