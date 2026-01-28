<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
                [
                    'label' => 'Trang chủ',
                    'url' => route('dashboard'),
                ],
                [
                    'label' => 'Quản lý khối học',
                    'url' => route('grades.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M3 7h18M3 12h18M3 17h18\' />
                            </svg>',
                ],
            ],'separator' => 'arrow']]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                [
                    'label' => 'Trang chủ',
                    'url' => route('dashboard'),
                ],
                [
                    'label' => 'Quản lý khối học',
                    'url' => route('grades.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M3 7h18M3 12h18M3 17h18\' />
                            </svg>',
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

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Quản lý khối học','description' => 'Danh sách các khối theo năm học','statValue' => $blocks?->count(),'statLabel' => 'Khối học','iconType' => 'block']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Quản lý khối học','description' => 'Danh sách các khối theo năm học','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($blocks?->count()),'stat-label' => 'Khối học','icon-type' => 'block']); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

            
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">

                    
                    <div class="flex items-center gap-3">

                        
                        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => false,'showLop' => false,'showKy' => false,'selectedNamHoc' => $selectedNamHoc])->html();
} elseif ($_instance->childHasBeenRendered('l3699612363-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l3699612363-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l3699612363-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l3699612363-0');
} else {
    $response = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => false,'showLop' => false,'showKy' => false,'selectedNamHoc' => $selectedNamHoc]);
    $html = $response->html();
    $_instance->logRenderedChild('l3699612363-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

                        
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm kiếm khối"
                            class="w-56 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'create','icon' => 'plus','disabled' => !$selectedNamHoc]]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'create','icon' => 'plus','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$selectedNamHoc)]); ?>
                        Thêm khối
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                    
                </div>
            </div>
        </div>

        
        <?php if($selectedNamHoc): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if($blocks && $blocks->count() > 0): ?>
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
<?php $component->withAttributes([]); ?>Tên khối <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['class' => 'text-center']); ?>Thứ tự <?php echo $__env->renderComponent(); ?>
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
                        <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?php echo e($i + 1); ?>

                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-900">
                                <?php echo e($block->name); ?>

                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">
                                <?php echo e($block->weight); ?>

                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                    <?php echo e($block->status ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600'); ?>">
                                    <?php echo e($block->status ? 'Hoạt động' : 'Tắt'); ?>

                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex gap-3">
                                    <button wire:click="edit(<?php echo e($block->id); ?>)"
                                        class="text-primary-600 hover:text-primary-800">
                                        Sửa
                                    </button>

                                    <?php if($isAdmin): ?>
                                    <button wire:click="delete(<?php echo e($block->id); ?>)"
                                        onclick="return confirm('Xóa khối học?')"
                                        class="text-red-600 hover:text-red-800">
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

            
            <div class="px-6 py-4 border-t border-gray-200">
                
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <i class="las la-inbox text-6xl text-gray-300"></i>
                <p class="mt-2 text-gray-500">Chưa có khối học nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                    <i class="las la-plus mr-1"></i> Thêm khối học đầu tiên
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <i class="las la-hand-point-up text-6xl text-gray-300"></i>
            <p class="mt-4 text-lg text-gray-500">Vui lòng chọn năm học để xem danh sách khối</p>
        </div>
        <?php endif; ?>

        
        <?php if($showForm): ?>
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="block-modal-title"
            wire:click="$set('showForm', false)">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden"
                wire:click.stop>
                
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="block-modal-title" class="text-xl font-bold text-slate-900">
                        <?php echo e($editingId ? 'Cập nhật khối học' : 'Thêm khối học mới'); ?>

                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Khối học thuộc năm học đã chọn
                    </p>
                </div>

                
                <div class="p-6 space-y-5">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Tên khối <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            placeholder="Ví dụ: Khối 1, Khối 2..."
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

                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Thứ tự sắp xếp
                        </label>
                        <input
                            type="number"
                            min="0"
                            wire:model.defer="weight"
                            placeholder="0"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="mt-1 text-xs text-slate-500">
                            Số càng nhỏ sẽ hiển thị trước
                        </p>
                        <?php $__errorArgs = ['weight'];
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

                    
                    <div class="flex items-center gap-3 pt-1">
                        <input
                            id="block-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300
                           text-primary-600 focus:ring-primary-500">
                        <label for="block-status" class="text-sm text-slate-700">
                            Hoạt động
                        </label>
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
                        Lưu khối
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/block/block-manager.blade.php ENDPATH**/ ?>