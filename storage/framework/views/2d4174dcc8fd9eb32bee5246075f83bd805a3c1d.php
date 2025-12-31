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
            
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Quản lý năm học</h1>
                            <p class="text-sm text-slate-600 mt-1">
                                Danh sách các năm học đã được tạo trong hệ thống của giáo xứ.
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-3xl font-bold text-primary-600"><?php echo e($namHocs->count()); ?></div>
                        <div class="text-xs text-slate-600 font-medium">Năm học</div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex justify-end">
                    
                    <button
                        wire:click="create"
                        class="inline-flex items-center gap-2 bg-primary-600 text-white px-4 py-2 
                                   rounded-xl text-sm font-semibold hover:bg-primary-700 active:scale-95 
                                   transition-all shadow-sm"
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
                        <?php $__empty_1 = true; $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition-colors">
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
                                    <span class="w-1.5 h-1.5 bg-primary-500 rounded-full mr-1.5"></span>
                                    Hoạt động
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold 
                                                   rounded-full bg-slate-200 text-slate-600">
                                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full mr-1.5"></span>
                                    Lưu trữ
                                </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    
                                    <button
                                        wire:click="edit(<?php echo e($nh->id); ?>)"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                   text-primary-600 hover:text-primary-800 transition-colors"
                                        aria-label="Sửa năm học <?php echo e($nh->name); ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Sửa
                                    </button>

                                    <span class="text-slate-300">|</span>

                                    
                                    <button
                                        wire:click="toggleStatus(<?php echo e($nh->id); ?>)"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                   text-orange-600 hover:text-orange-800 transition-colors"
                                        aria-label="<?php echo e($nh->status ? 'Lưu trữ' : 'Kích hoạt'); ?> năm học <?php echo e($nh->name); ?>">
                                        <?php if($nh->status): ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                        Lưu trữ
                                        <?php else: ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Kích hoạt
                                        <?php endif; ?>
                                    </button>

                                    
                                    <?php if($isAdmin): ?>
                                    <span class="text-slate-300">|</span>
                                    <button
                                        wire:click="delete(<?php echo e($nh->id); ?>)"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa năm học này?\n\nLưu ý: Chỉ có thể xóa năm học chưa có khối học hoặc lớp học.')"
                                        class="inline-flex items-center gap-1 text-sm font-medium 
                                                       text-red-600 hover:text-red-800 transition-colors"
                                        aria-label="Xóa năm học <?php echo e($nh->name); ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Xóa
                                    </button>
                                    <?php endif; ?>
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
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="namhoc-modal-title"
        wire:click="$set('showForm', false)">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden"
            wire:click.stop>
            
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <h2 id="namhoc-modal-title" class="text-xl font-bold text-slate-900">
                    <?php echo e($editingId ? 'Cập nhật năm học' : 'Thêm năm học mới'); ?>

                </h2>
                <p class="text-sm text-slate-600 mt-1">
                    Thiết lập thông tin năm học và thời gian các học kỳ
                </p>
            </div>

            
            <div class="p-6 space-y-5">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Tên năm học <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model.defer="name"
                        placeholder="Ví dụ: 2024 – 2025"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="text-sm text-slate-600">Kết thúc</label>
                            <input
                                type="date"
                                wire:model.defer="end_date_one"
                                class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
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
                                class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="text-sm text-slate-600">Kết thúc</label>
                            <input
                                type="date"
                                wire:model.defer="end_date_two"
                                class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-300
                                   focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <button
                    wire:click="$set('showForm', false)"
                    class="px-4 py-2 rounded-xl bg-white border border-slate-300
                       text-slate-700 font-semibold hover:bg-slate-100
                       active:scale-95 transition-all">
                    Huỷ
                </button>

                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-xl bg-primary-600 text-white
                       font-semibold hover:bg-primary-700
                       active:scale-95 transition-all
                       disabled:opacity-60">
                    Lưu năm học
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/nam-hoc/nam-hoc-manager.blade.php ENDPATH**/ ?>