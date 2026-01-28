<?php $attributes = $attributes->exceptProps([
    'placeholder' => 'Tìm kiếm...',
    'wireModel' => 'search',
    'debounce' => '500ms'
]); ?>
<?php foreach (array_filter(([
    'placeholder' => 'Tìm kiếm...',
    'wireModel' => 'search',
    'debounce' => '500ms'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="relative flex-1">
    
    <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>

    
    <input 
        type="text"
        wire:model.live.debounce.<?php echo e($debounce); ?>="<?php echo e($wireModel); ?>"
        placeholder="<?php echo e($placeholder); ?>"
        <?php echo e($attributes->merge([
            'class' => 'w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all'
        ])); ?>>

    
    <div wire:loading wire:target="<?php echo e($wireModel); ?>" class="absolute right-4 top-1/2 -translate-y-1/2">
        <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    
    <button 
        wire:click="$set('<?php echo e($wireModel); ?>', '')"
        type="button"
        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
        x-data
        x-show="$wire.<?php echo e($wireModel); ?>.length > 0"
        x-transition>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/search-input.blade.php ENDPATH**/ ?>