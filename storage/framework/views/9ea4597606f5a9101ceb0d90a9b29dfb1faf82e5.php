<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <div class="mx-auto max-w-7xl space-y-6">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Điểm danh', 'url' => route('attendance.show')],
            ['label' => $this->selectedClassName]
        ]]]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Điểm danh', 'url' => route('attendance.show')],
            ['label' => $this->selectedClassName]
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>



        
        <?php if(session()->has('message')): ?>
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-4 right-4 z-50 max-w-sm">
            <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span><?php echo e(session('message')); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if(session()->has('error')): ?>
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition
            class="fixed top-4 right-4 z-50 max-w-sm">
            <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span><?php echo e(session('error')); ?></span>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-slate-900">
                        Điểm danh - <?php echo e($this->selectedClassName ?? 'Chọn lớp'); ?>

                    </h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Điểm danh <?php echo e($attendanceType == 1 ? 'đi học' : 'đi lễ'); ?>

                        cho <?php echo e(count($students)); ?> học sinh • <?php echo e(count($sessions)); ?> buổi
                    </p>
                </div>
            </div>

            
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end">

                    
                    <div class="flex-1">
                        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('class-filter-selector', [
                        'parish_id' => $parish_id,
                        'showNamHoc' => true,
                        'showKhoi' => true,
                        'showLop' => true,
                        'showKy' => true,
                        ])->html();
} elseif ($_instance->childHasBeenRendered('l342222745-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l342222745-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l342222745-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l342222745-0');
} else {
    $response = \Livewire\Livewire::mount('class-filter-selector', [
                        'parish_id' => $parish_id,
                        'showNamHoc' => true,
                        'showKhoi' => true,
                        'showLop' => true,
                        'showKy' => true,
                        ]);
    $html = $response->html();
    $_instance->logRenderedChild('l342222745-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
                    </div>

                    
                    <div class="relative w-full lg:max-w-sm">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Tìm học sinh (tên thánh, họ, tên)..."
                            class="w-full px-4 py-2 pl-10 rounded-xl
                       border border-slate-300 bg-white
                       focus:ring-2 focus:ring-primary-500">

                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    
                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700">
                            Xuất Excel
                        </button>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="bg-white rounded-xl shadow-sm border-0">
            
            <div class="bg-primary-50 p-1 rounded-t-lg flex gap-1">
                <button wire:click="changeType(1)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold
                    <?php echo e($attendanceType == 1 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'); ?>">
                    Điểm danh đi học
                </button>
                <button wire:click="changeType(2)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold
                    <?php echo e($attendanceType == 2 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'); ?>">
                    Điểm danh đi lễ
                </button>
            </div>

            
            <div class="p-6" id="attendance-panel" role="tabpanel">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    <div class="bg-primary-50 rounded-xl p-3">
                        <div class="text-xs text-primary-600 font-semibold">Tổng học sinh</div>
                        <div class="text-2xl font-bold text-primary-700"><?php echo e(count($students)); ?></div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <div class="text-xs text-green-600 font-semibold">Đã điểm danh</div>
                        <div class="text-2xl font-bold text-green-700"><?php echo e($totalChecked ?? 0); ?></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-xs text-slate-600 font-semibold">Số buổi</div>
                        <div class="text-2xl font-bold text-slate-700"><?php echo e(count($sessions)); ?></div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <div class="text-xs text-purple-600 font-semibold">Loại</div>
                        <div class="text-sm font-bold text-purple-700">
                            <?php echo e($attendanceType == 1 ? 'Đi học' : 'Đi lễ'); ?>

                        </div>
                    </div>
                </div>

                
                <div class="hidden lg:block">
                    <div wire:loading.delay
                        wire:target="changeClass,changeType"
                        class="absolute inset-0 bg-white/80 backdrop-blur-sm z-40 flex items-center justify-center rounded-lg">
                        <div class="bg-white rounded-xl shadow-xl p-6 flex flex-col items-center gap-3 border border-blue-200">
                            <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-slate-700 font-semibold">Đang tải dữ liệu điểm danh...</p>
                            <p class="text-xs text-slate-500">Vui lòng đợi</p>
                        </div>
                    </div>

                    <div class="border border-blue-200 rounded-lg overflow-x-auto">
                        <table class="w-full min-w-max" role="table" aria-label="Bảng điểm danh">
                            <caption class="sr-only">
                                Bảng điểm danh <?php echo e($attendanceType == 1 ? 'đi học' : 'đi lễ'); ?>

                                cho <?php echo e(count($students)); ?> học sinh trong <?php echo e(count($sessions)); ?> buổi
                            </caption>
                            <thead class="bg-blue-50 border-b border-blue-200">
                                <tr role="row">
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-900 sticky left-0 bg-blue-50 z-10 border-r border-blue-200">#</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-900 sticky left-12 bg-blue-50 z-10 border-r-2 border-blue-300">Họ và tên</th>
                                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th scope="col" class="px-3 py-3 text-center text-xs font-semibold text-slate-900 border-r border-blue-100 min-w-[120px]">
                                        <div class="flex flex-col gap-1">
                                            <div class="<?php echo e($session['locked'] ? 'text-slate-400' : ''); ?> flex items-center justify-center gap-1">
                                                <span><?php echo e($session['dayName']); ?></span>
                                                <?php if($session['locked']): ?>
                                                <svg class="inline h-3 w-3 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[10px] <?php echo e($session['locked'] ? 'text-slate-400' : 'text-slate-600'); ?>">
                                                <?php echo e($session['fullDate']); ?>

                                            </div>
                                            <?php if(!$session['locked']): ?>
                                            <button wire:click="markAllPresent('<?php echo e($session['id']); ?>')"
                                                class="text-[9px] text-green-600 hover:text-green-700 hover:underline disabled:opacity-50 disabled:cursor-not-allowed"
                                                aria-label="Đánh dấu tất cả học sinh có mặt ngày <?php echo e($session['fullDate']); ?>"
                                                wire:loading.attr="disabled"
                                                wire:target="markAllPresent">
                                                <span wire:loading.remove wire:target="markAllPresent">✓ Tất cả</span>
                                                <span wire:loading wire:target="markAllPresent">⏳</span>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-blue-100 hover:bg-blue-50 transition-colors" role="row">
                                    <td class="px-4 py-3 text-sm text-slate-600 sticky left-0 bg-white z-10 border-r border-blue-100" role="cell">
                                        <?php echo e($index + 1); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm sticky left-12 bg-white z-10 border-r-2 border-blue-200" role="cell">
                                        <div class="text-slate-600"><?php echo e($student->holy_name); ?></div>
                                        <div class="font-medium text-slate-900"><?php echo e($student->last_name); ?> <?php echo e($student->name); ?></div>
                                    </td>
                                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    $status = $this->getAttendanceStatus($student->id, $session['dateStr']);
                                    ?>
                                    <td class="px-3 py-3 text-center border-r border-blue-100" role="cell">
                                        <?php if($session['locked']): ?>
                                        <div class="flex items-center justify-center h-8">
                                            <?php if($status == 1): ?>
                                            <span class="text-green-700 font-medium" aria-label="Có mặt">✓</span>
                                            <?php elseif($status == 2): ?>
                                            <span class="text-yellow-700 font-medium" aria-label="Vắng có phép">P</span>
                                            <?php elseif($status == 3): ?>
                                            <span class="text-red-700 font-medium" aria-label="Vắng không phép">✕</span>
                                            <?php else: ?>
                                            <span class="text-xs text-slate-400" aria-label="Chưa có dữ liệu">-</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="flex gap-1 justify-center" role="group" aria-label="Chọn trạng thái điểm danh">
                                            <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 1 ? 'null' : 1); ?>)"
                                                class="px-2 py-1 rounded text-xs font-medium transition-all
                                                       <?php echo e($status == 1 ? 'bg-green-500 text-white shadow-md scale-105' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'); ?>"
                                                aria-label="Đánh dấu <?php echo e($student->holy_name); ?> <?php echo e($student->name); ?> có mặt"
                                                aria-pressed="<?php echo e($status == 1 ? 'true' : 'false'); ?>">
                                                ✓
                                            </button>
                                            <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 2 ? 'null' : 2); ?>)"
                                                class="px-2 py-1 rounded text-xs font-medium transition-all
                                                       <?php echo e($status == 2 ? 'bg-yellow-400 text-slate-900 shadow-md scale-105' : 'bg-amber-100 text-amber-800 border border-yellow-200 hover:bg-yellow-100'); ?>"
                                                aria-label="Đánh dấu <?php echo e($student->holy_name); ?> <?php echo e($student->name); ?> vắng có phép"
                                                aria-pressed="<?php echo e($status == 2 ? 'true' : 'false'); ?>">
                                                P
                                            </button>
                                            <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 3 ? 'null' : 3); ?>)"
                                                class="px-2 py-1 rounded text-xs font-medium transition-all
                                                       <?php echo e($status == 3 ? 'bg-red-500 text-white shadow-md scale-105' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100'); ?>"
                                                aria-label="Đánh dấu <?php echo e($student->holy_name); ?> <?php echo e($student->name); ?> vắng không phép"
                                                aria-pressed="<?php echo e($status == 3 ? 'true' : 'false'); ?>">
                                                ✕
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                
                                <tr>
                                    <td colspan="100" class="py-12 text-center" role="cell">
                                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <h3 class="text-lg font-semibold text-slate-900 mb-2">
                                            Không tìm thấy học sinh
                                        </h3>
                                        <p class="text-slate-600 mb-4">
                                            <?php if($searchTerm): ?>
                                            Không có học sinh nào khớp với "<?php echo e($searchTerm); ?>"
                                            <?php else: ?>
                                            Lớp này chưa có học sinh
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php endif; ?>

                                
                                <?php if(count($students) > 0): ?>
                                <tr class="bg-blue-50 font-semibold border-t-2 border-blue-300" role="row">
                                    <td colspan="2" class="px-4 py-3 text-sm text-slate-900 sticky left-0 bg-blue-50 z-10" role="cell">
                                        Thống kê
                                    </td>
                                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    $stats = $this->getDateStats($session['dateStr']);
                                    ?>
                                    <td class="px-3 py-3 text-center border-r border-blue-200" role="cell">
                                        <div class="flex flex-col gap-1 text-xs">
                                            <div class="text-green-600">✓ <?php echo e($stats['present']); ?></div>
                                            <div class="text-yellow-600">P <?php echo e($stats['absentPermitted']); ?></div>
                                            <div class="text-red-600">✕ <?php echo e($stats['absentNotPermitted']); ?></div>
                                        </div>
                                    </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="lg:hidden space-y-4">
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-900">
                            Chọn ngày <?php echo e($attendanceType == 1 ? 'đi học' : 'đi lễ'); ?>

                        </label>
                        <select wire:model="selectedDate"
                            class="w-full px-4 py-3 border border-blue-200 rounded-lg bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($session['dateStr']); ?>">
                                <?php echo e($session['dayName']); ?> - <?php echo e($session['fullDate']); ?> <?php echo e($session['locked'] ? '🔒' : ''); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <?php
                    $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
                    $locked = $currentSession['locked'] ?? false;
                    $stats = $this->getDateStats($selectedDate);
                    ?>

                    <?php if(!$locked): ?>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="border-0 shadow-sm bg-green-50 p-3 rounded-lg">
                            <div class="text-xs text-green-700 font-semibold mb-1">Có mặt</div>
                            <div class="text-xl font-bold text-green-600"><?php echo e($stats['present']); ?></div>
                        </div>
                        <div class="border-0 shadow-sm bg-yellow-50 p-3 rounded-lg">
                            <div class="text-xs text-yellow-700 font-semibold mb-1">Vắng CP</div>
                            <div class="text-xl font-bold text-yellow-600"><?php echo e($stats['absentPermitted']); ?></div>
                        </div>
                        <div class="border-0 shadow-sm bg-red-50 p-3 rounded-lg">
                            <div class="text-xs text-red-700 font-semibold mb-1">Vắng KP</div>
                            <div class="text-xl font-bold text-red-600"><?php echo e($stats['absentNotPermitted']); ?></div>
                        </div>
                    </div>

                    <button wire:click="markAllPresent('<?php echo e($selectedDate); ?>')"
                        class="w-full py-2 px-4 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center gap-2 mb-4">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Có mặt tất cả
                    </button>
                    <?php else: ?>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="border-0 shadow-sm bg-green-50 p-3 rounded-lg">
                            <div class="text-xs text-green-700 font-semibold mb-1">Có mặt</div>
                            <div class="text-xl font-bold text-green-600"><?php echo e($stats['present']); ?></div>
                        </div>
                        <div class="border-0 shadow-sm bg-yellow-50 p-3 rounded-lg">
                            <div class="text-xs text-yellow-700 font-semibold mb-1">Vắng CP</div>
                            <div class="text-xl font-bold text-yellow-600"><?php echo e($stats['absentPermitted']); ?></div>
                        </div>
                        <div class="border-0 shadow-sm bg-red-50 p-3 rounded-lg">
                            <div class="text-xs text-red-700 font-semibold mb-1">Vắng KP</div>
                            <div class="text-xl font-bold text-red-600"><?php echo e($stats['absentNotPermitted']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                    $status = $this->getAttendanceStatus($student->id, $selectedDate);
                    ?>
                    <div class="border border-blue-200 shadow-sm rounded-lg">
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="text-xs text-slate-500">#<?php echo e($index + 1); ?></div>
                                    <div class="font-semibold text-slate-900 text-lg"><?php echo e($student->saint_name); ?></div>
                                    <div class="text-sm text-slate-600"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></div>
                                </div>
                            </div>

                            <?php if($locked): ?>
                            
                            <div class="flex items-center justify-center gap-4 py-4">
                                <?php if($status == \App\Models\AttendanceRecord::STATUS_PRESENT): ?>
                                <div class="flex flex-col items-center">
                                    <span class="inline-block w-7 h-7 rounded bg-green-500"></span>
                                    <span class="text-xs text-green-700 mt-1">Có mặt</span>
                                </div>
                                <?php elseif($status == \App\Models\AttendanceRecord::STATUS_ABSENT_EXCUSED): ?>
                                <div class="flex flex-col items-center">
                                    <span class="inline-block w-7 h-7 rounded bg-yellow-400"></span>
                                    <span class="text-xs text-yellow-700 mt-1">Vắng CP</span>
                                </div>
                                <?php elseif($status == \App\Models\AttendanceRecord::STATUS_ABSENT_UNEXCUSED): ?>
                                <div class="flex flex-col items-center">
                                    <span class="inline-block w-7 h-7 rounded bg-red-500"></span>
                                    <span class="text-xs text-red-700 mt-1">Vắng KP</span>
                                </div>
                                <?php else: ?>
                                <div class="text-sm text-slate-400">Chưa có dữ liệu</div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="grid grid-cols-3 gap-2">
                                <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 1 ? 'null' : 1); ?>)"
                                    class="py-3 rounded-lg text-sm font-medium transition-all
                                                           <?php echo e($status == 1 ? 'bg-green-500 text-white shadow-md' : 'bg-green-50 text-green-700 border border-green-200'); ?>">
                                    <div class="text-lg mb-1">✓</div>
                                    <div class="text-xs">Có mặt</div>
                                </button>
                                <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 2 ? 'null' : 2); ?>)"
                                    class="py-3 rounded-lg text-sm font-medium transition-all
                                                           <?php echo e($status == 2 ? 'bg-yellow-400 text-slate-900 shadow-md' : 'bg-yellow-50 text-yellow-700 border border-yellow-200'); ?>">
                                    <div class="text-lg mb-1">P</div>
                                    <div class="text-xs">Vắng CP</div>
                                </button>
                                <button wire:click="setAttendance(<?php echo e($student->id); ?>, '<?php echo e($session['id']); ?>', <?php echo e($status == 3 ? 'null' : 3); ?>)"
                                    class="py-3 rounded-lg text-sm font-medium transition-all
                                                           <?php echo e($status == 3 ? 'bg-red-500 text-white shadow-md' : 'bg-red-50 text-red-700 border border-red-200'); ?>">
                                    <div class="text-lg mb-1">✕</div>
                                    <div class="text-xs">Vắng KP</div>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="mt-4 flex flex-wrap items-center gap-4 md:gap-6 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-green-500" aria-hidden="true"></span>
                        <span>Có mặt (✓)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-yellow-400" aria-hidden="true"></span>
                        <span>Vắng có phép (P)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded bg-red-500" aria-hidden="true"></span>
                        <span>Vắng không phép (✕)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Ngày đã đóng điểm danh (🔒)</span>
                    </div>
                </div>

                
                <div class="flex flex-col sm:flex-row gap-3 mt-6 sm:justify-end">
                    <a href="<?php echo e(route('attendance.show')); ?>"
                        class="px-4 py-2 border border-blue-200 hover:bg-blue-50 bg-white rounded-lg text-center">
                        Hủy
                    </a>
                    <button wire:click="saveAttendance"
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500  hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg shadow-md transition-all
                            disabled:opacity-50 disabled:cursor-not-allowed 
                            flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="saveAttendance">

                        <span wire:loading.remove wire:target="saveAttendance">
                            Lưu điểm danh
                        </span>
                        <span wire:loading wire:target="saveAttendance" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Đang lưu...
                        </span>
                    </button>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // Keyboard shortcuts
    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            window.livewire.find('<?php echo e($_instance->id); ?>').call('saveAttendance');
        }
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/attendance-manager.blade.php ENDPATH**/ ?>