<!-- select2 from ajax -->
<?php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
    // by default set ajax query delay to 500ms
    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
?>

<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <select
        name="<?php echo e($field['name']); ?>"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromAjaxElementStudent"
        data-field-is-inline="<?php echo e(var_export($inlineCreate ?? false)); ?>"
        data-column-nullable="<?php echo e(var_export($field['allows_null'])); ?>"
        data-dependencies="<?php echo e(isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([])); ?>"
        data-placeholder="<?php echo e($field['placeholder']); ?>"
        data-minimum-input-length="<?php echo e($field['minimum_input_length']); ?>"
        data-data-source="<?php echo e($field['data_source']); ?>"
        data-method="<?php echo e($field['method'] ?? 'GET'); ?>"
        data-field-attribute="<?php echo e($field['attribute']); ?>"
        data-connected-entity-key-name="<?php echo e($connected_entity_key_name); ?>"
        data-include-all-form-fields="<?php echo e(isset($field['include_all_form_fields']) ? ($field['include_all_form_fields'] ? 'true' : 'false') : 'false'); ?>"
        data-ajax-delay="<?php echo e($field['delay']); ?>"
        data-language="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
        <?php echo $__env->make('crud::fields.inc.attributes', ['default_class' =>  'form-control'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        >

        <?php if($old_value): ?>
            <?php
                if(!is_object($old_value)) {
                    $item = $connected_entity->find($old_value);
                }else{
                    $item = $old_value;
                }

            ?>
            <?php if($item): ?>
            
            <?php if($field['allows_null']): ?>)
            <option value="" selected>
                <?php echo e($field['placeholder']); ?>

            </option>
            <?php endif; ?>

            <option value="<?php echo e($item->getKey()); ?>" selected>
                <?php echo e($item->{$field['attribute']}); ?>

            </option>
            <?php endif; ?>
        <?php endif; ?>
    </select>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>




<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>

    
    <?php $__env->startPush('crud_fields_styles'); ?>
    <!-- include select2 css-->
    <link href="<?php echo e(asset('packages/select2/dist/css/select2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />
    
    <?php if($field['allows_null']): ?>
    <style type="text/css">
        .select2-selection__clear::after {
            content: ' <?php echo e(trans('backpack::crud.clear')); ?>';
        }
    </style>
    <?php endif; ?>
    <?php $__env->stopPush(); ?>

    
    <?php $__env->startPush('crud_fields_scripts'); ?>
    <!-- include select2 js-->
    <script src="<?php echo e(asset('packages/select2/dist/js/select2.full.min.js')); ?>"></script>
    <?php if(app()->getLocale() !== 'en'): ?>
    <script src="<?php echo e(asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js')); ?>"></script>
    <?php endif; ?>
    <?php $__env->stopPush(); ?>

<?php endif; ?>

<!-- include field specific select2 js-->
<?php $__env->startPush('crud_fields_scripts'); ?>
<script>
    function bpFieldInitSelect2FromAjaxElementStudent(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
        var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
        var $ajaxDelay = element.attr('data-ajax-delay');
        var $selectedOptions = typeof element.attr('data-selected-options') === 'string' ? JSON.parse(element.attr('data-selected-options')) : JSON.parse(null);
        var $isFieldInline = element.data('field-is-inline');

        var select2AjaxFetchSelectedEntry = function (element) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: $dataSource,
                    data: {
                        'keys': $selectedOptions
                    },
                    type: $method,
                    success: function (result) {

                        resolve(result);
                    },
                    error: function (result) {
                        reject(result);
                    }
                });
            });
        };

        // do not initialise select2s that have already been initialised
        if ($(element).hasClass("select2-hidden-accessible"))
        {
            return;
        }
        //init the element
        $(element).select2({
            theme: 'bootstrap',
            multiple: false,
            placeholder: $placeholder,
            minimumInputLength: $minimumInputLength,
            allowClear: $allowClear,
            dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body,
            ajax: {
                url: $dataSource,
                type: $method,
                dataType: 'json',
                delay: $ajaxDelay,
                data: function (params) {
                	var giaoxu = $('input[name="giaoxu"]').val();
                    if ($includeAllFormFields) {
                        return {
                        	giaoxu: giaoxu,
                            q: params.term, // search term
                            page: params.page, // pagination
                            form: form.serializeArray() // all other form inputs
                        };
                    } else {
                        return {
                            q: params.term, // search term
                            page: params.page, // pagination
                            giaoxu: giaoxu,
                        };
                    }
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    var result = {
                        results: $.map(data.data, function (item) {
                            textField = $fieldAttribute;
                            return {
                                text: item[textField],
                                id: item[$connectedEntityKeyName]
                            }
                        }),
                        pagination: {
                                more: data.current_page < data.last_page
                        }
                    };

                    return result;
                },
                cache: true
            },
        });

        // if we have selected options here we are on a repeatable field, we need to fetch the options with the keys
        // we have stored from the field and append those options in the select.
        if (typeof $selectedOptions !== typeof undefined &&
            $selectedOptions !== false &&
            $selectedOptions != '' &&
            $selectedOptions != null &&
            $selectedOptions != [])
        {
            var optionsForSelect = [];
            select2AjaxFetchSelectedEntry(element).then(function(result) {
                result.forEach(function(item) {
                    $itemText = item[$fieldAttribute];
                    $itemValue = item[$connectedEntityKeyName];
                    //add current key to be selected later.
                    optionsForSelect.push($itemValue);

                    //create the option in the select
                    $(element).append('<option value="'+$itemValue+'">'+$itemText+'</option>');
                });

                // set the option keys as selected.
                $(element).val(optionsForSelect);
            });
        }

        // if any dependencies have been declared
        // when one of those dependencies changes value
        // reset the select2 value
        for (var i=0; i < $dependencies.length; i++) {
            var $dependency = $dependencies[i];
            //if element does not have a custom-selector attribute we use the name attribute
            if(typeof element.attr('data-custom-selector') == 'undefined') {
                form.find('[name="'+$dependency+'"], [name="'+$dependency+'[]"]').change(function(el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                });
            }else{
                // we get the row number and custom selector from where element is called
                let rowNumber = element.attr('data-row-number');
                let selector = element.attr('data-custom-selector');

                // replace in the custom selector string the corresponding row and dependency name to match
                selector = selector
                    .replaceAll('%DEPENDENCY%', $dependency)
                    .replaceAll('%ROW%', rowNumber);

                $(selector).change(function (el) {
                    $(element.find('option:not([value=""])')).remove();
                    element.val(null).trigger("change");
                });
            }
        }
    }
</script>
<?php $__env->stopPush(); ?>


<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/crud/fields/select_from_student_ajax.blade.php ENDPATH**/ ?>