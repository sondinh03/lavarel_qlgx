<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    
    <?php if($showNamHoc): ?>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Năm học</label>
        <select wire:model.live="selectedNamHoc"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Chọn năm học --</option>
            <?php $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    
    <?php if($showKhoi): ?>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Khối</label>
        <select wire:model.live="selectedKhoi"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            <?php if(!$selectedNamHoc): ?> disabled <?php endif; ?>>
            <option value="">-- Tất cả khối --</option>
            <?php $__currentLoopData = $khois; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    
    <?php if($showLop): ?>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Lớp</label>
        <select wire:model.live="selectedLop"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            <?php if(!$selectedNamHoc): ?> disabled <?php endif; ?>>
            <option value="">-- Chọn lớp --</option>
            <?php $__currentLoopData = $lops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    
    <?php if($showKy): ?>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Kỳ</label>
        <select wire:model.live="selectedKy"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            <?php if(!$selectedNamHoc): ?> disabled <?php endif; ?>>
            <option value="">-- Chọn kỳ --</option>
            <?php $__currentLoopData = $kys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    
    <div class="flex items-end">
        <button wire:click="resetFiltersHandler"
            type="button"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-semibold text-slate-900">Đặt lại</span>
        </button>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/class-filter-selector.blade.php ENDPATH**/ ?>