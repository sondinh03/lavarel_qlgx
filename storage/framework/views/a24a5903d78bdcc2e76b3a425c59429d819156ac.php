<?php if($crud->hasAccess('revise') && count($entry->revisionHistory)): ?>
    <a href="<?php echo e(url($crud->route.'/'.$entry->getKey().'/revise')); ?>" class="btn btn-sm btn-link"><i class="la la-history"></i> <?php echo e(trans('revise-operation::revise.revisions')); ?></a>
<?php endif; ?>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\vendor\backpack\revise-operation\src/../resources/views/revise_button.blade.php ENDPATH**/ ?>