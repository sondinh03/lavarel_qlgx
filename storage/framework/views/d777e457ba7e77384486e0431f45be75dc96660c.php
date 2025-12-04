<?php $attributes = $attributes->exceptProps([
    'label' => '',
    'placeholder' => '-- Chọn --',
    'options' => [],
    'wireModel' => '',
    'required' => false,
    'disabled' => false,
    'error' => null
]); ?>
<?php foreach (array_filter(([
    'label' => '',
    'placeholder' => '-- Chọn --',
    'options' => [],
    'wireModel' => '',
    'required' => false,
    'disabled' => false,
    'error' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="w-full">
    <?php if($label): ?>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            <?php echo e($label); ?>

            <?php if($required): ?>
                <span class="text-red-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="relative">
        <select 
            <?php echo e($wireModel ? "wire:model.live={$wireModel}" : ''); ?>

            <?php echo e($disabled ? 'disabled' : ''); ?>

            <?php echo e($attributes->merge([
                'class' => 'w-full px-4 py-2.5 bg-white border rounded-xl text-slate-900 focus:outline-none focus:ring-2 transition-all ' .
                           ($error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 focus:ring-blue-500 focus:border-transparent') .
                           ($disabled ? ' opacity-50 cursor-not-allowed bg-slate-50' : ' cursor-pointer')
            ])); ?>>
            
            <?php if($placeholder): ?>
                <option value=""><?php echo e($placeholder); ?></option>
            <?php endif; ?>

            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $text): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"><?php echo e($text); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    <?php if($error): ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($error); ?></p>
    <?php endif; ?>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/select-input.blade.php ENDPATH**/ ?>