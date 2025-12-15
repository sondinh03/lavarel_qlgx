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

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col" x-data="{open:false}">

    
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

    
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">

                
                <a href="/" class="flex items-center gap-3">
                    <img src="<?php echo e(url(config('settings.logo'))); ?>" class="h-10 w-auto" alt="Logo">
                    <span class="hidden sm:block text-lg font-semibold text-indigo-700">
                        <?php echo e(config('settings.web_name')); ?>

                    </span>
                </a>

                
                <nav class="hidden md:flex items-center gap-6">
                    <a href="https://mvqlgiaoxu.org/tim-kiem" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition">
                        Kết quả học tập
                    </a>

                    <a href="<?php echo e(route('attendance')); ?>" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition">
                        Điểm danh
                    </a>
                </nav>

                
                <div class="flex items-center gap-3">
                    <button id="fullscreen-button" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                    <button @click="open = !open" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        
        <div x-show="open" x-transition @click.outside="open=false" class="md:hidden bg-white border-t border-slate-200">
            <nav class="px-4 py-4 space-y-2">
                <a href="https://mvqlgiaoxu.org/tim-kiem" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">
                    Kết quả học tập
                </a>
            </nav>
        </div>
    </header>

    
    <main class="flex-1">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <?php echo $__env->yieldContent('content_top'); ?>
            <div class="w-full">
                <section>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                </section>
            </div>
            <?php echo $__env->yieldContent('content_bottom'); ?>
        </div>
    </main>

    
    <?php echo $__env->make('frontend.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <button
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-indigo-600 text-white rounded-full shadow-lg
           hover:bg-indigo-700 transition hover:scale-110"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    
    <script src="<?php echo e(mix('js/manifest.js')); ?>"></script>
    <script src="<?php echo e(mix('js/vendor.js')); ?>"></script>
    <script src="<?php echo e(mix('js/app.js')); ?>"></script>

    <script>
        document.getElementById('fullscreen-button')?.addEventListener('click', () => {
            if (!document.fullscreenElement) document.documentElement.requestFullscreen();
            else document.exitFullscreen();
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?php echo \Livewire\Livewire::scripts(); ?>

</body>

</html><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/main.blade.php ENDPATH**/ ?>