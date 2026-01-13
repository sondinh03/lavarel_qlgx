<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
                ['label' => 'Trang chủ', 'url' => route('home')],
                ['label' => 'Điểm danh', 'url' => route('attendance.show')],
                ['label' => $this->selectedClassName]
            ],'separator' => 'arrow']]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Trang chủ', 'url' => route('home')],
                ['label' => 'Điểm danh', 'url' => route('attendance.show')],
                ['label' => $this->selectedClassName]
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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Điểm danh - '.e($this->selectedClassName).'','description' => 'Điểm danh '.e($attendanceType == 1 ? 'đi học' : 'đi lễ').' cho '.e($students->count()).' học sinh • '.e(count($sessions)).' buổi','statValue' => $students->count(),'statLabel' => 'Học sinh','iconType' => 'attendance']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Điểm danh - '.e($this->selectedClassName).'','description' => 'Điểm danh '.e($attendanceType == 1 ? 'đi học' : 'đi lễ').' cho '.e($students->count()).' học sinh • '.e(count($sessions)).' buổi','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($students->count()),'stat-label' => 'Học sinh','icon-type' => 'attendance']); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

            
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                    
                    <div class="flex items-center gap-3 flex-1 w-full lg:w-auto">
                        
                        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => true,'showLop' => true,'showKy' => true,'selectedNamHoc' => $selectedNamHoc,'selectedKhoi' => $selectedKhoi,'selectedLop' => $selectedClassId,'selectedKy' => $selectedKy])->html();
} elseif ($_instance->childHasBeenRendered('l342222745-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l342222745-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l342222745-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l342222745-0');
} else {
    $response = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => true,'showLop' => true,'showKy' => true,'selectedNamHoc' => $selectedNamHoc,'selectedKhoi' => $selectedKhoi,'selectedLop' => $selectedClassId,'selectedKy' => $selectedKy]);
    $html = $response->html();
    $_instance->logRenderedChild('l342222745-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

                        
                        <input
                            wire:model.live.debounce.500ms="search"
                            placeholder="Tìm học sinh..."
                            class="w-56 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    
                    <div class="flex items-center gap-3">
                        
                        <button
                            class="px-4 py-2 bg-white border border-slate-300 rounded-xl
                                text-sm font-medium text-slate-700
                                hover:bg-slate-50 transition-colors"
                            disabled>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Xuất Excel
                        </button>

                        
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'saveAttendance','icon' => 'save','loading' => true,'disabled' => empty($draftAttendance)]]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'saveAttendance','icon' => 'save','loading' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($draftAttendance))]); ?>
                            Lưu điểm danh
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="bg-primary-50 p-1 flex gap-1 border-b border-slate-200">
                <button
                    wire:click="$set('attendanceType', 1)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        <?php echo e($attendanceType == 1 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'); ?>">
                    Điểm danh đi học
                </button>
                <button
                    wire:click="$set('attendanceType', 2)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        <?php echo e($attendanceType == 2 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'); ?>">
                    Điểm danh đi lễ
                </button>
            </div>
        </div>

        
        <?php if($selectedClassId): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-600 font-medium">Tổng học sinh</div>
                        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e($students->count()); ?></div>
                    </div>
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-600 font-medium">Số buổi</div>
                        <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo e(count($sessions)); ?></div>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-600 font-medium">Chưa lưu</div>
                        <div class="text-2xl font-bold text-amber-600 mt-1"><?php echo e(count($draftAttendance)); ?></div>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-600 font-medium">Loại điểm danh</div>
                        <div class="text-lg font-bold text-purple-600 mt-1">
                            <?php echo e($attendanceType == 1 ? 'Đi học' : 'Đi lễ'); ?>

                        </div>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!$selectedClassId): ?>
        <div class="text-center text-gray-500 py-10">
            Vui lòng chọn lớp để bắt đầu điểm danh
        </div>
        <?php else: ?>
        <?php if($students->isEmpty()): ?>
        <div class="text-center text-gray-500 py-10">
            Lớp chưa có học sinh
        </div>
        <?php else: ?>
        
        <?php if($selectedClassId): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if($students->count() > 0 && count($sessions) > 0): ?>
            
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'sticky left-0 bg-slate-50 z-20']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'sticky left-0 bg-slate-50 z-20']); ?># <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'sticky left-12 bg-slate-50 z-20 min-w-[200px]']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'sticky left-12 bg-slate-50 z-20 min-w-[200px]']); ?>Họ và tên <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-header','data' => ['class' => 'text-center min-w-[120px]']]); ?>
<?php $component->withName('table-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'text-center min-w-[120px]']); ?>
                                <div class="flex flex-col gap-1">
                                    <div class="<?php echo e($session['locked'] ? 'text-slate-400' : ''); ?> flex items-center justify-center gap-1">
                                        <span><?php echo e($session['dayName']); ?></span>
                                        <?php if($session['locked']): ?>
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[10px] <?php echo e($session['locked'] ? 'text-slate-400' : 'text-slate-600'); ?>">
                                        <?php echo e($session['fullDate']); ?>

                                    </div>
                                    <?php if(!$session['locked']): ?>
                                    <button
                                        wire:click="markAllPresent(<?php echo e($session['id']); ?>)"
                                        class="text-[9px] text-green-600 hover:text-green-700 hover:underline"
                                        wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="markAllPresent">✓ Tất cả</span>
                                        <span wire:loading wire:target="markAllPresent">⏳</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="student-<?php echo e($student->id); ?>">
                            
                            <td class="px-6 py-4 text-sm text-slate-500 sticky left-0 bg-white z-10">
                                <?php echo e($index + 1); ?>

                            </td>

                            
                            <td class="px-6 py-4 sticky left-12 bg-white z-10">
                                <div class="text-xs text-slate-500"><?php echo e($student->saint_name); ?></div>
                                <div class="font-semibold text-slate-900">
                                    <?php echo e($student->last_name); ?> <?php echo e($student->name); ?>

                                </div>
                            </td>

                            
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $status = $this->getAttendanceStatus($student->id, $session['dateStr']);
                            ?>
                            <td class="px-3 py-3 text-center">
                                <?php if($session['locked']): ?>
                                
                                <div class="flex items-center justify-center h-8">
                                    <?php if($status == 1): ?>
                                    <span class="text-green-700 font-medium">✓</span>
                                    <?php elseif($status == 2): ?>
                                    <span class="text-yellow-700 font-medium">P</span>
                                    <?php elseif($status == 3): ?>
                                    <span class="text-red-700 font-medium">✕</span>
                                    <?php else: ?>
                                    <span class="text-xs text-slate-400">-</span>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                
                                <div class="flex gap-1 justify-center">
                                    <button
                                        wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($session['id']); ?>, <?php echo e($status == 1 ? 'null' : 1); ?>)"
                                        class="px-2 py-1 rounded text-xs font-medium transition-all
                                            <?php echo e($status == 1 ? 'bg-green-500 text-white shadow-md scale-105' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'); ?>">
                                        ✓
                                    </button>
                                    <button
                                        wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($session['id']); ?>, <?php echo e($status == 2 ? 'null' : 2); ?>)"
                                        class="px-2 py-1 rounded text-xs font-medium transition-all
                                            <?php echo e($status == 2 ? 'bg-yellow-400 text-slate-900 shadow-md scale-105' : 'bg-amber-100 text-amber-800 border border-yellow-200 hover:bg-yellow-100'); ?>">
                                        P
                                    </button>
                                    <button
                                        wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($session['id']); ?>, <?php echo e($status == 3 ? 'null' : 3); ?>)"
                                        class="px-2 py-1 rounded text-xs font-medium transition-all
                                            <?php echo e($status == 3 ? 'bg-red-500 text-white shadow-md scale-105' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100'); ?>">
                                        ✕
                                    </button>
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        
                        <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                            <td colspan="2" class="px-6 py-3 text-sm text-slate-900 sticky left-0 bg-slate-50 z-10">
                                Thống kê
                            </td>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $stats = $this->getDateStats($session['dateStr']);
                            ?>
                            <td class="px-3 py-3 text-center">
                                <div class="flex flex-col gap-1 text-xs">
                                    <div class="text-green-600">✓ <?php echo e($stats['present']); ?></div>
                                    <div class="text-yellow-600">P <?php echo e($stats['absentPermitted']); ?></div>
                                    <div class="text-red-600">✕ <?php echo e($stats['absentNotPermitted']); ?></div>
                                </div>
                            </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div class="lg:hidden">
                
                <div class="sticky top-0 z-30 bg-white p-4 border-b border-slate-200 shadow-sm">
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-slate-900">
                            Chọn ngày <?php echo e($attendanceType == 1 ? 'đi học' : 'đi lễ'); ?>

                        </label>
                        <select
                            wire:model.live="selectedDate"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300
                    bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($session['dateStr']); ?>">
                                <?php echo e($session['dayName']); ?> - <?php echo e($session['fullDate']); ?>

                                <?php echo e($session['locked'] ? '🔒' : ''); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <?php
                        $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
                        $locked = $currentSession['locked'] ?? false;
                        $stats = $this->getDateStats($selectedDate);
                        ?>

                        
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-green-50 p-2 rounded-lg border border-green-200 text-center">
                                <div class="text-xs text-green-700 font-medium">Có mặt</div>
                                <div class="text-lg font-bold text-green-600"><?php echo e($stats['present']); ?></div>
                            </div>
                            <div class="bg-yellow-50 p-2 rounded-lg border border-yellow-200 text-center">
                                <div class="text-xs text-yellow-700 font-medium">Vắng CP</div>
                                <div class="text-lg font-bold text-yellow-600"><?php echo e($stats['absentPermitted']); ?></div>
                            </div>
                            <div class="bg-red-50 p-2 rounded-lg border border-red-200 text-center">
                                <div class="text-xs text-red-700 font-medium">Vắng KP</div>
                                <div class="text-lg font-bold text-red-600"><?php echo e($stats['absentNotPermitted']); ?></div>
                            </div>
                        </div>

                        
                        <?php if(!$locked): ?>
                        <button
                            wire:click="markAllPresent(<?php echo e($currentSession['id'] ?? 0); ?>)"
                            class="w-full py-2.5 px-4 bg-green-500 hover:bg-green-600 text-white rounded-xl
                    flex items-center justify-center gap-2 font-medium transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Có mặt tất cả
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 border-b-2 border-slate-200 sticky top-[200px] z-20">
                            <tr>
                                <th class="w-12 px-2 py-3 text-left text-xs font-bold text-slate-900 uppercase tracking-wider border-r border-slate-200">
                                    STT
                                </th>
                                <th class="w-32 px-2 py-3 text-left text-xs font-bold text-slate-900 uppercase tracking-wider border-r border-slate-200">
                                    Họ và tên
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-900 uppercase tracking-wider">
                                    <div class="flex flex-col gap-1">
                                        <span class="<?php echo e($locked ? 'text-slate-400' : ''); ?>">Điểm danh</span>
                                        <?php if($locked): ?>
                                        <div class="flex items-center justify-center gap-1 text-slate-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span class="text-[10px]">Đã khóa</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $status = $this->getAttendanceStatus($student->id, $selectedDate);
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors" wire:key="mobile-student-<?php echo e($student->id); ?>">
                                
                                <td class="w-12 px-2 py-3 text-sm text-slate-500 font-medium border-r border-slate-100">
                                    <?php echo e($index + 1); ?>

                                </td>

                                
                                <td class="w-32 px-2 py-3 border-r border-slate-100">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-slate-500 leading-tight"><?php echo e($student->saint_name); ?></span>
                                        <span class="font-semibold text-slate-900 text-xs leading-tight">
                                            <?php echo e($student->last_name); ?> <?php echo e($student->name); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <?php if($locked): ?>
                                    
                                    <div class="flex items-center justify-center">
                                        <?php if($status == 1): ?>
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="text-[9px] text-green-700 font-medium">Có mặt</span>
                                        </div>
                                        <?php elseif($status == 2): ?>
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center shadow-sm">
                                                <span class="text-slate-900 font-bold text-sm">P</span>
                                            </div>
                                            <span class="text-[9px] text-yellow-700 font-medium">Vắng CP</span>
                                        </div>
                                        <?php elseif($status == 3): ?>
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </div>
                                            <span class="text-[9px] text-red-700 font-medium">Vắng KP</span>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-xs text-slate-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    
                                    <div class="flex gap-1 justify-center">
                                        <button
                                            wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($currentSession['id'] ?? 0); ?>, <?php echo e($status == 1 ? 'null' : 1); ?>)"
                                            class="w-9 h-9 rounded-md flex items-center justify-center transition
                                                <?php echo e($status == 1
                                                    ? 'bg-green-500 text-white shadow-sm'
                                                    : 'bg-green-50 text-green-700 border border-green-100 hover:bg-green-100'); ?>"
                                            wire:loading.attr="disabled">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>


                                        <button
                                            wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($currentSession['id'] ?? 0); ?>, <?php echo e($status == 2 ? 'null' : 2); ?>)"
                                            class="w-9 h-9 rounded-md flex items-center justify-center font-semibold transition
                                                <?php echo e($status == 2
                                                    ? 'bg-yellow-400 text-slate-900 shadow-sm'
                                                    : 'bg-yellow-50 text-yellow-700 border border-yellow-100 hover:bg-yellow-100'); ?>"
                                            wire:loading.attr="disabled">
                                            P
                                        </button>


                                        <button
                                            wire:click="setAttendance(<?php echo e($student->id); ?>, <?php echo e($currentSession['id'] ?? 0); ?>, <?php echo e($status == 3 ? 'null' : 3); ?>)"
                                            class="w-9 h-9 rounded-md flex items-center justify-center transition
                                                <?php echo e($status == 3
                                                    ? 'bg-red-500 text-white shadow-sm'
                                                    : 'bg-red-50 text-red-700 border border-red-100 hover:bg-red-100'); ?>"
                                            wire:loading.attr="disabled">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>

                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="sticky bottom-0 bg-slate-50 border-t border-slate-200 p-3 shadow-lg">
                    <div class="flex flex-wrap items-center justify-center gap-3 text-[10px] text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded bg-green-500"></span>
                            <span>Có mặt</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded bg-yellow-400"></span>
                            <span>Vắng CP</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded bg-red-500"></span>
                            <span>Vắng KP</span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-green-500"></span>
                        <span>Có mặt (✓)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-yellow-400"></span>
                        <span>Vắng có phép (P)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-red-500"></span>
                        <span>Vắng không phép (✕)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Buổi đã đóng (🔒)</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <div class="text-center py-12">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">
                    <?php if($students->isEmpty()): ?>
                    Chưa có học sinh trong lớp này
                    <?php elseif(empty($sessions)): ?>
                    Chưa có buổi điểm danh nào
                    <?php else: ?>
                    Không có dữ liệu để hiển thị
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để điểm danh</p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<?php if($this->hasUnsavedChanges): ?>
<div class="fixed bottom-4 right-4 bg-amber-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-pulse">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <span><?php echo e($this->pendingCount); ?> thay đổi chưa lưu</span>
</div>
<?php endif; ?>


<button
    wire:click="discardDrafts"
    class="px-4 py-2 border border-red-300 text-red-700 rounded-xl hover:bg-red-50"
    @disabled(!$this->hasUnsavedChanges)>
    Hủy thay đổi
</button>


<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('livewire:load', function() {
        /* =============================
         * 1️⃣ Detect View Mode (mobile / desktop)
         * ============================= */
        function detectViewMode() {
            const isMobile = window.innerWidth < 1024; // Tailwind lg breakpoint
            Livewire.emit('viewModeDetected', isMobile ? 'mobile' : 'desktop');
        }

        detectViewMode();

        // Debounce resize để tránh emit liên tục
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(detectViewMode, 200);
        });

        /* =============================
         * 2️⃣ Keyboard shortcut: Ctrl / Cmd + S
         * ============================= */
        window.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S: Save attendance
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                window.livewire.find('<?php echo e($_instance->id); ?>').call('saveAttendance');
            }
        });
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/attendance-manager.blade.php ENDPATH**/ ?>