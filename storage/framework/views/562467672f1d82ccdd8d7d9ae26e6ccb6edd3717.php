

<?php $__env->startSection('content'); ?>
<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('lop', ['id' => $lopId])->html();
} elseif ($_instance->childHasBeenRendered('LovjLDf')) {
    $componentId = $_instance->getRenderedChildComponentId('LovjLDf');
    $componentTag = $_instance->getRenderedChildComponentTagName('LovjLDf');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('LovjLDf');
} else {
    $response = \Livewire\Livewire::mount('lop', ['id' => $lopId]);
    $html = $response->html();
    $_instance->logRenderedChild('LovjLDf', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/lop.blade.php ENDPATH**/ ?>