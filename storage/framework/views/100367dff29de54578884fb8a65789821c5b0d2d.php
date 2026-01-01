<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-4xl space-y-5">

        
        <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Trang chủ', 'url' => route('ds-lop')],
            ['label' => 'Quản lý lớp học', 'url' => route('ds-lop')],
            ['label' => $isEdit ? 'Chỉnh sửa lớp' : 'Tạo lớp mới']
        ]]]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Trang chủ', 'url' => route('ds-lop')],
            ['label' => 'Quản lý lớp học', 'url' => route('ds-lop')],
            ['label' => $isEdit ? 'Chỉnh sửa lớp' : 'Tạo lớp mới']
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <?php $__currentLoopData = ['success', 'message']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(session()->has($key)): ?>
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'success','duration' => 3000]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'success','duration' => 3000]); ?>
            <?php echo e(session($key)); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => $isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới','description' => $isEdit ? 'Cập nhật thông tin lớp học' : 'Thêm lớp học mới vào hệ thống','icon' => 'class','gradient' => 'primary']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới'),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Cập nhật thông tin lớp học' : 'Thêm lớp học mới vào hệ thống'),'icon' => 'class','gradient' => 'primary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <form wire:submit.prevent="save" id="main-content">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                
                <div class="p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Thông tin cơ bản
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Mã lớp <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                wire:model.defer="form.symbol"
                                placeholder="VD: GL-01"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-primary-500
                                          transition-all
                                          <?php $__errorArgs = ['form.symbol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['form.symbol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Tên lớp <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                wire:model.defer="form.name"
                                placeholder="VD: Giáo lý 1"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-primary-500
                                          transition-all
                                          <?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Năm học <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.schoolyear"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all
                                           <?php $__errorArgs = ['form.schoolyear'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- Chọn năm học --</option>
                                <?php $__currentLoopData = $schoolyears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['form.schoolyear'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Khối <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.block"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all
                                           <?php $__errorArgs = ['form.block'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- Chọn khối --</option>
                                <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['form.block'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                    </div>
                </div>

                
                <div class="p-6 border-t border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857" />
                        </svg>
                        Phân công giáo lý viên
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Giáo lý viên chủ nhiệm
                            </label>
                            <select wire:model.defer="form.mainTeacherId"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all">
                                <option value="">-- Chọn giáo lý viên --</option>
                                <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Giáo lý viên phụ trách
                            </label>
                            <select multiple size="5"
                                wire:model.defer="form.assistantTeacherIds"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all">
                                <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">
                                Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều
                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="p-6 border-t border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4" />
                        </svg>
                        Ghi chú
                    </h3>

                    <textarea rows="4"
                        wire:model.defer="form.note"
                        placeholder="Nhập ghi chú (không bắt buộc)..."
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                     focus:outline-none focus:ring-2 focus:ring-primary-500
                                     transition-all resize-none"></textarea>
                </div>

                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <a href="<?php echo e(route('ds-lop')); ?>"
                            class="px-6 py-2.5 bg-white border border-slate-300 rounded-xl
                                  text-slate-700 font-semibold hover:bg-slate-100
                                  active:scale-95 transition-all text-center">
                            Huỷ
                        </a>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl
                                       bg-primary-600 text-white font-semibold
                                       hover:bg-primary-700
                                       active:scale-[0.98] transition-all
                                       disabled:opacity-60">
                            <?php echo e($isEdit ? 'Cập nhật lớp học' : 'Tạo lớp học'); ?>

                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<?php $__env->stopPush(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/lop/lop-form.blade.php ENDPATH**/ ?>