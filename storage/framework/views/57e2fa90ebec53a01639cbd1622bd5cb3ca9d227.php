<!-- slug input -->
<?php
    $slug = old($field['name']) ? old($field['name']) : (isset($field['value']) ? data_get($field['value'], 'keyword') : (isset($field['default']) ? $field['default'] : '' ))
?>
<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<label><?php echo $field['label']; ?></label>
<?php echo $__env->make('crud::fields.inc.translatable_icon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text cursor-pointer"><i class="la la-cog"></i></span>
    </div>
    <input
        type="text"
        name="<?php echo e($field['name']); ?>"
        value="<?php echo e($slug); ?>"
        data-init-function="bpFieldInitSlugElement"
        <?php echo $__env->make('crud::fields.inc.attributes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    >
    <div class="input-group-append">
        <a href="<?php echo e(url($slug . config('settings.url_prefix'))); ?>" target="_blank" class="input-group-text"><i
                class="fas fa-external-link-alt"></i></a>
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
        <!-- no styles -->
    <?php $__env->stopPush(); ?>

    
    

    <?php $__env->startPush('crud_fields_scripts'); ?>
        <script src="<?php echo e(asset('packages/slugify/@1.6.0/slugify.js')); ?>"></script>
        <script>
            function bpFieldInitSlugElement(element) {
                // var pathName = window.location.pathname.split('/')
                var inputName = $("input[name='<?php echo e(data_get($field, 'source', 'title')); ?>']")
                var inputSlug = $("input[name='<?php echo e($field['name']); ?>']")
                element.siblings('.input-group-prepend').children('.input-group-text').click(function (event) {
                    event.preventDefault();
                    // console.log($(this))
                    const icon = $(this).children('i')
                    icon.removeClass('la-check').addClass('la-spin la-cog')
                    setTimeout(function () {
                        icon.removeClass('la-spin la-cog').addClass('la-check')
                        inputSlug.val(slugify(inputName.val()).toLowerCase());
                    }, 250)
                    // if (pathName.pop() === 'create') {
                    //     inputName.on('keyup', function () {
                    //         inputSlug.value = slugify(inputName.value)
                    //     })
                    // }
                });
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/vendor/backpack/crud/fields/slug.blade.php ENDPATH**/ ?>