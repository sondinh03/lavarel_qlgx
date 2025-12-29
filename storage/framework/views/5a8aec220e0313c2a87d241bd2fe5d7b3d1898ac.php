<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Quản lý khối']
        ]]]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Quản lý khối']
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <div role="status" aria-live="polite" aria-atomic="true">
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
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-3">
                <label for="selectedNamHoc" class="font-medium">Năm học:</label>
                <select wire:model="selectedNamHoc" id="selectedNamHoc" class="rounded-md border-gray-300">
                    <?php $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($nh->id); ?>"><?php echo e($nh->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <?php if($parish_id): ?>
            <button wire:click="create"
                class="bg-purple-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-purple-700">
                Thêm khối
            </button>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.loading.overlay','data' => ['wireTarget' => 'selectedNamHoc,perPage,resetForm,save,toggleStatus','mode' => 'centered']]); ?>
<?php $component->withName('loading.overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire-target' => 'selectedNamHoc,perPage,resetForm,save,toggleStatus','mode' => 'centered']); ?>
                Đang tải danh sách khối...
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => []]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>Trạng thái <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Thao tác <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo e($blocks->firstItem() + $index); ?></td>
                            <td class="px-4 py-2"><?php echo e($block->name); ?></td>
                            <td class="px-4 py-2">
                                <button wire:click="toggleStatus(<?php echo e($block->id); ?>)"
                                    class="px-3 py-1 rounded-lg <?php echo e($block->status ? 'bg-green-500 text-white' : 'bg-red-500 text-white'); ?>">
                                    <?php echo e($block->status ? 'Hoạt động' : 'Đóng'); ?>

                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <button wire:click="edit(<?php echo e($block->id); ?>)" class="text-blue-600 hover:underline">Sửa</button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Chưa có khối nào</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($blocks && $blocks->hasPages()): ?>
            <div class="p-4">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.pagination','data' => ['paginator' => $blocks,'perPageOptions' => $perPageOptions]]); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($blocks),'per-page-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($perPageOptions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/block/block-manager.blade.php ENDPATH**/ ?>