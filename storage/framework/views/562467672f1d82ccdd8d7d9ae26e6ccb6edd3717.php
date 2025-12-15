

<?php $__env->startSection('content'); ?>

<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('pages.student.student-list', ['id' => $lopId])->html();
} elseif ($_instance->childHasBeenRendered('ZPm3WoD')) {
    $componentId = $_instance->getRenderedChildComponentId('ZPm3WoD');
    $componentTag = $_instance->getRenderedChildComponentTagName('ZPm3WoD');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('ZPm3WoD');
} else {
    $response = \Livewire\Livewire::mount('pages.student.student-list', ['id' => $lopId]);
    $html = $response->html();
    $_instance->logRenderedChild('ZPm3WoD', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/lop.blade.php ENDPATH**/ ?>