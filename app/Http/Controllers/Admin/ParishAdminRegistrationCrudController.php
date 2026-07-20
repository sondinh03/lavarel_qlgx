<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ParishAdmin\ApproveParishAdminRegistrationAction;
use App\Actions\ParishAdmin\RejectParishAdminRegistrationAction;
use App\Models\ParishAdminRegistrationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Prologue\Alerts\Facades\Alert;

class ParishAdminRegistrationCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use AuthorizesRequests;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(ParishAdminRegistrationRequest::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parish-admin-registration');
        CRUD::setEntityNameStrings('yêu cầu đăng ký QTX', 'đăng ký quản trị xứ');

        if (! backpack_user()?->isSuperAdmin()) {
            CRUD::denyAccess(['list', 'show']);
        }

        // Script mở modal phải có sẵn trước khi DataTables inject HTML nút (AJAX)
        $this->pushParishAdminRegistrationModalScript();
    }

    protected function pushParishAdminRegistrationModalScript(): void
    {
        $script = view('vendor.backpack.crud.buttons.inc.parish_admin_registration_modal_js')->render();

        View::startPush('crud_list_scripts', $script);
        View::startPush('after_scripts', $script);
    }

    protected function setupListOperation(): void
    {
        CRUD::orderBy('created_at', 'desc');

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'dropdown',
            'label' => 'Trạng thái',
        ], config('parish-admin-registration.statuses', []), function ($value) {
            CRUD::addClause('where', 'status', $value);
        });

        CRUD::column('reference_code')->label('Mã tham chiếu');
        CRUD::column('name')->label('Họ tên');
        CRUD::column('email')->label('Email');
        CRUD::column('phone')->label('SĐT');
        CRUD::addColumn([
            'name'  => 'diocese.name',
            'label' => 'Giáo phận',
            'type'  => 'text',
        ]);
        CRUD::addColumn([
            'name'  => 'deanery.name',
            'label' => 'Giáo hạt',
            'type'  => 'text',
        ]);
        CRUD::addColumn([
            'name'     => 'parish_display',
            'label'    => 'Giáo xứ',
            'type'     => 'closure',
            'function' => fn ($entry) => $entry->parishDisplayName(),
        ]);
        CRUD::addColumn([
            'name'     => 'parish_groups_display',
            'label'    => 'Giáo họ yêu cầu',
            'type'     => 'closure',
            'function' => fn ($entry) => $entry->parishGroupNamesLabel(),
        ]);
        CRUD::addColumn([
            'name'     => 'requested_roles',
            'label'    => 'Quyền',
            'type'     => 'closure',
            'function' => fn ($entry) => implode(', ', $entry->requestedRoleLabels()) ?: '—',
        ]);
        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'Trạng thái',
            'type'          => 'closure',
            'function'      => fn ($entry) => $entry->statusLabel(),
            'escaped'       => false,
        ]);
        CRUD::column('created_at')->label('Ngày gửi')->type('datetime');

        CRUD::addButtonFromView('line', 'approve_parish_admin_registration', 'approve_parish_admin_registration', 'end');
        CRUD::addButtonFromView('line', 'reject_parish_admin_registration', 'reject_parish_admin_registration', 'end');
    }

    protected function setupShowOperation(): void
    {
        $this->applyStandardShowSettings();

        CRUD::column('reference_code')->label('Mã tham chiếu');
        CRUD::column('name')->label('Họ tên');
        CRUD::column('email')->label('Email');
        CRUD::column('phone')->label('SĐT');
        CRUD::addColumn([
            'name'  => 'diocese.name',
            'label' => 'Giáo phận',
            'type'  => 'text',
        ]);
        CRUD::addColumn([
            'name'  => 'deanery.name',
            'label' => 'Giáo hạt',
            'type'  => 'text',
        ]);
        CRUD::addColumn([
            'name'     => 'parish_display',
            'label'    => 'Giáo xứ',
            'type'     => 'closure',
            'function' => fn ($entry) => $entry->parishDisplayName(),
        ]);
        CRUD::addColumn([
            'name'     => 'parish_groups_display',
            'label'    => 'Giáo họ yêu cầu',
            'type'     => 'closure',
            'function' => fn ($entry) => $entry->parishGroupNamesLabel(),
        ]);
        CRUD::addColumn([
            'name'     => 'requested_roles',
            'label'    => 'Quyền yêu cầu',
            'type'     => 'closure',
            'function' => fn ($entry) => implode(', ', $entry->requestedRoleLabels()) ?: '—',
        ]);
        CRUD::column('note')->label('Ghi chú')->type('textarea');
        CRUD::addColumn([
            'name'     => 'status',
            'label'    => 'Trạng thái',
            'type'     => 'closure',
            'function' => fn ($entry) => $entry->statusLabel(),
        ]);
        CRUD::column('rejection_reason')->label('Lý do từ chối')->type('textarea');
        CRUD::addColumn([
            'name'  => 'reviewer.name',
            'label' => 'Người duyệt',
            'type'  => 'text',
        ]);
        CRUD::column('reviewed_at')->label('Thời gian duyệt')->type('datetime');
        CRUD::column('ip_address')->label('IP');
        CRUD::column('created_at')->label('Ngày gửi')->type('datetime');

        CRUD::addButtonFromView('top', 'approve_parish_admin_registration', 'approve_parish_admin_registration', 'end');
        CRUD::addButtonFromView('top', 'reject_parish_admin_registration', 'reject_parish_admin_registration', 'end');
    }

    public function approve(int $id, Request $request, ApproveParishAdminRegistrationAction $action)
    {
        $entry = ParishAdminRegistrationRequest::findOrFail($id);
        $this->authorize('approve', $entry);

        $parishCode = null;

        if ($entry->createsNewParish()) {
            try {
                $validated = $request->validate([
                    'parish_code' => [
                        'required',
                        'string',
                        'max:10',
                        'regex:/^[A-Za-z0-9\\-]+$/',
                        'unique:parishes,code',
                    ],
                ], [
                    'parish_code.required' => 'Vui lòng nhập mã giáo xứ.',
                    'parish_code.unique'   => 'Mã giáo xứ đã tồn tại.',
                    'parish_code.max'      => 'Mã giáo xứ không được quá 10 ký tự.',
                    'parish_code.regex'    => 'Mã giáo xứ chỉ gồm chữ, số và dấu gạch ngang.',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Alert::error($e->validator->errors()->first())->flash();

                return redirect()->back()->withInput();
            }

            $parishCode = strtoupper(trim($validated['parish_code']));
        }

        try {
            $result = $action->handle($entry, backpack_user(), $parishCode);
            Alert::success('Đã duyệt yêu cầu. Tài khoản: ' . $result['user']->email)->flash();
        } catch (InvalidArgumentException $e) {
            Alert::error($e->getMessage())->flash();
        } catch (\Throwable $e) {
            report($e);
            Alert::error('Không thể duyệt yêu cầu. Vui lòng thử lại.')->flash();
        }

        return redirect()->back();
    }

    public function reject(int $id, Request $request, RejectParishAdminRegistrationAction $action)
    {
        $entry = ParishAdminRegistrationRequest::findOrFail($id);
        $this->authorize('reject', $entry);

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $action->handle($entry, backpack_user(), $request->input('rejection_reason'));
            Alert::success('Đã từ chối yêu cầu đăng ký.')->flash();
        } catch (InvalidArgumentException $e) {
            Alert::error($e->getMessage())->flash();
        } catch (\Throwable $e) {
            report($e);
            Alert::error('Không thể từ chối yêu cầu. Vui lòng thử lại.')->flash();
        }

        return redirect()->back();
    }
}
