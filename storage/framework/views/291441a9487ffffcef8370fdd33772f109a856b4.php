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

<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">
    
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.loading-indicator','data' => ['target' => 'selectedNamHoc,selectedKhoi,resetFilters']]); ?>
<?php $component->withName('loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['target' => 'selectedNamHoc,selectedKhoi,resetFilters']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

    
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <img src="<?php echo e(url(config('settings.logo'))); ?>" alt="Logo" class="h-10 w-auto">
                        <span class="text-xl font-bold text-indigo-700 hidden sm:block">
                            <?php echo e(config('settings.web_name')); ?>

                        </span>
                    </a>
                </div>

                
                <nav class="hidden md:flex space-x-8">
                    <a href="https://mvqlgiaoxu.org/tim-kiem" class="text-gray-700 hover:text-indigo-600 font-medium transition">
                        Kết quả học tập
                    </a>
                </nav>

                
                <div class="flex items-center space-x-4">
                    <button id="fullscreen-button" class="text-gray-600 hover:text-indigo-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                    <!-- <?php if(auth()->guard()->check()): ?>
                        <?php if (isset($component)) { $__componentOriginal4ef3f5a8a8724cef81dcbc68e612558cabc1c480 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\Nav::class, []); ?>
<?php $component->withName('nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4ef3f5a8a8724cef81dcbc68e612558cabc1c480)): ?>
<?php $component = $__componentOriginal4ef3f5a8a8724cef81dcbc68e612558cabc1c480; ?>
<?php unset($__componentOriginal4ef3f5a8a8724cef81dcbc68e612558cabc1c480); ?>
<?php endif; ?> 
                    <?php endif; ?> -->
                </div>

                
                <button class="md:hidden burger-menu text-gray-700">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            
            <?php echo $__env->yieldContent('content_top'); ?>
            <?php echo $__env->yieldContent('column_left'); ?>

            
            <?php echo $__env->yieldContent('content'); ?>

            <?php echo $__env->yieldContent('column_right'); ?>
            <?php echo $__env->yieldContent('content_bottom'); ?>
        </div>
    </main>

    
    <?php if ($__env->exists('frontend.footer')) echo $__env->make('frontend.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <button class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition hover:scale-110" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    
    <script src="<?php echo e(mix('js/manifest.js')); ?>"></script>
    <script src="<?php echo e(mix('js/vendor.js')); ?>"></script>
    <script src="<?php echo e(mix('js/app.js')); ?>"></script> 

    
    <script>
        document.getElementById('fullscreen-button')?.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen error:', err);
                });
            } else {
                document.exitFullscreen();
            }
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php echo \Livewire\Livewire::scripts(); ?>

</body>
html><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/main.blade.php ENDPATH**/ ?>