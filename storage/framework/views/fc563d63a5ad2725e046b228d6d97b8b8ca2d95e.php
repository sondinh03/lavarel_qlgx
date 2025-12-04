<!-- radio -->
<?php
    $optionValue = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';


    // check if attribute is casted, if it is, we get back un-casted values
    if(Arr::get($crud->model->getCasts(), $field['name']) === 'boolean') {
        $optionValue = (int) $optionValue;
    }

    // if the class isn't overwritten, use 'radio'
    if (!isset($field['attributes']['class'])) {
        $field['attributes']['class'] = 'radio';
    }

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitRadioElement';
?>

<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div>
        <label><?php echo $field['label']; ?></label>
        <?php echo $__env->make('crud::fields.inc.translatable_icon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <input type="hidden" value="<?php echo e($optionValue); ?>" name="<?php echo e($field['name']); ?>" />

    <?php if( isset($field['options']) && $field['options'] = (array)$field['options'] ): ?>

        <?php $__currentLoopData = $field['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="form-check <?php echo e(isset($field['inline']) && $field['inline'] ? 'form-check-inline' : ''); ?>">
                <input  type="radio"
                        class="form-check-input"
                        value="<?php echo e($value); ?>"
                        <?php echo $__env->make('crud::fields.inc.attributes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        >
                <label class="<?php echo e(isset($field['inline']) && $field['inline'] ? 'radio-inline' : ''); ?> form-check-label font-weight-normal"><?php echo $label; ?></label>
            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php endif; ?>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>

<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>

    
    <?php $__env->startPush('crud_fields_scripts'); ?>
    <script>
        function bpFieldInitRadioElement(element) {
            var hiddenInput = element.find('input[type=hidden]');
            var value = hiddenInput.val();
            var id = 'radio_'+Math.floor(Math.random() * 1000000);

            // set unique IDs so that labels are correlated with inputs
            element.find('.form-check input[type=radio]').each(function(index, item) {
                $(this).attr('id', id+index);
                $(this).siblings('label').attr('for', id+index);
            });

            // when one radio input is selected
            element.find('input[type=radio]').change(function(event) {
                // the value gets updated in the hidden input and the 'change' event is fired
                hiddenInput.val($(this).val()).change();
                // all other radios get unchecked
                element.find('input[type=radio]').not(this).prop('checked', false);
            });

            // select the right radios
            element.find('input[type=radio][value="'+value+'"]').prop('checked', true);
        }
    </script>
    <?php $__env->stopPush(); ?>

<?php endif; ?>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/crud/fields/radio.blade.php ENDPATH**/ ?>