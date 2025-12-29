<div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-5">

        <h2 class="text-xl font-semibold text-slate-800">
            <?php echo e($editingId ? 'Cập nhật năm học' : 'Thêm năm học'); ?>

        </h2>

        
        <div>
            <label class="text-sm font-medium">Tên năm học</label>
            <input type="text" wire:model.defer="name"
                class="w-full mt-1 border rounded-xl p-2.5 focus:ring focus:ring-blue-200">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm">Bắt đầu HK I</label>
                <input type="date" wire:model.defer="start_date_one"
                    class="w-full mt-1 border rounded-xl p-2.5">
            </div>
            <div>
                <label class="text-sm">Kết thúc HK I</label>
                <input type="date" wire:model.defer="end_date_one"
                    class="w-full mt-1 border rounded-xl p-2.5">
            </div>
        </div>

        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm">Bắt đầu HK II</label>
                <input type="date" wire:model.defer="start_date_two"
                    class="w-full mt-1 border rounded-xl p-2.5">
            </div>
            <div>
                <label class="text-sm">Kết thúc HK II</label>
                <input type="date" wire:model.defer="end_date_two"
                    class="w-full mt-1 border rounded-xl p-2.5">
            </div>
        </div>

        
        <div class="flex justify-end gap-3 pt-4">
            <button wire:click="resetForm"
                class="px-5 py-2.5 rounded-xl border hover:bg-slate-50">
                Huỷ
            </button>

            <button wire:click="save"
                class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold
                       hover:bg-blue-700 active:scale-95 transition">
                Lưu
            </button>
        </div>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/nam-hoc/partials/form.blade.php ENDPATH**/ ?>