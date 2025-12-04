<nav class="position-relative p-2 bg-white">
    <div class="row">
    	<div class="col-8 col-md-2 mb-3 mb-md-0">
    		<div class="flex justify-content-center">
                <a class="text-3xl font-bold leading-none text-decoration-none" href="/">
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'smart::components.smart-image','data' => ['src' => ''.e(url(config('settings.logo'))).'','alt' => ''.e(config('settings.web_name')).'','class' => 'img-fluid w-auto h-auto']]); ?>
<?php $component->withName('smart-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['src' => ''.e(url(config('settings.logo'))).'','alt' => ''.e(config('settings.web_name')).'','class' => 'img-fluid w-auto h-auto']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                </a>
            </div>
    	</div>
    	<div class="col-4 col-md-10 d-flex justify-content-end align-items-center">
    		<?php if (isset($component)) { $__componentOriginald0b4154eafa6ddf1d90e70a636ac005452fbb4c9 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\Menu::class, []); ?>
<?php $component->withName('menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0b4154eafa6ddf1d90e70a636ac005452fbb4c9)): ?>
<?php $component = $__componentOriginald0b4154eafa6ddf1d90e70a636ac005452fbb4c9; ?>
<?php unset($__componentOriginald0b4154eafa6ddf1d90e70a636ac005452fbb4c9); ?>
<?php endif; ?>
    		<div class="clearfix d-flex d-sm-flex d-md-none align-items-center">
        		<button type="button" class="d-block d-sm-block d-md-none burger-menu btn btn-secondary p-0 border-0 fs-3 py-1 px-2">
                	<i class="bi bi-list"></i>
                </button>
            </div>
    	</div>
    </div>
</nav>
<div class="canvas-menu d-flex align-items-start flex-column">
    <nav class="vertical">
    	<button type="button" class="text-white bg-transparent btn-close fs-6" aria-label="Close"></button>
		<?php if (isset($component)) { $__componentOriginald1b94f7a7af6f716c83028e2fee8a03c82926d95 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\MenuMobile::class, []); ?>
<?php $component->withName('menu-mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1b94f7a7af6f716c83028e2fee8a03c82926d95)): ?>
<?php $component = $__componentOriginald1b94f7a7af6f716c83028e2fee8a03c82926d95; ?>
<?php unset($__componentOriginald1b94f7a7af6f716c83028e2fee8a03c82926d95); ?>
<?php endif; ?>
	</nav>
</div>


<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/components/nav.blade.php ENDPATH**/ ?>