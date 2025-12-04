
<?php $attributes = $attributes->exceptProps(['type' => 'success', 'duration' => 3000]); ?>
<?php foreach (array_filter((['type' => 'success', 'duration' => 3000]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
$configs = [
    'success' => [
        'bgColor' => 'bg-green-50',
        'borderColor' => 'border-green-200',
        'iconBg' => 'bg-green-100',
        'iconColor' => 'text-green-600',
        'textColor' => 'text-green-900',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
    ],
    'error' => [
        'bgColor' => 'bg-red-50',
        'borderColor' => 'border-red-200',
        'iconBg' => 'bg-red-500',
        'iconColor' => 'text-white',
        'textColor' => 'text-red-900',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />'
    ],
    'warning' => [
        'bgColor' => 'bg-amber-50',
        'borderColor' => 'border-amber-200',
        'iconBg' => 'bg-amber-100',
        'iconColor' => 'text-amber-600',
        'textColor' => 'text-amber-900',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
    ],
    'info' => [
        'bgColor' => 'bg-blue-50',
        'borderColor' => 'border-blue-200',
        'iconBg' => 'bg-blue-100',
        'iconColor' => 'text-blue-600',
        'textColor' => 'text-blue-900',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
    ]
];

$config = $configs[$type] ?? $configs['success'];
?>

<div x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, <?php echo e($duration); ?>)"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-4 right-4 z-50 max-w-sm w-full sm:w-auto">
    <div class="<?php echo e($config['bgColor']); ?> border <?php echo e($config['borderColor']); ?> rounded-xl shadow-lg p-4 flex items-start gap-3">
        <div class="w-8 h-8 <?php echo e($config['iconBg']); ?> rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 <?php echo e($config['iconColor']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?php echo $config['icon']; ?>

            </svg>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="<?php echo e($config['textColor']); ?> font-medium text-sm"><?php echo e($slot); ?></p>
        </div>
        <button @click="show = false" class="flex-shrink-0 <?php echo e($config['textColor']); ?> opacity-50 hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/toast-notification.blade.php ENDPATH**/ ?>