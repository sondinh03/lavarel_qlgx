<?php $attributes = $attributes->exceptProps([
'items' => [],
'separator' => 'chevron', // 'chevron', 'slash', 'arrow'
'size' => 'md' // 'sm', 'md', 'lg'
]); ?>
<?php foreach (array_filter(([
'items' => [],
'separator' => 'chevron', // 'chevron', 'slash', 'arrow'
'size' => 'md' // 'sm', 'md', 'lg'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
$sizeClasses = [
'sm' => 'text-xs',
'md' => 'text-sm',
'lg' => 'text-base'
];

$separatorIcons = [
'chevron' => '<svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
</svg>',
'slash' => '<span class="text-slate-400 mx-2">/</span>',
'arrow' => '<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
</svg>'
];

$textSize = $sizeClasses[$size] ?? $sizeClasses['md'];
?>

<nav <?php echo e($attributes->merge(['class' => 'mb-4 sm:mb-6'])); ?> aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 flex-wrap"
        itemscope
        itemtype="https://schema.org/BreadcrumbList">

        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="inline-flex items-center"
            itemprop="itemListElement"
            itemscope
            itemtype="https://schema.org/ListItem">

            
            <?php if($index > 0): ?>
            <span class="mx-1 md:mx-2" aria-hidden="true">
                <?php echo $separatorIcons[$separator]; ?>

            </span>
            <?php endif; ?>

            
            <?php if($index === 0 && !isset($item['icon'])): ?>
            <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            <?php endif; ?>

            
            <?php if(isset($item['icon']) && $index !== 0): ?>
            <span class="mr-1.5 text-slate-500" aria-hidden="true">
                <?php echo $item['icon']; ?>

            </span>
            <?php endif; ?>

            
            <?php if(isset($item['url']) && $index !== count($items) - 1): ?>
            <a href="<?php echo e($item['url']); ?>"
                class="inline-flex items-center <?php echo e($textSize); ?> font-medium text-slate-600 hover:text-blue-600 transition-colors duration-200"
                itemprop="item">
                <span itemprop="name"><?php echo e($item['label']); ?></span>
            </a>
            <?php else: ?>
            <span class="inline-flex items-center <?php echo e($textSize); ?> font-semibold text-slate-900"
                itemprop="item"
                aria-current="page">
                <span itemprop="name"><?php echo e($item['label']); ?></span>
            </span>
            <?php endif; ?>

            <meta itemprop="position" content="<?php echo e($index + 1); ?>">
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>