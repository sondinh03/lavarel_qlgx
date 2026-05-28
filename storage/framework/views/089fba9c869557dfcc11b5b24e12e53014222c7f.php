<?php $attributes = $attributes->exceptProps([
    'title' => '',
    'description' => '',
    'count' => null,

    // Optional iOS-style enhancements (backwards compatible)
    'iconType' => null,   // e.g. attendance, score, students...
    'statValue' => null,  // number/string
    'statLabel' => null,  // string
]); ?>
<?php foreach (array_filter(([
    'title' => '',
    'description' => '',
    'count' => null,

    // Optional iOS-style enhancements (backwards compatible)
    'iconType' => null,   // e.g. attendance, score, students...
    'statValue' => null,  // number/string
    'statLabel' => null,  // string
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $icons = [
        'attendance' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'score'      => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'students'   => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'default'    => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
    ];

    $iconPath = $icons[$iconType] ?? ($iconType ? $icons['default'] : null);
?>

<div class="px-6 py-5 border-b border-slate-100 bg-white rounded-t-2xl">
    <div class="flex items-start justify-between gap-4">

        
        <div class="flex items-start gap-3 min-w-0">
            <?php if($iconPath): ?>
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($iconPath); ?>" />
                    </svg>
                </div>
            <?php endif; ?>

            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 truncate">
                    <?php echo e($title); ?>


                    <?php if($count !== null): ?>
                        <span class="ml-2 text-sm font-normal text-slate-500">(<?php echo e($count); ?>)</span>
                    <?php endif; ?>
                </h1>

                <?php if($description): ?>
                    <p class="text-sm text-slate-500 mt-0.5">
                        <?php echo e($description); ?>

                    </p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="flex items-center gap-3 shrink-0">
            <?php if($statValue !== null && $statLabel): ?>
                <div class="hidden sm:flex flex-col items-end px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-lg font-extrabold text-slate-900 leading-tight"><?php echo e($statValue); ?></div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide"><?php echo e($statLabel); ?></div>
                </div>
            <?php endif; ?>

            <div class="flex items-center gap-2">
                <?php echo e($actions ?? ''); ?>

            </div>
        </div>

    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/page-header.blade.php ENDPATH**/ ?>