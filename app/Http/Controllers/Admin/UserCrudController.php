<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

class UserCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings(__('backend.user'), __('backend.users'));

        $user = backpack_user();

        if ($user->isSuperAdmin()) {
            CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show']);
            return;
        }

        if ($user->isParishAdmin()) {
            CRUD::allowAccess(['list', 'show']);
            CRUD::denyAccess(['create', 'update', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('update');
            CRUD::removeButton('delete');
            return;
        }

        CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
    }

    protected function setupListOperation()
    {
        $user = backpack_user();

        if ($user->isParishAdmin() && !$user->isSuperAdmin()) {
            CRUD::addClause('where', 'parish_id', $user->parish_id ?? 0);
        }

        $this->crud->query->with(['parish']);

        CRUD::addColumn([
            'name'  => 'name',
            'type'  => 'text',
            'label' => __('backend.name'),
        ]);

        CRUD::addColumn([
            'name'  => 'email',
            'type'  => 'text',
            'label' => __('backend.email'),
        ]);

        CRUD::addColumn([
            'name'     => 'parish_id',
            'type'     => 'closure',
            'label'    => __('backend.parish_management'),
            'function' => fn($entry) => $entry->parish?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'     => 'roles',
            'type'     => 'closure',
            'label'    => 'Vai trò',
            'function' => fn($entry) => $entry->getRoleNames()->implode(', ') ?: '—',
        ]);

        CRUD::addColumn([
            'name'     => 'is_active',
            'type'     => 'closure',
            'label'    => 'Trạng thái',
            'orderable' => true,
            'function' => fn ($entry) => $entry->is_active ? 'Hoạt động' : 'Ngưng',
        ]);
    }

    protected function setupShowOperation()
    {
        $this->applyStandardShowSettings();

        CRUD::setShowView('admin.user.show');
        $this->crud->with(['parish', 'roles']);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        // ← Thêm dòng này: báo Backpack bỏ qua field 'roles' khi lưu vào DB
        $this->crud->setOperationSetting('saveAllInputsExcept', [
            '_token',
            '_method',
            'http_method',
            'current_tab',
            'save_action',
            'assigned_role',
            'roles',
        ]);

        CRUD::addField([
            'name'    => 'name',
            'type'    => 'text',
            'label'   => __('backend.name'),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'email',
            'type'    => 'text',
            'label'   => __('backend.email'),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'password',
            'type'    => 'password',
            'label'   => __('backend.password'),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // --- Giáo xứ ---
        $user        = backpack_user();
        $parishQuery = DB::table('parishes')->where('status', 1)->orderBy('name');

        if ($user->isParishAdmin() && !$user->isSuperAdmin()) {
            $parishQuery->where('id', $user->parish_id);
        }

        $parishes = [];
        foreach ($parishQuery->get() as $parish) {
            $parishes[$parish->id] = $parish->name;
        }

        CRUD::addField([
            'name'        => 'parish_id',
            'type'        => 'select_from_array',
            'label'       => 'Giáo xứ',
            'options'     => $parishes,
            'allows_null' => true,
            'default'     => $user->isParishAdmin() ? $user->parish_id : null,
            'wrapper'     => ['class' => 'form-group col-md-6'],
        ]);

        // --- Vai trò (Spatie) — field ảo, tránh trùng quan hệ `roles` ---
        $roles = [];
        foreach (DB::table('roles')->orderBy('name')->get() as $role) {
            $roles[$role->name] = $role->name;
        }

        $currentRole = $this->crud->getCurrentEntry()
            ? $this->crud->getCurrentEntry()->getRoleNames()->first()
            : null;

        CRUD::addField([
            'name'        => 'assigned_role',
            'type'        => 'select_from_array',
            'label'       => 'Vai trò',
            'options'     => $roles,
            'allows_null' => false,
            'value'       => $currentRole,
            'default'     => $currentRole,
            'wrapper'     => ['class' => 'form-group col-md-6'],
        ]);

        if (backpack_user()?->isSuperAdmin()) {
            CRUD::addField([
                'name'    => 'is_active',
                'type'    => 'checkbox',
                'label'   => 'Hoạt động',
                'default' => true,
                'hint'    => 'Bỏ chọn để vô hiệu hóa tài khoản (không đăng nhập được). Không nên tắt tài khoản đang đăng nhập của chính bạn.',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]);
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        // ← Thêm lại vì setupUpdateOperation chạy độc lập với setupCreateOperation
        $this->crud->setOperationSetting('saveAllInputsExcept', [
            '_token',
            '_method',
            'http_method',
            'current_tab',
            'save_action',
            'assigned_role',
            'roles',
        ]);

        $entry = $this->crud->getCurrentEntry();
        if ($entry) {
            $this->crud->modifyField('assigned_role', [
                'value'   => $entry->getRoleNames()->first(),
                'default' => $entry->getRoleNames()->first(),
            ]);
        }

        CRUD::addField([
            'name'    => 'password',
            'type'    => 'password',
            'label'   => __('backend.password'),
            'hint'    => 'Để trống nếu không muốn đổi mật khẩu.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);
    }
}
