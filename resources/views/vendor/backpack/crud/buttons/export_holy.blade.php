@if ($crud->hasAccess('list'))
    <a href="{{ url($crud->route.'/export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"
       class="btn btn-primary"
       data-style="zoom-in">
        <span class="ladda-label">
            <i class="la la-download"></i> Xuất Excel
        </span>
    </a>
@endif
