@if ($entry->isPending())
    @if ($entry->createsNewParish())
        @php
            $suggestedCode = \App\Support\ParishCodeGenerator::suggestUnique((string) $entry->custom_parish_name);
            $modalId = 'approveParishModal'.$entry->getKey();
        @endphp
        <button type="button" class="btn btn-sm btn-success"
            onclick="parishAdminRegOpenModal('{{ $modalId }}')">
            <i class="la la-check"></i> Duyệt
        </button>

        <div class="modal fade js-parish-admin-reg-modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ url($crud->route.'/'.$entry->getKey().'/approve') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Duyệt & tạo giáo xứ mới</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                Yêu cầu <strong>#{{ $entry->reference_code }}</strong> sẽ tạo giáo xứ
                                <strong>{{ $entry->custom_parish_name }}</strong>.
                                Vui lòng nhập mã giáo xứ (bắt buộc).
                            </p>
                            @if ($entry->parishGroupNamesLabel() !== '—')
                            <p class="mb-3 text-muted">
                                Giáo họ: <strong>{{ $entry->parishGroupNamesLabel() }}</strong>
                            </p>
                            @endif
                            <div class="form-group mb-0">
                                <label for="parish_code_{{ $entry->getKey() }}">
                                    Mã giáo xứ <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    name="parish_code"
                                    id="parish_code_{{ $entry->getKey() }}"
                                    class="form-control text-uppercase"
                                    value="{{ old('parish_code', $suggestedCode) }}"
                                    maxlength="10"
                                    required
                                    autocomplete="off"
                                    placeholder="VD: HDO" />
                                <small class="form-text text-muted">
                                    Đã gợi ý từ tên xứ. Có thể sửa; mã phải duy nhất và không đổi sau này.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                            <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @else
        <form method="POST" action="{{ url($crud->route.'/'.$entry->getKey().'/approve') }}" class="d-inline"
            onsubmit="return confirm('Duyệt yêu cầu đăng ký quản trị xứ này?');">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">
                <i class="la la-check"></i> Duyệt
            </button>
        </form>
    @endif
@endif
