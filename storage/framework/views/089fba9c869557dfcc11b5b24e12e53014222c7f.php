<?php $attributes = $attributes->exceptProps([
'title' => '',
'description' => '',
'statValue' => null,
'statLabel' => '',
'iconType' => 'class', // 'class', 'block', 'student', 'teacher'
]); ?>
<?php foreach (array_filter(([
'title' => '',
'description' => '',
'statValue' => null,
'statLabel' => '',
'iconType' => 'class', // 'class', 'block', 'student', 'teacher'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
    <div class="flex items-center justify-between gap-6">
        
        <div class="flex items-center gap-4 min-w-0">
            <?php if($slot->isNotEmpty() || $iconType): ?>
            <div class="w-12 h-12 rounded-xl bg-primary-500 flex items-center justify-center shadow-sm shrink-0">
                <?php if($slot->isNotEmpty()): ?>
                <?php echo e($slot); ?>

                <?php else: ?>
                <?php switch($iconType):
                case ('block'): ?>
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4l8 4-8 4-8-4 8-4z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4 8-4" />
                </svg>
                <?php break; ?>
                <?php case ('student'): ?>
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <?php break; ?>
                <?php case ('teacher'): ?>
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <?php break; ?>
                <?php case ('schoolYear'): ?>
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <?php break; ?>
                <?php default: ?>
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
                <?php endswitch; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-900 truncate">
                    <?php echo e($title); ?>

                </h1>
                <?php if($description): ?>
                <p class="text-sm text-slate-600 mt-1">
                    <?php echo e($description); ?>

                </p>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($statValue !== null): ?>
        <div class="flex items-center gap-4 pl-6 border-l border-slate-200 text-right shrink-0">
            <div>
                <div class="text-3xl font-bold text-primary-600 leading-none">
                    <?php echo e($statValue); ?>

                </div>
                <div class="text-xs text-slate-600 font-medium mt-1">
                    <?php echo e($statLabel); ?>

                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/page-header.blade.php ENDPATH**/ ?>