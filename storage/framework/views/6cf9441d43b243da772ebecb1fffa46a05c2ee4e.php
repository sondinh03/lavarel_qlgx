<?php $attributes = $attributes->exceptProps([
'sortable' => false,
'sortField' => '',
'currentSort' => '',
'sortDirection' => 'asc',
'align' => 'left'
]); ?>
<?php foreach (array_filter(([
'sortable' => false,
'sortField' => '',
'currentSort' => '',
'sortDirection' => 'asc',
'align' => 'left'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
$alignClass = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left'
};

$justifyClass = match($align) {
'center' => 'justify-center',
'right' => 'justify-end',
default => 'justify-start'
};
?>

<th <?php echo e($attributes->merge([
    'class' => "px-4 py-3 {$alignClass} text-xs font-semibold text-slate-600"
])); ?>>
    <?php if($sortable && $sortField): ?>
    <button
        wire:click="sortBy('<?php echo e($sortField); ?>')"
        class="flex items-center h-full gap-2 p w-full <?php echo e($justifyClass); ?>

           transition-all duration-200
           hover:text-slate-800 hover:opacity-90
           focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">

        <span class="uppercase tracking-wide"><?php echo e($slot); ?></span>

        
        <?php if($currentSort === $sortField): ?>
        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <?php if($sortDirection === 'asc'): ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            <?php else: ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            <?php endif; ?>
        </svg>
        <?php else: ?>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
        <?php endif; ?>

    </button>
    <?php else: ?>
        <span class="uppercase tracking-wide">
            <?php echo e($slot); ?>

        </span>
    <?php endif; ?>
</th><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/table-header.blade.php ENDPATH**/ ?>