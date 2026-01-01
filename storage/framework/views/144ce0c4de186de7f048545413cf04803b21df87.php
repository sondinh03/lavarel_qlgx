<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    
    <?php if($showNamHoc): ?>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Năm học</label>
        <select wire:model.live="selectedNamHoc"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
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
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
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
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
            <?php if(!$selectedNamHoc): ?> disabled <?php endif; ?>>
            <option value="">-- Tất cả lớp --</option>
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
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
            <?php if(!$selectedNamHoc): ?> disabled <?php endif; ?>>
            <option value="">-- Chọn học kỳ --</option>
            <?php $__currentLoopData = $kys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/class-filter-selector.blade.php ENDPATH**/ ?>