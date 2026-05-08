<?php

namespace App\Http\Controllers\Admin;

use App\Models\GradeLevel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\SetAdmin;
use App\Models\Decen;

class GradeLevelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\ReviseOperation\ReviseOperation;

    public function setup()
    {
        CRUD::setModel(GradeLevel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/grade-level');
        CRUD::setEntityNameStrings('Khối lớp', 'Khối lớp');

        // --- Permissions (giữ nguyên logic từ BlockCrudController) ---
        if (!backpack_user()->can('view_manager')) {
            CRUD::denyAccess(['list']);
        }
        if (!backpack_user()->can('delete_manager')) {
            CRUD::removeButton('delete');
        }
        if (!backpack_user()->can('create_manager')) {
            CRUD::removeButton('create');
        }
        if (backpack_user()->can('update_manager')) {
            CRUD::allowAccess(['revisions']);
            CRUD::with('revisionHistory');
        } else {
            CRUD::removeButton('update');
            CRUD::allowAccess(['show']);
        }

        $user = backpack_user();
        if (!empty($user->id)) {
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();
            if (!empty($setadmin)) {
                CRUD::allowAccess(['create', 'delete', 'update', 'show']);
            } else {
                $decen = Decen::where('use', $user->id)->where('status', '1')->first();
                if (empty($decen->student)) {
                    CRUD::denyAccess(['create', 'delete', 'update', 'show']);
                }
            }
        }
    }

    protected function setupListOperation()
    {
        CRUD::addColumn(['name' => 'name',       'type' => 'text',    'label' => 'Tên khối']);
        CRUD::addColumn(['name' => 'code',       'type' => 'text',    'label' => 'Mã khối']);
        CRUD::addColumn(['name' => 'sort_order', 'type' => 'number',  'label' => 'Thứ tự']);
        CRUD::addColumn([
            'name'     => 'is_active',
            'type'     => 'closure',
            'label'    => 'Trạng thái',
            'function' => fn($entry) => $entry->is_active ? 'Hoạt động' : 'Tạm ẩn',
        ]);
    }

    protected function setupCreateOperation()
    {
        // Validation: tạo GradeLevelRequest (xem bước 2)
        CRUD::setValidation(\App\Http\Requests\GradeLevelRequest::class);

        CRUD::addField(['name' => 'name',       'type' => 'text',   'label' => 'Tên khối',
            'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'code',       'type' => 'text',   'label' => 'Mã khối',
            'wrapper' => ['class' => 'form-group col-md-2']]);
        CRUD::addField(['name' => 'sort_order', 'type' => 'number', 'label' => 'Thứ tự sắp xếp', 'default' => 0,
            'wrapper' => ['class' => 'form-group col-md-2']]);
        CRUD::addField([
            'name'    => 'is_active',
            'type'    => 'radio',
            'label'   => 'Trạng thái',
            'options' => [1 => 'Hoạt động', 0 => 'Tạm ẩn'],
            'default' => 1,
            'inline'  => true,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}