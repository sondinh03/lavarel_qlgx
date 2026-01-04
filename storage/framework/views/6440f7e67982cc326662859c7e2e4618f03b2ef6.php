<?php $attributes = $attributes->exceptProps([
'wire' => null,
'disabled' => false,
'loading' => false,
'icon' => null, // 'plus', 'edit', 'trash', 'check', 'save'
'variant' => 'primary', // primary, secondary, danger
]); ?>
<?php foreach (array_filter(([
'wire' => null,
'disabled' => false,
'loading' => false,
'icon' => null, // 'plus', 'edit', 'trash', 'check', 'save'
'variant' => 'primary', // primary, secondary, danger
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<button
    <?php echo e($attributes->merge([
        'class' => match($variant) {
            'primary' => 'bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white',
            'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100',
            'danger' => 'bg-red-600 hover:bg-rare-700 text-white',
        } . ' inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed transition-all shadow-sm'
    ])); ?>

    <?php if($wire): ?> wire:click="<?php echo e($wire); ?>" <?php endif; ?>
    <?php if($loading): ?> wire:loading.attr="disabled" <?php endif; ?>
    @disabled($disabled)>

    
    <?php if($icon && !$loading): ?>
    <?php if($wire): ?>
    <svg wire:loading.remove wire:target="<?php echo e($wire); ?>" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php switch($icon):
        case ('plus'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        <?php break; ?>
        <?php case ('edit'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        <?php break; ?>
        <?php case ('trash'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        <?php break; ?>
        <?php case ('check'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 13l4 4L19 7" />
        <?php break; ?>
        <?php endswitch; ?>
    </svg>
    <?php else: ?>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php switch($icon):
        case ('plus'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        <?php break; ?>
        <?php case ('edit'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        <?php break; ?>
        <?php case ('trash'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        <?php break; ?>
        <?php case ('check'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 13l4 4L19 7" />
        <?php break; ?>
        <?php endswitch; ?>
    </svg>
    <?php endif; ?>
    <?php endif; ?>

    <?php echo e($slot); ?>

</button><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/action-button.blade.php ENDPATH**/ ?>