<?php $attributes = $attributes->exceptProps(['teacherNames' => [], 'teacherCount' => 0]); ?>
<?php foreach (array_filter((['teacherNames' => [], 'teacherCount' => 0]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    Giáo lý viên phụ trách (<?php echo e($teacherCount); ?> người)
</div>
<div class="space-y-2">
    <?php $__currentLoopData = $teacherNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="flex items-center gap-3 text-sm">
        <div class="w-2 h-2 <?php echo e($index === 0 ? 'bg-purple-600' : 'bg-slate-400'); ?> rounded-full"></div>
        <span class="<?php echo e($index === 0 ? 'font-semibold text-purple-900' : 'text-slate-700'); ?>"><?php echo e(trim($name)); ?></span>
        <?php if($index === 0): ?>
        <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">Chủ nhiệm</span>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/teacher/popup.blade.php ENDPATH**/ ?>