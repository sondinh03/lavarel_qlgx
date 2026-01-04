<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
                ['label' => 'Trang chủ', 'url' => route('home')],
                [
                    'label' => 'Quản lý giáo viên',
                    'url' => route('teacher.show'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' />
                            </svg>',
                ],
            ],'separator' => 'arrow']]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Trang chủ', 'url' => route('home')],
                [
                    'label' => 'Quản lý giáo viên',
                    'url' => route('teacher.show'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' />
                            </svg>',
                ],
            ]),'separator' => 'arrow']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Quản lý giáo viên','description' => 'Danh sách giáo viên giáo lý','statValue' => $teachers->total(),'statLabel' => 'Giáo viên','iconType' => 'teacher']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Quản lý giáo viên','description' => 'Danh sách giáo viên giáo lý','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($teachers->total()),'stat-label' => 'Giáo viên','icon-type' => 'teacher']); ?>
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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'error','duration' => 4000]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'error','duration' => 4000]); ?>
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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'warning','duration' => 4000]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'warning','duration' => 4000]); ?>
                    <?php echo e(session('warning')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                <?php endif; ?>

                <?php if(session()->has('info')): ?>
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'info','duration' => 3500]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'info','duration' => 3500]); ?>
                    <?php echo e(session('info')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>

            
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">

                    
                    <div class="flex items-center gap-3">
                        
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm theo tên hoặc SĐT"
                            class="w-64 px-3 py-2 rounded-xl
                                   border border-slate-300
                                   text-sm
                                   focus:ring-2 focus:ring-primary-500
                                   focus:border-transparent" />
                    </div>

                    
                    <button
                        wire:click="create"
                        class="inline-flex items-center gap-2
                               px-5 py-2.5 rounded-xl
                               bg-gradient-to-r from-primary-500 to-primary-600
                               hover:from-primary-600 hover:to-primary-700
                               text-white text-sm font-semibold
                               active:scale-95
                               transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm giáo viên
                    </button>

                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if($teachers->count() > 0): ?>
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
<?php $component->withAttributes([]); ?>Họ tên <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Ngày sinh <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Số điện thoại <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Giáo họ <?php echo $__env->renderComponent(); ?>
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
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?php echo e($teachers->firstItem() + $i); ?>

                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    <?php echo e($teacher->name); ?>

                                </div>
                                <?php if($teacher->year): ?>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Năm <?php echo e($teacher->year); ?>

                                </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php if($teacher->birthday): ?>
                                <?php echo e($teacher->birthday->format('d/m/Y')); ?>

                                <span class="text-xs text-slate-400 ml-1">
                                    (<?php echo e($teacher->birthday->age); ?> tuổi)
                                </span>
                                <?php else: ?>
                                <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php if($teacher->phone_number): ?>
                                <a href="tel:<?php echo e($teacher->phone_number); ?>"
                                    class="hover:text-primary-600 transition-colors">
                                    <?php echo e($teacher->phone_number); ?>

                                </a>
                                <?php else: ?>
                                <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs">
                                <?php if($teacher->parish_child_name ): ?>
                                <div class="truncate" title="<?php echo e($teacher->parish_child_name); ?>">
                                    <?php echo e($teacher->parish_child_name); ?>

                                </div>
                                <?php else: ?>
                                <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="toggleStatus(<?php echo e($teacher->id); ?>)"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-full
                                           transition-all hover:scale-105
                                           <?php echo e($teacher->status ? 'bg-primary-100 text-primary-700 hover:bg-primary-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'); ?>">
                                    <?php echo e($teacher->status ? 'Hoạt động' : 'Tắt'); ?>

                                </button>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button
                                        wire:click="edit(<?php echo e($teacher->id); ?>)"
                                        class="text-primary-600 hover:text-primary-800 font-medium text-sm
                                               transition-colors">
                                        Sửa
                                    </button>

                                    <?php if($isAdmin): ?>
                                    <button
                                        wire:click="delete(<?php echo e($teacher->id); ?>)"
                                        onclick="return confirm('Xác nhận xóa giáo viên <?php echo e($teacher->name); ?>?')"
                                        class="text-red-600 hover:text-red-800 font-medium text-sm
                                               transition-colors">
                                        Xóa
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($teachers->hasPages()): ?>
            <div class="border-t border-slate-200">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.pagination','data' => ['paginator' => $teachers,'perPageOptions' => $this->getPerPageOptions()]]); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($teachers),'per-page-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getPerPageOptions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="text-center py-16">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có giáo viên nào</p>
                <button
                    wire:click="create"
                    class="mt-4 px-5 py-2.5 bg-primary-600 text-white rounded-xl
                           hover:bg-primary-700 font-semibold
                           active:scale-95 transition-all">
                    <i class="las la-plus mr-1"></i> Thêm giáo viên đầu tiên
                </button>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if($showForm): ?>
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="teacher-modal-title"
            wire:click="$set('showForm', false)">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] overflow-y-auto"
                wire:click.stop>
                
                <div class="sticky top-0 z-10 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="teacher-modal-title" class="text-xl font-bold text-slate-900">
                        <?php echo e($editingId ? 'Cập nhật giáo viên' : 'Thêm giáo viên mới'); ?>

                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Nhập thông tin giáo viên giáo lý
                    </p>
                </div>

                
                <div class="p-6 space-y-5">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Họ tên <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: Nguyễn Văn A"
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

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Ngày sinh
                            </label>
                            <input
                                type="date"
                                wire:model.defer="birthday"
                                max="<?php echo e(date('Y-m-d')); ?>"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <?php $__errorArgs = ['birthday'];
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

                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Số điện thoại
                            </label>
                            <input
                                type="tel"
                                wire:model.defer="phoneNumber"
                                placeholder="0123456789"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <?php $__errorArgs = ['phoneNumber'];
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
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Ghi chú
                        </label>
                        <textarea
                            wire:model.defer="note"
                            rows="3"
                            placeholder="Thông tin bổ sung về giáo viên..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500
                                   resize-none"></textarea>
                        <?php $__errorArgs = ['note'];
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

                    
                    <div class="pt-2 border-t border-slate-200">
                        <details class="group">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-600 hover:text-slate-900">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Thông tin bổ sung (tùy chọn)
                                </span>
                            </summary>
                            <div class="mt-4 space-y-4">
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Năm
                                    </label>
                                    <input
                                        type="number"
                                        wire:model.defer="year"
                                        placeholder="2024"
                                        min="1900"
                                        max="2100"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <?php $__errorArgs = ['year'];
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
                            </div>
                        </details>
                    </div>

                    
                    <div class="flex items-center gap-3 pt-1">
                        <input
                            id="teacher-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300
                                   text-primary-600 focus:ring-primary-500">
                        <label for="teacher-status" class="text-sm text-slate-700">
                            Hoạt động
                        </label>
                    </div>
                </div>

                
                <div class="sticky bottom-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <button
                        wire:click="cancel"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-300
                               text-slate-700 font-semibold hover:bg-slate-100
                               active:scale-95 transition-all">
                        Hủy
                    </button>

                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white
                               font-semibold hover:bg-primary-700
                               active:scale-95 transition-all
                               disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">Lưu giáo viên</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div wire:loading.flex class="fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3 shadow-xl">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700 font-medium">Đang xử lý...</span>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/teacher/teacher-manager.blade.php ENDPATH**/ ?>