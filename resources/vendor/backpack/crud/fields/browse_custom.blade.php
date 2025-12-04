@php
    $value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';
    // https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types
    $image_mimes = ['apng', 'bmp', 'gif', 'ico', 'cur', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'svg', 'webp'];
    if(!empty($value)) {
        $ext = pathinfo($value, PATHINFO_EXTENSION);
        if(in_array($ext, $image_mimes)) {
            $preview = '/'.$value;
        } else {
            //$preview = 'https://via.placeholder.com/38?text='.$ext;
            $preview = '/images/no-image.png';
        }
    } else {
        //$preview = 'https://via.placeholder.com/38';
        $preview = '/images/no-image.png';
    }
@endphp

@include('crud::fields.inc.wrapper_start')

<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<div class="controls">
    <div class="input-group">
        <div class="input-group-prepend">
            <div class="d-flex align-items-center">
                <img width="38" height="38" style="object-fit: cover" src="{{ $preview }}" alt=""
                     id="preview-{{ $field['name'] }}"/>
            </div>
        </div>
        <input
            type="text"
            name="{{ $field['name'] }}"
            value="{{ $value }}"
            id="input-{{ $field['name'] }}"
            data-ckfinder-mimes="{{ serialize($field['mimes']) }}"
            @include('crud::fields.inc.attributes')

            @if(!isset($field['readonly']) || $field['readonly']) readonly @endif
        >

        <span class="input-group-append">
            <button type="button" onclick="selectFileWithCKFinder('{{ $field['name'] }}')"
                    class="btn btn-outline-success" data-toggle="tooltip"
                    title="@lang('backpack::crud.browse_uploads')"><i class="la la-cloud-upload"></i></button>
            <button type="button" data-inputid="{{ $field['name'] }}-filemanager"
                    class="btn btn-outline-danger" data-toggle="tooltip" onclick="clearImage('{{ $field['name'] }}')"
                    title="@lang('backpack::crud.clear')"><i class="la la-eraser"></i></button>
        </span>
    </div>
</div>

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
            <script type="text/javascript" src="{{ asset('/js/ckfinder/ckfinder.js') }}"></script>
            <script>CKFinder.config({connectorPath: '{{ route('ckfinder_connector') }}'});</script>
            <script>
                function selectFileWithCKFinder(elementId) {
                    CKFinder.modal({
                        chooseFiles: true,
                        width: 800,
                        height: 600,
                        onInit: function (finder) {
                            const output = document.getElementById('input-' + elementId)
                            const preview = document.getElementById('preview-' + elementId)

                            finder.on('files:choose', function (evt) {
                                const file = evt.data.files.first();
                                output.value = file.getUrl()
                                preview.src = window.location.origin + '/' + file.getUrl()
                            })

                            finder.on('file:choose:resizedImage', function (evt) {
                                const output = document.getElementById('input-' + elementId)
                                output.value = evt.data.resizedUrl
                                preview.src = window.location.origin + '/' + evt.data.resizedUrl
                            })
                        }
                    })
                }

                function clearImage(elementId) {
                    const output = document.getElementById('input-' + elementId)
                    const preview = document.getElementById('preview-' + elementId)
                    preview.src = 'https://via.placeholder.com/38'
                    output.value = ''
                }
            </script>
        @endonce
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
