<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
            [
                'label' => 'Trang chủ',
                'url' => route('home'),
            ],
            [
                'label' => 'Quản lý năm học',
                'url' => route('nam-hoc'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'
            ],
        ],'separator' => 'arrow']]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            [
                'label' => 'Trang chủ',
                'url' => route('home'),
            ],
            [
                'label' => 'Quản lý năm học',
                'url' => route('nam-hoc'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'
            ],
        ]),'separator' => 'arrow']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <div role="status" aria-live="polite">
            <?php if(session()->has('message')): ?>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'success','duration' => 3500]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'success','duration' => 3500]); ?>
                <?php echo e(session('message')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if(session()->has('error')): ?>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'error','duration' => 3500]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'error','duration' => 3500]); ?>
                <?php echo e(session('error')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if(session()->has('warning')): ?>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'warning','duration' => 3500]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'warning','duration' => 3500]); ?>
                <?php echo e(session('warning')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Quản lý năm học','description' => 'Danh sách các năm học của giáo xứ','statValue' => $namHocs?->count(),'statLabel' => 'Năm học','iconType' => 'schoolYear']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Quản lý năm học','description' => 'Danh sách các năm học của giáo xứ','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($namHocs?->count()),'stat-label' => 'Năm học','icon-type' => 'schoolYear']); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

            
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex justify-end">
                    
                    <button
                        wire:click="create"
                        class="inline-flex items-center gap-2
                             px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600
                             text-white text-sm font-semibold hover:from-primary-600 hover:to-primary-700 active:scale-95 
                             disabled:bg-slate-300 disabled:cursor-not-allowed transition-all shadow-sm"
                        aria-label="Thêm năm học mới">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm năm học
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => []]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>STT <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => []]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>Tên năm học <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>Học kỳ I <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>Học kỳ II <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>HK hiện tại <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>Trạng thái <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>Thao tác <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?php echo e($i + 1); ?>

                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    <?php echo e($nh->name); ?>

                                </div>
                            </td>

                            <td class="px-6 py-4 text-center text-sm text-slate-600">
                                <?php if($nh->start_date_one && $nh->end_date_one): ?>
                                <div class="inline-flex items-center gap-1">
                                    <span><?php echo e($nh->start_date_one->format('d/m/Y')); ?></span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span><?php echo e($nh->end_date_one->format('d/m/Y')); ?></span>
                                </div>
                                <?php else: ?>
                                <span class="text-slate-400">Chưa thiết lập</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center text-sm text-slate-600">
                                <?php if($nh->start_date_two && $nh->end_date_two): ?>
                                <div class="inline-flex items-center gap-1">
                                    <span><?php echo e($nh->start_date_two->format('d/m/Y')); ?></span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span><?php echo e($nh->end_date_two->format('d/m/Y')); ?></span>
                                </div>
                                <?php else: ?>
                                <span class="text-slate-400">Chưa thiết lập</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php if($nh->current_semester): ?>
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold 
                                                   rounded-full bg-emerald-100 text-emerald-700">
                                    HK <?php echo e($nh->current_semester); ?>

                                </span>
                                <?php else: ?>
                                <span class="text-slate-400 text-sm">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php if($nh->status): ?>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full bg-primary-100 text-primary-700">
                                    Hoạt động
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full bg-slate-200 text-slate-600">
                                    Lưu trữ
                                </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    
                                    <button
                                        wire:click="edit(<?php echo e($nh->id); ?>)"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                   text-primary-600 hover:text-primary-700 transition-colors"
                                        aria-label="Sửa năm học <?php echo e($nh->name); ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Sửa
                                    </button>

                                    <span class="text-slate-300">|</span>

                                    
                                    <button
                                        wire:click.debounce.500ms="toggleStatus(<?php echo e($nh->id); ?>)"
                                        wire:loading:attr="disabled"
                                        wire:target="toggleStatus(<?php echo e($nh->id); ?>)"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                            <?php echo e($nh->status ? 'text-orange-600 hover:text-orange-700' : 'text-primary-600 hover:text-primary-700'); ?>

                                            transition-colors
                                            disabled:opacity-50 disabled:cursor-not-allowed">

                                        
                                        <svg wire:loading wire:target="toggleStatus(<?php echo e($nh->id); ?>)"
                                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>

                                        
                                        <svg wire:loading.remove wire:target="toggleStatus(<?php echo e($nh->id); ?>)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php if($nh->status): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            <?php endif; ?>
                                        </svg>

                                        

                                        <span><?php echo e($nh->status ? 'Lưu trữ' : 'Kích hoạt'); ?></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.empty-state','data' => ['icon' => 'calendar','title' => 'Chưa có năm học','description' => 'Hãy tạo năm học đầu tiên cho giáo xứ']]); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['icon' => 'calendar','title' => 'Chưa có năm học','description' => 'Hãy tạo năm học đầu tiên cho giáo xứ']); ?>
                                    <button
                                        wire:click="create"
                                        class="inline-flex items-center gap-2 bg-primary-600 text-white 
                                                   px-4 py-2 rounded-xl text-sm font-semibold 
                                                   hover:bg-primary-700 transition-all mt-4">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Thêm năm học đầu tiên
                                    </button>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php if($showForm): ?>
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="namhoc-modal-title"
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="namhoc-modal-title" class="text-xl font-bold text-slate-900">
                        <?php echo e($editingId ? 'Cập nhật năm học' : 'Thêm năm học mới'); ?>

                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Thiết lập thông tin năm học và thời gian các học kỳ
                    </p>
                </div>

                
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    
                    <?php if($errors->any()): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 animate-shake">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-red-800 mb-2">
                                    Vui lòng kiểm tra lại thông tin
                                </h4>
                                <ul class="space-y-1 text-sm text-red-700">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2">
                                        <span class="text-red-400 font-bold">•</span>
                                        <span><?php echo e($error); ?></span>
                                    </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Tên năm học <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: 2025 – 2026"
                            class="w-full px-3 py-2 rounded-xl border
                            <?php echo e($errors->has('name') ? 'border-red-500' : 'border-slate-300'); ?>

                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ I
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-slate-600">Bắt đầu</label>
                                <input
                                    type="date"
                                    wire:model.defer="start_date_one"
                                    min="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>"
                                    max="<?php echo e(now()->addYears(10)->format('Y-m-d')); ?>"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border
                                    <?php echo e($errors->has('start_date_one') ? 'border-red-500' : 'border-slate-300'); ?>

                                   focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Kết thúc</label>
                                <input
                                    type="date"
                                    wire:model.defer="end_date_one"
                                    min="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>"
                                    max="<?php echo e(now()->addYears(10)->format('Y-m-d')); ?>"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border
                                    <?php echo e($errors->has('end_date_one') ? 'border-red-500' : 'border-slate-300'); ?>

                                   focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            Học kỳ II
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-slate-600">Bắt đầu</label>
                                <input
                                    type="date"
                                    wire:model.defer="start_date_two"
                                    min="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>"
                                    max="<?php echo e(now()->addYears(10)->format('Y-m-d')); ?>"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border
                                    <?php echo e($errors->has('start_date_two') ? 'border-red-500' : 'border-slate-300'); ?>

                                   focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Kết thúc</label>
                                <input
                                    type="date"
                                    wire:model.defer="end_date_two"
                                    min="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>"
                                    max="<?php echo e(now()->addYears(10)->format('Y-m-d')); ?>"
                                    class="w-full mt-1 px-3 py-2 rounded-xl border
                                    <?php echo e($errors->has('end_date_two') ? 'border-red-500' : 'border-slate-300'); ?>

                                   focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-300
                       text-slate-700 font-semibold hover:bg-slate-100
                       active:scale-95 transition-all">
                        Huỷ
                    </button>

                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white
                       font-semibold hover:bg-primary-700
                       active:scale-95 transition-all
                       disabled:opacity-60 disabled:cursor-not-allowed">

                        
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Đang lưu...
                        </span>

                        
                        <span wire:loading.remove wire:target="save">
                            Lưu năm học
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/nam-hoc/nam-hoc-manager.blade.php ENDPATH**/ ?>