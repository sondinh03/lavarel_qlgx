<?php $attributes = $attributes->exceptProps(['paginator', 'perPageOptions' => [10, 15, 25, 50, 100]]); ?>
<?php foreach (array_filter((['paginator', 'perPageOptions' => [10, 15, 25, 50, 100]]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($paginator->hasPages()): ?>
<div class="px-6 py-4 bg-white border-t border-slate-200">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

        
        <div class="text-sm text-slate-600">
            Hiển thị
            <span class="font-semibold text-slate-900"><?php echo e($paginator->firstItem() ?? 0); ?></span>
            đến
            <span class="font-semibold text-slate-900"><?php echo e($paginator->lastItem() ?? 0); ?></span>
            trong tổng số
            <span class="font-semibold text-slate-900"><?php echo e($paginator->total()); ?></span>
            kết quả
        </div>

        
        <nav class="flex items-center gap-2">

            
            <?php if($paginator->onFirstPage()): ?>
                <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            <?php else: ?>
                <button
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg
                           hover:bg-slate-50 active:scale-95 transition-all
                           disabled:opacity-50">
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            <?php endif; ?>

            
            <div class="hidden sm:flex items-center gap-1">
                <?php $__currentLoopData = $paginator->getUrlRange(1, $paginator->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="px-3 py-2 text-sm font-bold text-white
                                     bg-primary-600 rounded-lg shadow-sm">
                            <?php echo e($page); ?>

                        </span>
                    <?php else: ?>
                        <button
                            wire:click="gotoPage(<?php echo e($page); ?>)"
                            wire:loading.attr="disabled"
                            class="px-3 py-2 text-sm font-medium text-slate-700
                                   bg-white border border-slate-300 rounded-lg
                                   hover:bg-slate-50 active:scale-95 transition-all
                                   disabled:opacity-50">
                            <?php echo e($page); ?>

                        </button>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="sm:hidden px-3 py-2 text-sm font-medium text-slate-700
                        bg-slate-50 border border-slate-200 rounded-lg">
                <?php echo e($paginator->currentPage()); ?> / <?php echo e($paginator->lastPage()); ?>

            </div>

            
            <?php if($paginator->hasMorePages()): ?>
                <button
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-white border border-slate-300 rounded-lg
                           hover:bg-slate-50 active:scale-95 transition-all
                           disabled:opacity-50">
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            <?php else: ?>
                <span class="px-3 py-2 text-sm text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            <?php endif; ?>
        </nav>

        
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-600 whitespace-nowrap">Hiển thị:</label>
            <select
                wire:model.live="perPage"
                class="px-3 py-2 text-sm border border-slate-300 rounded-lg
                       focus:ring-2 focus:ring-primary-500
                       focus:border-transparent transition-all">
                <?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($option); ?>"><?php echo e($option); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

    </div>
</div>
<?php endif; ?>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/pagination.blade.php ENDPATH**/ ?>