<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
                ['label' => 'Trang chủ', 'url' => route('dashboard')],
                ['label' => 'Quản lý phiên điểm danh', 'url' => route('session.index')],
                ['label' => $this->selectedClassName]
            ],'separator' => 'arrow']]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Trang chủ', 'url' => route('dashboard')],
                ['label' => 'Quản lý phiên điểm danh', 'url' => route('session.index')],
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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['title' => 'Quản lý phiên điểm danh - '.e($this->selectedClassName).'','description' => 'Tạo và quản lý các phiên điểm danh cho lớp học','statValue' => $sessions?->count(),'statLabel' => 'Phiên điểm danh','iconType' => 'calendar']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['title' => 'Quản lý phiên điểm danh - '.e($this->selectedClassName).'','description' => 'Tạo và quản lý các phiên điểm danh cho lớp học','stat-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sessions?->count()),'stat-label' => 'Phiên điểm danh','icon-type' => 'calendar']); ?>
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
    $html = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => true,'showLop' => true,'showKy' => false,'selectedNamHoc' => $selectedNamHoc,'selectedKhoi' => $selectedKhoi,'selectedLop' => $selectedClassId])->html();
} elseif ($_instance->childHasBeenRendered('l1259578529-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l1259578529-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l1259578529-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l1259578529-0');
} else {
    $response = \Livewire\Livewire::mount('filters.filter-bar', ['parishId' => $parishId,'showNamHoc' => true,'showKhoi' => true,'showLop' => true,'showKy' => false,'selectedNamHoc' => $selectedNamHoc,'selectedKhoi' => $selectedKhoi,'selectedLop' => $selectedClassId]);
    $html = $response->html();
    $_instance->logRenderedChild('l1259578529-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

                        
                        <input
                            wire:model.live.debounce.500ms="search"
                            placeholder="Tìm phiên..."
                            class="w-56 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    
                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'create','icon' => 'plus','disabled' => !$selectedClassId]]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'create','icon' => 'plus','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$selectedClassId)]); ?>
                            Tạo phiên mới
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>

            
            <?php if($currentNamHoc): ?>
            <div class="px-6 py-3 bg-blue-50 border-b border-blue-200">
                <div class="flex items-center gap-2 text-sm text-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium"><?php echo e($currentNamHoc->name); ?></span>
                    <?php if($currentNamHoc->start_date_one && $currentNamHoc->end_date_one): ?>
                    <span class="text-blue-600">
                        • HK1: <?php echo e($currentNamHoc->semester_1_display); ?>

                    </span>
                    <?php endif; ?>
                    <?php if($currentNamHoc->start_date_two && $currentNamHoc->end_date_two): ?>
                    <span class="text-blue-600">
                        • HK2: <?php echo e($currentNamHoc->semester_2_display); ?>

                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if($selectedClassId): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if($sessions && $sessions->count() > 0): ?>
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
<?php $component->withAttributes([]); ?>Ngày <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Loại <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Tiêu đề <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Thời gian <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['class' => 'text-center']); ?>Điểm danh <?php echo $__env->renderComponent(); ?>
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

                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="session-<?php echo e($session['id']); ?>">
                            
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?php echo e($index + 1); ?>

                            </td>

                            
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-900">
                                        <?php echo e($session['dayName']); ?> - <?php echo e($session['fullDate']); ?>

                                    </span>
                                    <span class="text-xs text-slate-500">
                                        
                                        <?php echo e($session['date']); ?>


                                    </span>
                                </div>
                            </td>

                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    <?php echo e($session['type'] == 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'); ?>">
                                    <?php echo e($session['typeLabel']); ?>

                                </span>
                            </td>

                            
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-700">
                                    <?php echo e($session['title'] ?: '-'); ?>

                                </span>
                            </td>

                            
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php if($session['start_time'] || $session['end_time']): ?>
                                <?php echo e($session['start_time'] ?? '--:--'); ?> - <?php echo e($session['end_time'] ?? '--:--'); ?>

                                <?php else: ?>
                                <span class="text-slate-400">Chưa đặt</span>
                                <?php endif; ?>
                            </td>

                            
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        <span class="text-green-700 font-medium"><?php echo e($session['stats']['present']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                                        <span class="text-yellow-700 font-medium"><?php echo e($session['stats']['absent_excused']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="text-red-700 font-medium"><?php echo e($session['stats']['absent_unexcused']); ?></span>
                                    </div>
                                </div>
                                <?php if($session['stats']['total'] > 0): ?>
                                <div class="text-center mt-1">
                                    <span class="text-xs text-slate-500">
                                        <?php echo e(number_format($session['stats']['present_rate'], 1)); ?>% có mặt
                                    </span>
                                </div>
                                <?php endif; ?>
                            </td>

                            
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo e($session['statusClass']); ?>">
                                    <?php if($session['locked']): ?>
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <?php endif; ?>
                                    <?php echo e($session['statusLabel']); ?>

                                </span>
                            </td>

                            
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    
                                    <a href="<?php echo e(route('attendance.show', ['classId' => $selectedClassId, 'type' => $session['type'], 'date' => $session['dateStr']])); ?>"
                                        class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 
                                               font-semibold text-sm transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                        Điểm danh
                                    </a>

                                    <span class="text-slate-300">|</span>

                                    
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-action','data' => ['wire' => 'toggleStatus('.e($session['id']).')','icon' => $session['locked'] ? 'check' : 'archive','color' => $session['locked'] ? 'success' : 'warning','loading' => true]]); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'toggleStatus('.e($session['id']).')','icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session['locked'] ? 'check' : 'archive'),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session['locked'] ? 'success' : 'warning'),'loading' => true]); ?>
                                        <?php echo e($session['locked'] ? 'Mở' : 'Khóa'); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

                                    <span class="text-slate-300">|</span>

                                    
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.table-action','data' => ['wire' => 'delete('.e($session['id']).')','icon' => 'trash','color' => 'danger','loading' => true,'confirm' => 'Xóa phiên này?']]); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'delete('.e($session['id']).')','icon' => 'trash','color' => 'danger','loading' => true,'confirm' => 'Xóa phiên này?']); ?>
                                        Xóa
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span>Có mặt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span>Vắng có phép</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span>Vắng không phép</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <div class="text-center py-12">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có phiên điểm danh nào</p>
                <button
                    wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700
                        transition-all flex items-center gap-2 mx-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tạo phiên đầu tiên
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để quản lý phiên điểm danh</p>
        </div>
        <?php endif; ?>

        
        <?php if($showForm): ?>
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="session-modal-title"
            wire:click="closeModal">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop
                x-data="{ mode: <?php if ((object) ('createMode') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('createMode'->value()); ?>')<?php echo e('createMode'->hasModifier('defer') ? '.defer' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('createMode'); ?>')<?php endif; ?> }">

                
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 id="session-modal-title" class="text-xl font-bold text-slate-900">
                                Tạo phiên điểm danh
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                Thiết lập thông tin cho các phiên điểm danh
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
                                    <li>• <?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Loại điểm danh <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                wire:click="$set('type', 1)"
                                class="px-4 py-3 rounded-xl border-2 transition-all text-left
                                    <?php echo e($type == 1 ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'); ?>">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center
                                        <?php echo e($type == 1 ? 'border-blue-500' : 'border-slate-300'); ?>">
                                        <?php if($type == 1): ?>
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-semibold <?php echo e($type == 1 ? 'text-blue-700' : 'text-slate-700'); ?>">
                                        Điểm danh đi học
                                    </span>
                                </div>
                            </button>

                            <button
                                type="button"
                                wire:click="$set('type', 2)"
                                class="px-4 py-3 rounded-xl border-2 transition-all text-left
                                    <?php echo e($type == 2 ? 'border-purple-500 bg-purple-50' : 'border-slate-200 hover:border-slate-300'); ?>">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center
                                        <?php echo e($type == 2 ? 'border-purple-500' : 'border-slate-300'); ?>">
                                        <?php if($type == 2): ?>
                                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-semibold <?php echo e($type == 2 ? 'text-purple-700' : 'text-slate-700'); ?>">
                                        Điểm danh đi lễ
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Chế độ tạo <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <button
                                type="button"
                                @click="mode = 'single'"
                                class="px-3 py-2 rounded-lg border-2 transition-all text-sm font-medium
                                    <?php echo e($createMode == 'single' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-200 hover:border-slate-300 text-slate-700'); ?>">
                                Ngày đơn
                            </button>
                            <button
                                type="button"
                                @click="mode = 'weekly'"
                                class="px-3 py-2 rounded-lg border-2 transition-all text-sm font-medium
                                    <?php echo e($createMode == 'weekly' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-200 hover:border-slate-300 text-slate-700'); ?>">
                                Theo tuần
                            </button>
                            <button
                                type="button"
                                @click="mode = 'custom'"
                                class="px-3 py-2 rounded-lg border-2 transition-all text-sm font-medium
                                    <?php echo e($createMode == 'custom' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-200 hover:border-slate-300 text-slate-700'); ?>">
                                Tùy chọn
                            </button>
                        </div>
                    </div>

                    
                    <div x-show="mode === 'single'">
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Ngày điểm danh','name' => 'startDate','type' => 'date','wire:model.defer' => 'startDate','required' => true]]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Ngày điểm danh','name' => 'startDate','type' => 'date','wire:model.defer' => 'startDate','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                    </div>

                    
                    <div x-show="mode === 'weekly'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Từ ngày','name' => 'startDate','type' => 'date','wire:model.defer' => 'startDate','required' => true]]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Từ ngày','name' => 'startDate','type' => 'date','wire:model.defer' => 'startDate','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Đến ngày','name' => 'endDate','type' => 'date','wire:model.defer' => 'endDate']]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Đến ngày','name' => 'endDate','type' => 'date','wire:model.defer' => 'endDate']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Chọn các ngày trong tuần <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                <?php
                                $days = [
                                ['value' => 0, 'label' => 'CN'],
                                ['value' => 1, 'label' => 'T2'],
                                ['value' => 2, 'label' => 'T3'],
                                ['value' => 3, 'label' => 'T4'],
                                ['value' => 4, 'label' => 'T5'],
                                ['value' => 5, 'label' => 'T6'],
                                ['value' => 6, 'label' => 'T7'],
                                ];
                                ?>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer
                                    <?php echo e(in_array($day['value'], $weekDays) ? 'border-primary-500 bg-primary-50' : 'border-slate-200 hover:border-slate-300'); ?>">
                                    <input
                                        type="checkbox"
                                        wire:model="weekDays"
                                        value="<?php echo e($day['value']); ?>"
                                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm font-medium <?php echo e(in_array($day['value'], $weekDays) ? 'text-primary-700' : 'text-slate-700'); ?>">
                                        <?php echo e($day['label']); ?>

                                    </span>
                                </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    
                    <div x-show="mode === 'custom'">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Chọn các ngày cụ thể <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-slate-300 rounded-xl p-4 bg-slate-50">
                            <input
                                type="date"
                                wire:model.defer="startDate"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300
                                    focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="mt-2 text-xs text-slate-500">
                                📌 Tính năng chọn nhiều ngày sẽ được bổ sung sau
                            </p>
                        </div>
                    </div>

                    
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Tiêu đề (không bắt buộc)','name' => 'title','wire:model.defer' => 'title','placeholder' => 'VD: Tuần lễ Phục sinh, Thánh lễ khai giảng...']]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Tiêu đề (không bắt buộc)','name' => 'title','wire:model.defer' => 'title','placeholder' => 'VD: Tuần lễ Phục sinh, Thánh lễ khai giảng...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

                    
                    <div class="grid grid-cols-2 gap-4">
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Giờ bắt đầu','name' => 'startTime','type' => 'time','wire:model.defer' => 'startTime']]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Giờ bắt đầu','name' => 'startTime','type' => 'time','wire:model.defer' => 'startTime']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form-input','data' => ['label' => 'Giờ kết thúc','name' => 'endTime','type' => 'time','wire:model.defer' => 'endTime']]); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => 'Giờ kết thúc','name' => 'endTime','type' => 'time','wire:model.defer' => 'endTime']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                    </div>

                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-blue-700">Lưu ý</h4>
                                <ul class="text-sm text-blue-600 mt-1 space-y-1">
                                    <li>• Chỉ tạo phiên trong khoảng thời gian năm học</li>
                                    <li>• Phiên đã tồn tại sẽ bị bỏ qua</li>
                                    <li>• Có thể tạo nhiều phiên cùng lúc ở chế độ "Theo tuần"</li>
                                </ul>
                            </div>
                        </div>
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
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.action-button','data' => ['wire' => 'save','icon' => 'save','loading' => true]]); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire' => 'save','icon' => 'save','loading' => true]); ?>
                        Tạo phiên
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
    </div>
</div>


<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/attendance/session-manager.blade.php ENDPATH**/ ?>