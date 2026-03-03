<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    
    
    
    <div class="mx-auto max-w-7xl space-y-5">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Xin chào, <?php echo e(auth()->user()->name ?? 'Quản trị viên'); ?> 👋
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        <?php echo e($todayLabel); ?>

                        <?php if($activeSchoolYear): ?>
                        &nbsp;·&nbsp;
                        <span class="font-medium text-slate-700">
                            Năm học <?php echo e($activeSchoolYear->name); ?>

                        </span>
                        <?php if($semesterLabel): ?>
                        &nbsp;·&nbsp;
                        <span class="text-primary-600 font-medium"><?php echo e($semesterLabel); ?></span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>

                
                <button
                    wire:click="refresh"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium
                           text-slate-600 bg-slate-100 rounded-xl
                           hover:bg-slate-200 transition disabled:opacity-50">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Làm mới
                </button>
            </div>
        </div>

        
        <?php if(session()->has('message')): ?>
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => 'success','duration' => 3000]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'success','duration' => 3000]); ?>
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

        
        
        
        <?php if(!$activeSchoolYear): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
            <svg class="mx-auto w-14 h-14 text-amber-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h2 class="text-lg font-semibold text-amber-800">Chưa có năm học nào được kích hoạt</h2>
            <p class="text-sm text-amber-700 mt-1 mb-4">Vui lòng thiết lập năm học để bắt đầu sử dụng hệ thống</p>
            <a href="<?php echo e(route('school-years.index')); ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 text-white
                      text-sm font-medium rounded-xl hover:bg-amber-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Thiết lập năm học
            </a>
        </div>

        <?php else: ?>

        
        
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Học sinh</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">
                            <?php echo e(number_format($stats['students'])); ?>

                        </p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Đang theo học</p>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Lớp học</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">
                            <?php echo e(number_format($stats['classes'])); ?>

                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Đang hoạt động</p>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Giáo lý viên</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">
                            <?php echo e(number_format($stats['teachers'])); ?>

                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Đang phân công</p>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Điểm danh</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">
                            <?php if($stats['attendance'] !== null): ?>
                            <?php echo e($stats['attendance']); ?>%
                            <?php else: ?>
                            <span class="text-lg text-slate-400">—</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3">Tuần này</p>
            </div>
        </div>

        
        
        
        <?php if(count($todos) > 0): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-slate-900">Việc cần làm</h2>
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs
                                 font-bold bg-red-500 text-white rounded-full">
                        <?php echo e($todoCount); ?>

                    </span>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $todos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $todo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between px-6 py-4
                            hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        
                        <?php if($todo['type'] === 'warning'): ?>
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <?php else: ?>
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <?php endif; ?>

                        <span class="text-sm font-medium text-slate-800">
                            <?php echo e($todo['message']); ?>

                        </span>
                    </div>

                    <a href="<?php echo e(route($todo['route'])); ?>"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700
                              px-3 py-1.5 bg-primary-50 rounded-lg hover:bg-primary-100
                              transition whitespace-nowrap">
                        Xem ngay →
                    </a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-green-50 border border-green-200 rounded-2xl px-6 py-4
                    flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-green-800">
                Mọi thứ đang ổn — không có việc cần xử lý ngay
            </p>
        </div>
        <?php endif; ?>

        
        
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-base font-semibold text-slate-900">Điểm danh hôm nay</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Danh sách lớp học đang hoạt động
                    </p>
                </div>

                <?php if(count($todayAttendance) > 0): ?>
                <div class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $todayAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between px-6 py-3">
                        <div class="flex items-center gap-3">
                            <?php if($item['has_attendance']): ?>
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                            <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                            <?php endif; ?>

                            <div>
                                <p class="text-sm font-medium text-slate-800"><?php echo e($item['name']); ?></p>
                                <p class="text-xs text-slate-400"><?php echo e($item['block']); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php if($item['has_attendance']): ?>
                            <span class="text-sm font-semibold text-green-700">
                                <?php echo e($item['attended']); ?>/<?php echo e($item['students_count']); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-xs text-slate-400">Chưa điểm danh</span>
                            <?php endif; ?>

                            <a href="<?php echo e($item['url']); ?>"
                                class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                                Xem →
                            </a>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if(count($todayAttendance) >= 10): ?>
                <div class="px-6 py-3 border-t border-slate-100 text-center">
                    <a href="<?php echo e(route('classes.index')); ?>"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Xem tất cả lớp →
                    </a>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="px-6 py-10 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-sm text-slate-400">Chưa có lớp nào</p>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-base font-semibold text-slate-900">Học sinh theo khối</h2>
                    <div class="flex items-center gap-4 mt-1">
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                            Nam: <?php echo e(number_format($genderStats['male'])); ?>

                        </span>
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-pink-400 inline-block"></span>
                            Nữ: <?php echo e(number_format($genderStats['female'])); ?>

                        </span>
                    </div>
                </div>

                <?php if(count($studentsByGrade) > 0): ?>
                <?php
                $maxCount = collect($studentsByGrade)->max('count') ?: 1;
                ?>
                <div class="px-6 py-4 space-y-4">
                    <?php $__currentLoopData = $studentsByGrade; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-slate-700">
                                <?php echo e($item['grade']); ?>

                            </span>
                            <span class="text-sm font-bold text-slate-900">
                                <?php echo e(number_format($item['count'])); ?>

                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div
                                class="bg-primary-500 h-2 rounded-full transition-all duration-700"
                                style="width: <?php echo e($maxCount > 0 ? ($item['count'] / $maxCount * 100) : 0); ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?>
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        
        
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Truy cập nhanh</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-slate-100">

                
                <a href="<?php echo e(route('classes.index')); ?>"
                    class="flex flex-col items-center justify-center gap-2 py-6 bg-white
                           hover:bg-primary-50 transition-colors group">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center
                                group-hover:bg-primary-200 transition-colors">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-primary-700 transition-colors">
                        Quản lý lớp
                    </span>
                </a>

                
                <a href="<?php echo e(route('students.index')); ?>"
                    class="flex flex-col items-center justify-center gap-2 py-6 bg-white
                           hover:bg-green-50 transition-colors group">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center
                                group-hover:bg-green-200 transition-colors">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-green-700 transition-colors">
                        Học sinh
                    </span>
                </a>

                
                <a href="<?php echo e(route('catechists.index')); ?>"
                    class="flex flex-col items-center justify-center gap-2 py-6 bg-white
                           hover:bg-purple-50 transition-colors group">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center
                                group-hover:bg-purple-200 transition-colors">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-purple-700 transition-colors">
                        Giáo lý viên
                    </span>
                </a>

                
                <a href="<?php echo e(route('school-years.index')); ?>"
                    class="flex flex-col items-center justify-center gap-2 py-6 bg-white
                           hover:bg-amber-50 transition-colors group">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center
                                group-hover:bg-amber-200 transition-colors">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-amber-700 transition-colors">
                        Năm học
                    </span>
                </a>

            </div>
        </div>

        <?php endif; ?> 

    </div>
</div><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/home.blade.php ENDPATH**/ ?>