

<?php $__env->startSection('content'); ?>
<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('student.student-list', ['id' => $lopId])->html();
} elseif ($_instance->childHasBeenRendered('GG5aTab')) {
    $componentId = $_instance->getRenderedChildComponentId('GG5aTab');
    $componentTag = $_instance->getRenderedChildComponentTagName('GG5aTab');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('GG5aTab');
} else {
    $response = \Livewire\Livewire::mount('student.student-list', ['id' => $lopId]);
    $html = $response->html();
    $_instance->logRenderedChild('GG5aTab', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/lop.blade.php ENDPATH**/ ?>