<?php $__env->startSection('after_scripts'); ?>
    <script type="text/javascript" src="<?php echo e(asset('/js/ckfinder/ckfinder.js')); ?>"></script>
    <script>CKFinder.config( { connectorPath: '<?php echo e(route('ckfinder_connector')); ?>' } );</script>

    <script>
        CKFinder.widget('ckfinder-widget', {
            width: '100%',
            height: 'calc(100vh - 150px)'
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div id="ckfinder-widget"></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backpack::layouts.top_left', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/base/ckfinder.blade.php ENDPATH**/ ?>