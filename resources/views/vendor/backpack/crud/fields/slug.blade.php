<!-- slug input -->
@php
    $slug = old($field['name']) ? old($field['name']) : (isset($field['value']) ? data_get($field['value'], 'keyword') : (isset($field['default']) ? $field['default'] : '' ))
@endphp
@include('crud::fields.inc.wrapper_start')

<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text cursor-pointer"><i class="la la-cog"></i></span>
    </div>
    <input
        type="text"
        name="{{ $field['name'] }}"
        value="{{ $slug }}"
        data-init-function="bpFieldInitSlugElement"
        @include('crud::fields.inc.attributes')
    >
    <div class="input-group-append">
        <a href="{{ url($slug . config('settings.url_prefix')) }}" target="_blank" class="input-group-text"><i
                class="fas fa-external-link-alt"></i></a>
    </div>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif

@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- no styles -->
    @endpush

    {{-- FIELD EXTRA JS --}}
    {{-- push things in the after_scripts section --}}

    @push('crud_fields_scripts')
        <script src="{{ asset('packages/slugify/@1.6.0/slugify.js') }}"></script>
        <script>
            function bpFieldInitSlugElement(element) {
                // var pathName = window.location.pathname.split('/')
                var inputName = $("input[name='{{ data_get($field, 'source', 'title')}}']")
                var inputSlug = $("input[name='{{ $field['name'] }}']")
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
    @endpush
@endif
