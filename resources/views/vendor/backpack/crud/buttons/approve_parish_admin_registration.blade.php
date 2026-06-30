@if ($entry->isPending())
<form method="POST" action="{{ url($crud->route.'/'.$entry->getKey().'/approve') }}" class="d-inline"
    onsubmit="return confirm('Duyệt yêu cầu đăng ký quản trị xứ này?');">
    @csrf
    <button type="submit" class="btn btn-sm btn-success">
        <i class="la la-check"></i> Duyệt
    </button>
</form>
@endif
