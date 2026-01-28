<div class="max-w-6xl mx-auto space-y-6">

    
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-700">
            Import danh sách Giáo lý viên
        </h1>

        <a href="<?php echo e(route('catechists.index')); ?>"
            class="text-sm text-slate-500 hover:text-slate-700">
            ← Quay lại danh sách
        </a>
    </div>

    
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <label class="block text-sm font-semibold text-slate-700">
            Chọn file Excel
        </label>

        
        <form wire:submit.prevent="preview"
            enctype="multipart/form-data"
            class="space-y-4">

            <input type="file"
                wire:model="file"
                accept=".xlsx,.csv">

            <button type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded">
                Xem trước
            </button>
        </form>


        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="text-sm text-red-500"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <p class="text-sm text-slate-500">
            File Excel phải có các cột:
            <code class="bg-slate-100 px-1 rounded">
                ten_thanh, ho_ten, ngay_sinh, so_dien_thoai, tao_tai_khoan
            </code>
        </p>
    </div>

    
    <?php if(!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <h3 class="font-semibold text-red-700 mb-2">
            Phát hiện lỗi
        </h3>

        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($rows)): ?>
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full border border-slate-200">
            <thead class="bg-slate-100">
                <tr class="text-left text-sm text-slate-600">
                    <th class="px-3 py-2 border">#</th>
                    <th class="px-3 py-2 border">Tên thánh</th>
                    <th class="px-3 py-2 border">Họ tên</th>
                    <th class="px-3 py-2 border">Ngày sinh</th>
                    <th class="px-3 py-2 border">Số điện thoại</th>
                    <th class="px-3 py-2 border">Giáo họ</th>
                    <th class="px-3 py-2 border">Tạo TK</th>
                    <th class="px-3 py-2 border">Trạng thái</th>
                </tr>
            </thead>

            <tbody>
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="text-sm <?php echo e($row['duplicate'] ? 'bg-red-50' : ''); ?>">
                    <td class="px-3 py-2 border">
                        <?php echo e($index + 1); ?>

                    </td>

                    <td class="px-3 py-2 border">
                        <?php echo e($row['ten_thanh']); ?>

                    </td>

                    <td class="px-3 py-2 border font-medium">
                        <?php echo e($row['ho_ten']); ?>

                    </td>

                    <td class="px-3 py-2 border">
                        <?php echo e($row['ngay_sinh']); ?>

                    </td>

                    <td class="px-3 py-2 border">
                        <?php echo e($row['so_dien_thoai']); ?>

                    </td>

                    <td class="px-3 py-2 border">
                        <?php echo e($row['giao_ho']); ?>

                    </td>

                    <td class="px-3 py-2 border text-center">
                        <?php echo e($row['tao_tai_khoan']); ?>

                    </td>

                    <td class="px-3 py-2 border text-center">
                        <?php if($row['duplicate']): ?>
                        <span class="text-red-600 font-semibold">
                            Trùng SĐT
                        </span>
                        <?php else: ?>
                        <span class="text-green-600 font-semibold">
                            OK
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($rows)): ?>
    <div class="flex justify-end gap-3">
        <a href="<?php echo e(route('catechists.index')); ?>"
            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600">
            Hủy
        </a>

        <button
            wire:click="confirmImport"
            @disabled(!$readyToImport)
            class="px-5 py-2 rounded-lg text-white
                       <?php echo e($readyToImport
                            ? 'bg-primary-600 hover:bg-primary-700'
                            : 'bg-slate-400 cursor-not-allowed'); ?>">
            Xác nhận import
        </button>
    </div>
    <?php endif; ?>

</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/teacher/teacher-import-preview.blade.php ENDPATH**/ ?>