<!-- textarea -->
@include('crud::fields.inc.wrapper_start')
<div class="js-wrapper-input">
    <label>{!! $field['label'] !!}
        <span class="text-info">
            (<small
                class="js-length">{{strlen(old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '')}}</small>)
        </span>
    </label>
    @include('crud::fields.inc.translatable_icon')

    <textarea
        name="{{ $field['name'] }}"
        @include('crud::fields.inc.attributes')

    	>{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>

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

    @push('crud_fields_scripts')
        @once
            <script>
                document.querySelectorAll('.js-wrapper-input').forEach(wrapper => {
                    const input = wrapper.querySelector('textarea')
                    const show = wrapper.querySelector('.js-length')

                    input.addEventListener('keypress', () => {
                        show.innerText = input.value.length + 1
                    })
                })
            </script>
        @endonce
    @endpush
@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

