<div>
    
    

    
    <?php if($showModal): ?>
    <div
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        wire:click="closeModal">
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
            wire:click.stop>

            
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-slate-900">
                            Thêm buổi điểm danh hàng loạt
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">
                            Tạo buổi điểm danh cho nhiều lớp cùng lúc
                        </p>
                    </div>
                    <button
                        wire:click="closeModal"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                
                <?php if($errors->any()): ?>
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">Vui lòng kiểm tra lại thông tin</h4>
                            <ul class="space-y-1 text-sm text-red-700">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>• <?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                
                <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-sm font-bold">1</span>
                        Thông tin cơ bản
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Năm học <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model="namhocId"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Chọn năm học --</option>
                                <?php $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($nh->id); ?>"><?php echo e($nh->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['namhocId'];
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
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Loại điểm danh <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-3">
                                <label class="flex-1 flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
                                              <?php echo e($type == 1 ? 'border-primary-500 bg-primary-50' : 'border-slate-300 hover:border-primary-300'); ?>">
                                    <input
                                        type="radio"
                                        wire:model="type"
                                        value="1"
                                        class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm font-medium">Đi học</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
                                              <?php echo e($type == 2 ? 'border-primary-500 bg-primary-50' : 'border-slate-300 hover:border-primary-300'); ?>">
                                    <input
                                        type="radio"
                                        wire:model="type"
                                        value="2"
                                        class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm font-medium">Đi lễ</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm font-bold">2</span>
                        Chọn phạm vi
                    </h3>

                    
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all
                                      <?php echo e($scope == 'school' ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-blue-300'); ?>">
                            <input
                                type="radio"
                                wire:model="scope"
                                value="school"
                                class="text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-slate-900">Toàn trường</div>
                                <div class="text-xs text-slate-500">Tất cả lớp</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all
                                      <?php echo e($scope == 'block' ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-blue-300'); ?>">
                            <input
                                type="radio"
                                wire:model="scope"
                                value="block"
                                class="text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-slate-900">Toàn khối</div>
                                <div class="text-xs text-slate-500">Chọn 1 khối</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all
                                      <?php echo e($scope == 'class' ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-blue-300'); ?>">
                            <input
                                type="radio"
                                wire:model="scope"
                                value="class"
                                class="text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-slate-900">Từng lớp</div>
                                <div class="text-xs text-slate-500">Chọn nhiều lớp</div>
                            </div>
                        </label>
                    </div>

                    
                    <?php if(in_array($scope, ['block', 'class'])): ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Chọn khối <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="blockId"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn khối --</option>
                            <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($block->id); ?>"><?php echo e($block->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($scope == 'class'): ?>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-slate-700">
                                Chọn lớp <span class="text-red-500">*</span>
                                <span class="text-slate-500 font-normal">(<?php echo e($this->selectedClassCount); ?>/<?php echo e($classes->count()); ?>)</span>
                            </label>
                            <div class="flex gap-2">
                                <button
                                    wire:click="selectAllClasses"
                                    type="button"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                    Chọn tất cả
                                </button>
                                <span class="text-slate-300">|</span>
                                <button
                                    wire:click="deselectAllClasses"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-700 font-medium">
                                    Bỏ chọn
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-xl">
                            <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all
                                          <?php echo e(in_array($class->id, $classIds) ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-blue-300'); ?>">
                                <input
                                    type="checkbox"
                                    wire:click="toggleClass(<?php echo e($class->id); ?>)"
                                    <?php echo e(in_array($class->id, $classIds) ? 'checked' : ''); ?>

                                    class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm"><?php echo e($class->name); ?></span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-full text-center text-slate-500 text-sm py-4">
                                Chưa có lớp nào
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                
                <div class="border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-sm font-bold">3</span>
                        Chọn ngày điểm danh
                    </h3>

                    
                    <div class="flex gap-2 p-1 bg-slate-100 rounded-xl">
                        <button
                            wire:click="$set('dateMode', 'semester')"
                            class="flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all
                                   <?php echo e($dateMode == 'semester' ? 'bg-white shadow-sm text-green-600' : 'text-slate-600 hover:text-slate-900'); ?>">
                            Theo học kỳ
                        </button>
                        <button
                            wire:click="$set('dateMode', 'range')"
                            class="flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all
                                   <?php echo e($dateMode == 'range' ? 'bg-white shadow-sm text-green-600' : 'text-slate-600 hover:text-slate-900'); ?>">
                            Khoảng thời gian
                        </button>
                        <button
                            wire:click="$set('dateMode', 'specific')"
                            class="flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all
                                   <?php echo e($dateMode == 'specific' ? 'bg-white shadow-sm text-green-600' : 'text-slate-600 hover:text-slate-900'); ?>">
                            Chọn ngày cụ thể
                        </button>
                    </div>

                    
                    <?php if($dateMode == 'semester'): ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Học kỳ</label>
                            <div class="flex gap-3">
                                <label class="flex-1 flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
                                              <?php echo e($semester == 1 ? 'border-green-500 bg-green-50' : 'border-slate-300'); ?>">
                                    <input type="radio" wire:model="semester" value="1" class="text-green-600">
                                    <span class="text-sm font-medium">Học kỳ I</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
                                              <?php echo e($semester == 2 ? 'border-green-500 bg-green-50' : 'border-slate-300'); ?>">
                                    <input type="radio" wire:model="semester" value="2" class="text-green-600">
                                    <span class="text-sm font-medium">Học kỳ II</span>
                                </label>
                            </div>
                        </div>

                        <?php if($startDate && $endDate): ?>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <div class="text-sm text-green-800">
                                <span class="font-semibold">Thời gian:</span>
                                <?php echo e(Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> - <?php echo e(Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($dateMode == 'range'): ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Từ ngày</label>
                            <input
                                type="date"
                                wire:model="startDate"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Đến ngày</label>
                            <input
                                type="date"
                                wire:model="endDate"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($dateMode == 'specific'): ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Chọn các ngày</label>
                        <input
                            type="date"
                            wire:model="selectedDates"
                            multiple
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="mt-1 text-xs text-slate-500">Giữ Ctrl/Cmd để chọn nhiều ngày</p>
                    </div>
                    <?php endif; ?>

                    
                    <?php if(in_array($dateMode, ['semester', 'range'])): ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Các ngày trong tuần (để trống = tất cả các ngày)
                        </label>
                        <div class="grid grid-cols-4 md:grid-cols-7 gap-2">
                            <?php $__currentLoopData = $this->weekdayLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                wire:click="toggleWeekday(<?php echo e($day); ?>)"
                                type="button"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-all
                                       <?php echo e(in_array($day, $weekdays) ? 'bg-green-500 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'); ?>">
                                <?php echo e($label); ?>

                            </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                
                <?php if($showPreview && $preview && $preview['success']): ?>
                <div class="border-2 border-amber-300 bg-amber-50 rounded-xl p-5">
                    <h4 class="text-lg font-bold text-amber-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Xem trước
                    </h4>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="bg-white rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-blue-600"><?php echo e($preview['classes_count']); ?></div>
                            <div class="text-xs text-slate-600 mt-1">Lớp</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-green-600"><?php echo e($preview['dates_count']); ?></div>
                            <div class="text-xs text-slate-600 mt-1">Ngày</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-purple-600"><?php echo e($preview['total_sessions']); ?></div>
                            <div class="text-xs text-slate-600 mt-1">Tổng buổi</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-amber-600"><?php echo e($preview['will_create']); ?></div>
                            <div class="text-xs text-slate-600 mt-1">Sẽ tạo mới</div>
                        </div>
                    </div>

                    <?php if($preview['existing'] > 0): ?>
                    <div class="bg-white rounded-lg p-3 text-sm text-amber-700">
                        ⚠️ Có <?php echo e($preview['existing']); ?> buổi đã tồn tại sẽ được bỏ qua
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-between">
                <button
                    wire:click="closeModal"
                    class="px-4 py-2 bg-white border border-slate-300 rounded-xl
                           text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Hủy
                </button>

                <div class="flex gap-3">
                    <button
                        wire:click="previewSessions"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-amber-500 text-white rounded-xl
                               text-sm font-medium hover:bg-amber-600 transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed
                               flex items-center gap-2">
                        <svg wire:loading.remove wire:target="previewSessions" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg wire:loading wire:target="previewSessions" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Xem trước</span>
                    </button>

                    <button
                        wire:click="confirmCreate"
                        wire:loading.attr="disabled"
                        @disabled(!$showPreview || !$preview || $preview['will_create']==0)
                        class="px-4 py-2 bg-primary-600 text-white rounded-xl
                               text-sm font-medium hover:bg-primary-700 transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed
                               flex items-center gap-2">
                        <svg wire:loading.remove wire:target="confirmCreate" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading wire:target="confirmCreate" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Tạo buổi điểm danh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/attendance/create-attendance-sessions.blade.php ENDPATH**/ ?>