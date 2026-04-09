<div class="flex items-center h-14">
    <nav 
        class="flex items-center text-sm text-slate-500"
        aria-label="Breadcrumb"
    >
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php if($index > 0): ?>
                <svg class="w-4 h-4 mx-2 text-slate-300"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7" />
                </svg>
            <?php endif; ?>

            <?php if(!empty($item['url']) && $index !== count($items) - 1): ?>
                <a href="<?php echo e($item['url']); ?>"
                   class="hover:text-slate-800 transition truncate max-w-[160px]">
                    <?php echo e($item['label']); ?>

                </a>
            <?php else: ?>
                <span class="font-semibold text-slate-800 truncate max-w-[200px]">
                    <?php echo e($item['label']); ?>

                </span>
            <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>