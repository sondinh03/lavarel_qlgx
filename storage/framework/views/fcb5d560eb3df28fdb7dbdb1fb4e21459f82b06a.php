<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6" x-data="{ 
    showModal: false,
    isEdit: false,
    formData: {
        id: null,
        symbol: '',
        name: '',
        schoolyear: '',
        block: '',
        mainTeacherId: '',
        assistantTeacherIds: [],
        note: ''
    },
    resetForm() {
        this.formData = {
            id: null,
            symbol: '',
            name: '',
            schoolyear: '',
            block: '',
            mainTeacherId: '',
            assistantTeacherIds: [],
            note: ''
        };
    },
    openCreateModal() {
        this.resetForm();
        this.isEdit = false;
        this.showModal = true;
    },
    openEditModal(lop) {
        this.formData = {
            id: lop.id,
            symbol: lop.symbol,
            name: lop.name,
            schoolyear: lop.schoolyear_id,
            block: lop.block_id,
            mainTeacherId: lop.main_teacher_id || '',
            assistantTeacherIds: lop.assistant_teacher_ids || [],
            note: lop.note || ''
        };
        this.isEdit = true;
        this.showModal = true;
    },
    closeModal() {
        this.showModal = false;
        setTimeout(() => this.resetForm(), 300);
    }
}">
    <a href="#lop-list-main" class="sr-only focus:not-sr-only">
        Bỏ qua tới nội dung
    </a>

    <main id="lop-list-main" class="mx-auto max-w-7xl space-y-5">

        
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Trang chủ', 'url' => route('home')],
            [
                'label' => 'Quản lý lớp học',
                'icon'  => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>',
            ],
        ]]]); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Trang chủ', 'url' => route('home')],
            [
                'label' => 'Quản lý lớp học',
                'icon'  => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>',
            ],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        
        <div role="status" aria-live="polite" aria-atomic="true">
            <?php $__currentLoopData = ['message' => 'success', 'error' => 'error', 'warning' => 'warning']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(session()->has($key)): ?>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.toast-notification','data' => ['type' => $type,'duration' => 3500]]); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($type),'duration' => 3500]); ?>
                <?php echo e(session($key)); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            
            <div class="flex items-start justify-between gap-4">
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white w-full">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900">
                                    Quản lý lớp học
                                </h1>
                                <p class="text-sm text-slate-600 mt-1">
                                    <?php if($selectedNamHoc): ?>
                                    Quản lý <?php echo e($lops?->total() ?? 0); ?> lớp trong năm học
                                    <span class="font-semibold text-slate-900">
                                        <?php echo e($namHocs[$selectedNamHoc] ?? ''); ?>

                                    </span>
                                    <?php else: ?>
                                    Chọn năm học để xem danh sách lớp
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if($parish_id && $selectedNamHoc): ?>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5
                                      bg-gradient-to-r from-primary-500 to-primary-600
                                      hover:from-primary-600 hover:to-primary-700
                                      text-white rounded-xl font-semibold
                                      active:scale-[0.98] transition-all shadow-sm"
                            aria-label="Thêm lớp học mới">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Thêm lớp học</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="p-6 bg-slate-50">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('class-filter-selector', [
                'parish_id' => $parish_id,
                'selectedNamHoc' => $selectedNamHoc,
                'selectedKhoi' => $selectedKhoi,
                'showLop' => false,
                ])->html();
} elseif ($_instance->childHasBeenRendered('l2453251112-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l2453251112-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2453251112-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2453251112-0');
} else {
    $response = \Livewire\Livewire::mount('class-filter-selector', [
                'parish_id' => $parish_id,
                'selectedNamHoc' => $selectedNamHoc,
                'selectedKhoi' => $selectedKhoi,
                'showLop' => false,
                ]);
    $html = $response->html();
    $_instance->logRenderedChild('l2453251112-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.loading.overlay','data' => ['wireTarget' => 'selectedNamHoc,selectedKhoi,resetFilters','mode' => 'inline']]); ?>
<?php $component->withName('loading.overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire-target' => 'selectedNamHoc,selectedKhoi,resetFilters','mode' => 'inline']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
        </section>

        
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.loading.overlay','data' => ['wireTarget' => 'selectedNamHoc,selectedKhoi,resetFilters','mode' => 'centered']]); ?>
<?php $component->withName('loading.overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire-target' => 'selectedNamHoc,selectedKhoi,resetFilters','mode' => 'centered']); ?>
                Đang tải danh sách lớp...
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0"
                    aria-label="Danh sách lớp học">

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
<?php $component->withAttributes([]); ?>Mã lớp <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Tên lớp <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Khối <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Sĩ số <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Giáo lý viên <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes([]); ?>Thao tác <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $lops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $lop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <?php echo e($lops->firstItem() + $index); ?>

                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-semibold text-slate-900">
                                    <?php echo e($lop->symbol); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-slate-900">
                                    <?php echo e($lop->name); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <?php echo e($lop->block->name ?? 'N/A'); ?>

                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 font-semibold">
                                <?php echo e($lop->students_count ?? 0); ?>

                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <?php echo e($lop->mainTeacher->name ?? 'Chưa phân công'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="openEditModal(<?php echo e(json_encode($lop)); ?>)"
                                        class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                        title="Chỉnh sửa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <a href="<?php echo e(route('lop.show', $lop->id)); ?>"
                                        class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                        title="Xem chi tiết">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.empty-state','data' => ['icon' => 'class','colspan' => 7,'title' => $selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học','description' => !$selectedNamHoc
                                    ? 'Vui lòng chọn năm học để xem danh sách lớp'
                                    : ($selectedKhoi
                                        ? 'Không có lớp nào trong khối này'
                                        : 'Chưa có lớp học nào trong năm học này')]]); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['icon' => 'class','colspan' => 7,'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học'),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$selectedNamHoc
                                    ? 'Vui lòng chọn năm học để xem danh sách lớp'
                                    : ($selectedKhoi
                                        ? 'Không có lớp nào trong khối này'
                                        : 'Chưa có lớp học nào trong năm học này'))]); ?>
                            <?php if($isAdmin && $selectedNamHoc): ?>
                            <button @click="openCreateModal()"
                                class="inline-flex items-center gap-2 px-6 py-2.5
                                              bg-gradient-to-r from-primary-500 to-primary-600
                                              hover:from-primary-600 hover:to-primary-700
                                              text-white rounded-xl font-semibold
                                              active:scale-[0.98] transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tạo lớp học mới
                            </button>
                            <?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($lops->hasPages()): ?>
            <div class="border-t border-slate-200">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.pagination','data' => ['paginator' => $lops,'perPageOptions' => [10, 15, 25, 50]]]); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lops),'per-page-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([10, 15, 25, 50])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
            <?php endif; ?>
        </section>

    </main>

    
    <div x-show="showModal"
        x-cloak
        @keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true">

        
        <div x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
            @click="closeModal()">
        </div>

        
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative  w-[95vw] sm:w-[90vw] max-w-lg bg-white rounded-2xl shadow-2xl transform transition-all max-h-[90vh] flex flex-col">

                
                <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h2 id="modal-title" class="text-xl font-bold text-slate-900">
                            <span x-text="isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới'"></span>
                        </h2>
                    </div>
                    <button @click="closeModal()"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                
                <form wire:submit.prevent="save" class="overflow-y-auto">

                    
                    <div class="p-6 space-y-4">
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
                                    x-model="formData.symbol"
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
                                    x-model="formData.name"
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
                                <select x-model="formData.schoolyear"
                                    wire:model="form.schoolyear"
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
                                    <?php $__currentLoopData = $namHocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                <select x-model="formData.block"
                                    wire:model="form.block"
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
                                    <?php $__currentLoopData = $khois; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

                    
                    <div class="px-6 pb-6 pt-2 border-t border-slate-200">
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
                                <select x-model="formData.mainTeacherId"
                                    wire:model.defer="form.mainTeacherId"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               transition-all">
                                    <option value="">-- Chọn giáo lý viên --</option>
                                    
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Giáo lý viên phụ trách
                                </label>
                                <select multiple size="5"
                                    x-model="formData.assistantTeacherIds"
                                    wire:model.defer="form.assistantTeacherIds"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                               focus:outline-none focus:ring-2 focus:ring-primary-500
                                               transition-all">
                                    
                                </select>
                                <p class="mt-1 text-xs text-slate-500">
                                    Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều
                                </p>
                            </div>
                        </div>
                    </div>

                    
                    <div class="px-6 pb-6 pt-2 border-t border-slate-200">
                        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4" />
                            </svg>
                            Ghi chú
                        </h3>

                        <textarea rows="4"
                            x-model="formData.note"
                            wire:model.defer="form.note"
                            placeholder="Nhập ghi chú (không bắt buộc)..."
                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                         focus:outline-none focus:ring-2 focus:ring-primary-500
                                         transition-all resize-none"></textarea>
                    </div>
                </form>

                
                <div class="shrink-0 sticky bottom-0 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button @click="closeModal()"
                            type="button"
                            class="px-6 py-2.5 bg-white border border-slate-300 rounded-xl
                                  text-slate-700 font-semibold hover:bg-slate-100
                                  active:scale-95 transition-all text-center">
                            Hủy
                        </button>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl
                                       bg-primary-600 text-white font-semibold
                                       hover:bg-primary-700
                                       active:scale-[0.98] transition-all
                                       disabled:opacity-60">
                            <span x-text="isEdit ? 'Cập nhật lớp học' : 'Tạo lớp học'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<?php $__env->stopPush(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/livewire/lop/lop-list-with-modal.blade.php ENDPATH**/ ?>