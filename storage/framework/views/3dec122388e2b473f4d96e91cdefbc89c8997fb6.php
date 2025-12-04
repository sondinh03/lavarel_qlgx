<?php $attributes = $attributes->exceptProps([
    'target' => null,
    'message' => 'Đang xử lý...',
]); ?>
<?php foreach (array_filter(([
    'target' => null,
    'message' => 'Đang xử lý...',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div 
    wire:loading 
    <?php if($target): ?> wire:target="<?php echo e($target); ?>" <?php endif; ?>
    class="fixed inset-0 z-[9999] flex items-center justify-center"
    style="display: none;"
>
    
    <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>

    
    <div class="relative z-10 flex flex-col items-center gap-3 bg-white px-6 py-4 rounded-2xl shadow-2xl border border-slate-200/50 min-w-[200px]">
        
        <svg class="animate-spin h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" 
                stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>

        
        <span class="text-sm font-medium text-slate-700 text-center"><?php echo e($message); ?></span>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/loading-indicator.blade.php ENDPATH**/ ?>