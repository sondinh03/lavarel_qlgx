<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', config('settings.web_name', 'Quản Lý Giáo Xứ')); ?></title>

    <?php if ($__env->exists('frontend.layout.meta')) echo $__env->make('frontend.layout.meta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <link href="<?php echo e(mix('css/app.css')); ?>" rel="stylesheet">
    <?php echo \Livewire\Livewire::styles(); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 antialiased">

    
    <main class="flex-1">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <?php echo $__env->yieldContent('content_top'); ?>
            <div class="w-full">
                <section>
                    <?php echo $__env->yieldContent('content'); ?>
                </section>
            </div>
            <?php echo $__env->yieldContent('content_bottom'); ?>
        </div>
    </main>

    
    <?php echo $__env->make('frontend.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <script src="<?php echo e(mix('js/manifest.js')); ?>"></script>
    <script src="<?php echo e(mix('js/vendor.js')); ?>"></script>
    <script src="<?php echo e(mix('js/app.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?php echo \Livewire\Livewire::scripts(); ?>

</body>

</html><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/landing.blade.php ENDPATH**/ ?>