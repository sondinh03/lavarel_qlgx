<div>
    
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
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    Giáo lý viên phụ trách
                </h3>
                <p class="text-sm text-slate-600 mt-1">
                    Lớp: <span class="font-semibold"><?php echo e($lop->name); ?></span> -
                    <?php echo e($lop->schoolYear->name ?? 'N/A'); ?>

                </p>
            </div>

            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'openModal','icon' => 'plus','size' => 'sm']]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'openModal','icon' => 'plus','size' => 'sm']); ?>
                Thêm GLV
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
        </div>

        <?php if($currentTeachers && $currentTeachers->isNotEmpty()): ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $currentTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                <div class="flex items-center gap-3 flex-1">
                    
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 
                                    flex items-center justify-center font-semibold flex-shrink-0">
                        <?php echo e(strtoupper(substr($ct['teacher_name'], 0, 2))); ?>

                    </div>

                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-900">
                                <?php echo e($ct['teacher_name']); ?>

                            </p>

                            
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             <?php echo e($ct['role'] === 1 
                                                ? 'bg-blue-100 text-blue-700' 
                                                : 'bg-purple-100 text-purple-700'); ?>">
                                <?php echo e($ct['role_label']); ?>

                            </span>
                        </div>

                        <?php if($ct['phone']): ?>
                        <p class="text-sm text-slate-500 mt-0.5">
                            <svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <?php echo e($ct['phone']); ?>

                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="flex items-center gap-2 flex-shrink-0">
                    
                    <button
                        wire:click="changeRole(<?php echo e($ct['id']); ?>, <?php echo e($ct['role'] === 1 ? 2 : 1); ?>)"
                        class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 
                                   rounded-lg transition"
                        title="<?php echo e($ct['role'] === 1 ? 'Đổi thành Phụ trách' : 'Đổi thành Chủ nhiệm'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>

                    
                    <button
                        wire:click="remove(<?php echo e($ct['id']); ?>)"
                        onclick="return confirm('Xóa GLV khỏi lớp?')"
                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 
                                   rounded-lg transition"
                        title="Xóa khỏi lớp">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.empty-state','data' => ['icon' => 'users','title' => 'Chưa có GLV','description' => 'Hãy thêm Giáo lý viên cho lớp này']]); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['icon' => 'users','title' => 'Chưa có GLV','description' => 'Hãy thêm Giáo lý viên cho lớp này']); ?>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'openModal','icon' => 'plus']]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'openModal','icon' => 'plus']); ?>
                Thêm GLV
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    
    <?php if($showModal): ?>
    <div
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        wire:click="closeModal">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            wire:click.stop>

            
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <h2 class="text-xl font-bold text-slate-900">
                    Phân công Giáo lý viên
                </h2>
                <p class="text-sm text-slate-600 mt-1">
                    Lớp: <span class="font-semibold"><?php echo e($lop->name); ?></span>
                </p>
            </div>

            
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                
                <?php if($errors->any()): ?>
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">
                                Vui lòng kiểm tra lại thông tin
                            </h4>
                            <ul class="space-y-1 text-sm text-red-700">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 font-bold">•</span>
                                    <span><?php echo e($error); ?></span>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Vai trò <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <div
                            wire:click="$set('selectedRole', 1)"
                            class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition
                   <?php echo e($selectedRole === 1 ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'); ?>">

                            
                            <input
                                type="radio"
                                wire:model="selectedRole"
                                value="1"
                                id="role-chu-nhiem"
                                class="absolute opacity-0 pointer-events-none">

                            
                            <div class="flex items-center gap-2 w-full">
                                
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                            <?php echo e($selectedRole === 1 ? 'border-blue-500 bg-blue-500' : 'border-slate-300'); ?>">
                                    <?php if($selectedRole === 1): ?>
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                    </svg>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">Chủ nhiệm</div>
                                    <div class="text-xs text-slate-500">Phụ trách chính</div>
                                </div>
                            </div>
                        </div>

                        
                        <div
                            wire:click="$set('selectedRole', 2)"
                            class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition
                   <?php echo e($selectedRole === 2 ? 'border-purple-500 bg-purple-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'); ?>">

                            
                            <input
                                type="radio"
                                wire:model="selectedRole"
                                value="2"
                                id="role-pho"
                                class="absolute opacity-0 pointer-events-none">

                            
                            <div class="flex items-center gap-2 w-full">
                                
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                            <?php echo e($selectedRole === 2 ? 'border-purple-500 bg-purple-500' : 'border-slate-300'); ?>">
                                    <?php if($selectedRole === 2): ?>
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                    </svg>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">Phụ trách</div>
                                    <div class="text-xs text-slate-500">Hỗ trợ</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <?php $__errorArgs = ['selectedRole'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <?php echo e($message); ?>

                    </p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Tìm kiếm Giáo lý viên
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.debounce.300ms="teacherSearch"
                            placeholder="Nhập tên hoặc số điện thoại..."
                            class="w-full pl-10 pr-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn Giáo lý viên <span class="text-red-500">*</span>
                    </label>

                    <?php if($availableTeachers->isNotEmpty()): ?>
                    <div class="border border-slate-200 rounded-xl max-h-60 overflow-y-auto">
                        <?php $__currentLoopData = $availableTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label
                            class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition
                                       <?php echo e($selectedTeacherId === $teacher->id ? 'bg-primary-50' : ''); ?>">
                            <input
                                type="radio"
                                wire:model="selectedTeacherId"
                                value="<?php echo e($teacher->id); ?>"
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500">

                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 truncate"><?php echo e($teacher->name); ?></p>
                                <div class="flex items-center gap-3 text-sm text-slate-500 mt-0.5">
                                    <?php if($teacher->phone_number): ?>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <?php echo e($teacher->phone_number); ?>

                                    </span>
                                    <?php endif; ?>
                                    <?php if($teacher->position): ?>
                                    <span class="text-xs px-2 py-0.5 bg-slate-100 rounded-full">
                                        <?php echo e($teacher->position); ?>

                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 border border-slate-200 rounded-xl">
                        <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2 text-slate-500">
                            <?php echo e(empty($teacherSearch) ? 'Không có GLV nào' : 'Không tìm thấy kết quả'); ?>

                        </p>
                    </div>
                    <?php endif; ?>

                    <?php $__errorArgs = ['selectedTeacherId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm text-red-500"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'closeModal','variant' => 'secondary']]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'closeModal','variant' => 'secondary']); ?>
                    Hủy
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'assign','icon' => 'check','loading' => true,'disabled' => !$selectedTeacherId]]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'assign','icon' => 'check','loading' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$selectedTeacherId)]); ?>
                    Phân công
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/lop/assign-teacher.blade.php ENDPATH**/ ?>