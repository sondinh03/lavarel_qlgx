<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Quản lý giáo họ','description' => 'Danh sách các giáo họ','statValue' => $parishes->count(),'statLabel' => 'Giáo họ','iconType' => 'parish']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Quản lý giáo họ','description' => 'Danh sách các giáo họ','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($parishes->count()),'stat-label' => 'Giáo họ','icon-type' => 'parish']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            <?php if($parishes->count()): ?>
            <table class="w-full border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b">
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
<?php $component->withAttributes([]); ?>Tên giáo họ <?php echo $__env->renderComponent(); ?>
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

                <tbody class="divide-y">
                    <?php $__currentLoopData = $parishes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $parish): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4"><?php echo e($i + 1); ?></td>

                        <td class="px-6 py-4 font-semibold">
                            <?php echo e($parish->name); ?>

                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                <?php echo e($parish->status
                                    ? 'bg-primary-100 text-primary-700'
                                    : 'bg-slate-200 text-slate-600'); ?>">
                                <?php echo e($parish->status ? 'Hoạt động' : 'Tắt'); ?>

                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button wire:click="edit(<?php echo e($parish->id); ?>)"
                                class="text-primary-600 hover:text-primary-800">
                                Sửa
                            </button>

                            <?php if($isAdmin): ?>
                            <button wire:click="delete(<?php echo e($parish->id); ?>)"
                                onclick="return confirm('Xóa giáo họ?')"
                                class="ml-3 text-red-600 hover:text-red-800">
                                Xóa
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="p-12 text-center text-slate-500">
                Chưa có giáo họ nào
            </div>
            <?php endif; ?>
        </div>

        
        <div class="fixed bottom-6 right-6">
            <button
                wire:click="create"
                class="inline-flex items-center gap-2
                    px-5 py-3 rounded-full
                    bg-primary-600 hover:bg-primary-700
                    text-white font-semibold shadow-lg
                    active:scale-95 transition-all">
                + Thêm giáo họ
            </button>
        </div>

        
        <?php if($showForm): ?>
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            wire:click="$set('showForm', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md"
                wire:click.stop>

                <div class="p-6 border-b bg-primary-50">
                    <h2 class="text-lg font-bold">
                        <?php echo e($editingId ? 'Cập nhật giáo họ' : 'Thêm giáo họ mới'); ?>

                    </h2>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-semibold">Tên giáo họ</label>
                        <input
                            wire:model.defer="name"
                            class="w-full mt-1 px-3 py-2 rounded-xl border focus:ring-2 focus:ring-primary-500">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-sm text-red-500 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="status">
                        <span>Hoạt động</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t flex justify-end gap-3 bg-slate-50">
                    <button wire:click="resetForm" class="px-4 py-2 rounded-xl border">
                        Huỷ
                    </button>
                    <button wire:click="save"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white">
                        Lưu
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/parish/parish-child.blade.php ENDPATH**/ ?>