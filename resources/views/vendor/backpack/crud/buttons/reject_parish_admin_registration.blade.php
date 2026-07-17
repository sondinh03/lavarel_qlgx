@if ($entry->isPending())
@php
    $modalId = 'rejectModal'.$entry->getKey();
@endphp
<button type="button" class="btn btn-sm btn-danger"
    onclick="parishAdminRegOpenModal('{{ $modalId }}')">
    <i class="la la-times"></i> Từ chối
</button>

<div class="modal fade js-parish-admin-reg-modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ url($crud->route.'/'.$entry->getKey().'/reject') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối yêu cầu #{{ $entry->reference_code }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason_{{ $entry->getKey() }}">Lý do (tuỳ chọn)</label>
                        <textarea name="rejection_reason" id="rejection_reason_{{ $entry->getKey() }}"
                            class="form-control" rows="3" placeholder="Ghi chú gửi nội bộ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
