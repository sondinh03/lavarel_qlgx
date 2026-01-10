<div class="container mx-auto px-4 py-8">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-600 mt-1">
                <?php if($activeSchoolYear): ?>
                    Năm học: <?php echo e($activeSchoolYear->name); ?>

                <?php else: ?>
                    Chưa có năm học được kích hoạt
                <?php endif; ?>
            </p>
        </div>
        
        <button 
            wire:click="refreshDashboard"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Làm mới
        </button>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Học sinh</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo e(number_format($totalStudents)); ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-blue-100 text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Đang hoạt động
            </div>
        </div>

        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Giáo viên</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo e(number_format($totalTeachers)); ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-green-100 text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
                Giảng dạy hiện tại
            </div>
        </div>

        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Lớp học</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo e(number_format($totalClasses)); ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-purple-100 text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                Năm học hiện tại
            </div>
        </div>
    </div>

    
    <?php if(!empty($quickStats)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <?php $__currentLoopData = $quickStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm"><?php echo e($stat['label']); ?></p>
                    <h4 class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($stat['value']); ?></h4>
                </div>
                <div class="bg-<?php echo e($stat['color']); ?>-100 text-<?php echo e($stat['color']); ?>-600 rounded-full p-3">
                    <?php if($stat['icon'] === 'users'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <?php elseif($stat['icon'] === 'trending-up'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <?php elseif($stat['icon'] === 'user-check'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Học sinh theo khối</h3>
            
            <?php if(!empty($studentsByGrade)): ?>
            <div class="space-y-3">
                <?php
                    $maxCount = collect($studentsByGrade)->max('count');
                ?>
                
                <?php $__currentLoopData = $studentsByGrade; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700"><?php echo e($item['grade']); ?></span>
                        <span class="text-sm font-bold text-gray-900"><?php echo e($item['count']); ?> HS</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div 
                            class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                            style="width: <?php echo e($maxCount > 0 ? ($item['count'] / $maxCount * 100) : 0); ?>%"
                        ></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p>Chưa có dữ liệu</p>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Thống kê giới tính</h3>
            
            <?php if($studentsByGender['male'] > 0 || $studentsByGender['female'] > 0): ?>
            <div class="space-y-6">
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Nam</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900"><?php echo e(number_format($studentsByGender['male'])); ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <?php
                            $total = $studentsByGender['male'] + $studentsByGender['female'];
                            $malePercent = $total > 0 ? ($studentsByGender['male'] / $total * 100) : 0;
                        ?>
                        <div 
                            class="bg-blue-500 h-3 rounded-full transition-all duration-500"
                            style="width: <?php echo e($malePercent); ?>%"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><?php echo e(number_format($malePercent, 1)); ?>%</p>
                </div>

                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-pink-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Nữ</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900"><?php echo e(number_format($studentsByGender['female'])); ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <?php
                            $femalePercent = $total > 0 ? ($studentsByGender['female'] / $total * 100) : 0;
                        ?>
                        <div 
                            class="bg-pink-500 h-3 rounded-full transition-all duration-500"
                            style="width: <?php echo e($femalePercent); ?>%"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><?php echo e(number_format($femalePercent, 1)); ?>%</p>
                </div>

                
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Tổng cộng</span>
                        <span class="text-lg font-bold text-gray-900"><?php echo e(number_format($total)); ?></span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p>Chưa có dữ liệu</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(!empty($recentActivities)): ?>
    <div class="mt-8 bg-white rounded-lg shadow p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Hoạt động gần đây</h3>
        
        <div class="space-y-4">
            <?php $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="bg-<?php echo e($activity['color']); ?>-100 text-<?php echo e($activity['color']); ?>-600 rounded-full p-2 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900"><?php echo e($activity['title']); ?></h4>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($activity['description']); ?></p>
                    <p class="text-xs text-gray-500 mt-2"><?php echo e($activity['time']); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="<?php echo e(route('lop.index')); ?>" class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200 hover:border-blue-500">
            <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Quản lý lớp</span>
        </a>

        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200 hover:border-green-500">
            <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Học sinh</span>
        </a>

        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200 hover:border-purple-500">
            <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Giáo viên</span>
        </a>

        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200 hover:border-yellow-500">
            <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Báo cáo</span>
        </a>
    </div>
</div>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/home.blade.php ENDPATH**/ ?>