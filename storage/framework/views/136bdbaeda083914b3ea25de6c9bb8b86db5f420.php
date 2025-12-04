<!-- bootstrap daterange picker input -->

<?php
    // if the column has been cast to Carbon or Date (using attribute casting)
    // get the value as a date string
    if (! function_exists('formatDate')) {
        function formatDate($entry, $dateFieldName)
        {
            $formattedDate = null;
            if (isset($entry) && ! empty($entry->{$dateFieldName})) {
                $dateField = $entry->{$dateFieldName};
                if ($dateField instanceof \Carbon\CarbonInterface) {
                    $formattedDate = $dateField->format('Y-m-d H:i:s');
                } else {
                    $formattedDate = date('Y-m-d H:i:s', strtotime($entry->{$dateFieldName}));
                }
            }

            return $formattedDate;
        }
    }
    
    $field['name'] = explode(',', $field['name']);
    if (isset($entry)) {
        //$field['name'] = explode(',', $field['name']);
        $start_value = formatDate($entry, $field['name'][0]);
        $end_value = formatDate($entry, $field['name'][1]);
    }

    $start_default = $field['default'][0] ?? date('Y-m-d H:i:s');
    $end_default = $field['default'][1] ?? date('Y-m-d H:i:s');

    // make sure the datepicker configuration has at least these defaults
    $field['date_range_options'] = array_replace_recursive([
        'autoApply' => true,
        'startDate' => $start_default,
        'endDate' => $end_default,
        'locale' => [
            'firstDay' => 0,
            'format' => config('backpack.base.default_date_format'),
            'applyLabel'=> trans('backpack::crud.apply'),
            'cancelLabel'=> trans('backpack::crud.cancel'),
        ],
    ], $field['date_range_options'] ?? []);
?>

<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <input class="datepicker-range-start" type="hidden" name="<?php echo e($field['name'][0]); ?>" value="<?php echo e(old(square_brackets_to_dots($field['name'][0])) ?? $start_value ?? $start_default ?? ''); ?>">
    <input class="datepicker-range-end" type="hidden" name="<?php echo e($field['name'][1]); ?>" value="<?php echo e(old(square_brackets_to_dots($field['name'][1])) ?? $end_value ?? $end_default ?? ''); ?>">
    <label><?php echo $field['label']; ?></label>
    <div class="input-group date">
        <input
            data-bs-daterangepicker="<?php echo e(json_encode($field['date_range_options'] ?? [])); ?>"
            data-init-function="bpFieldInitDateRangeElement"
            type="text"
            <?php echo $__env->make('crud::fields.inc.attributes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            >
        	<div class="input-group-append">
	            <span class="input-group-text">
                <span class="la la-calendar"></span>
            </span>
        </div>
    </div>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>




<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>

    
    <?php $__env->startPush('crud_fields_styles'); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('packages/bootstrap-daterangepicker/daterangepicker.css')); ?>" />
    <?php $__env->stopPush(); ?>

    
    <?php $__env->startPush('crud_fields_scripts'); ?>
    <script type="text/javascript" src="<?php echo e(asset('packages/moment/min/moment-with-locales.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('packages/bootstrap-daterangepicker/daterangepicker.js')); ?>"></script>
    <script>
        function bpFieldInitDateRangeElement(element) {

                moment.locale('<?php echo e(app()->getLocale()); ?>');

                var $visibleInput = element;
                var $startInput = $visibleInput.closest('.input-group').parent().find('.datepicker-range-start');
                var $endInput = $visibleInput.closest('.input-group').parent().find('.datepicker-range-end');

                var $configuration = $visibleInput.data('bs-daterangepicker');
                // set the startDate and endDate to the defaults
                $configuration.startDate = moment($configuration.startDate);
                $configuration.endDate = moment($configuration.endDate);

                // if the hidden inputs have values
                // then startDate and endDate should be the values there
                if ($startInput.val() != '') {
                    $configuration.startDate = moment($startInput.val());
                }
                if ($endInput.val() != '') {
                    $configuration.endDate = moment($endInput.val());
                }

                $visibleInput.daterangepicker($configuration);

                var $picker = $visibleInput.data('daterangepicker');

                $visibleInput.on('keydown', function(e){
                    e.preventDefault();
                    return false;
                });

                $visibleInput.on('apply.daterangepicker hide.daterangepicker', function(e, picker){
                    $startInput.val( picker.startDate.format('YYYY-MM-DD HH:mm:ss') );
                    $endInput.val( picker.endDate.format('YYYY-MM-DD HH:mm:ss') );
                });
        }
    </script>
    <?php $__env->stopPush(); ?>

<?php endif; ?>

<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/crud/fields/date_range_array.blade.php ENDPATH**/ ?>