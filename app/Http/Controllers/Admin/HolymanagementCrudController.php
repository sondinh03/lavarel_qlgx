<?php

namespace App\Http\Controllers\Admin;

use App\Exports\HolyExport;
use App\Http\Requests\HolymanagementRequest;
use App\Models\Holymanagement;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class HolymanagementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class HolymanagementCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Holymanagement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/holymanagement');
        CRUD::setEntityNameStrings(__('backend.holymanagement'), __('backend.holymanagements'));

        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions
         |--------------------------------------------------------------------------
         */
        if (! backpack_user()->can('view_manager')) {
            CRUD::denyAccess(['list']);
        }

        if (backpack_user()->can('delete_manager')) {
            //CRUD::enableBulkActions();
            //CRUD::addBulkDeleteButton();
        } else {
            CRUD::removeButton('delete');
        }

        if (! backpack_user()->can('create_manager')) {
            CRUD::removeButton('create');
        }

        if (backpack_user()->can('update_manager')) {
            CRUD::allowAccess(['revisions']);
            CRUD::with('revisionHistory');
        } else {
            CRUD::removeButton('update');
            CRUD::allowAccess(['show']);
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addFilter(
            [
                'name'  => 'name',
                'type'  => 'text',
                'label' => __('backend.holymanagement'),
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'name', 'like', '%' . $value . '%');
            }
        );

        CRUD::addColumn([
            'name'  => 'name',
            'type'  => 'text',
            'label' => __('backend.holymanagement'),
            'limit' => 255,
        ]);
        CRUD::addColumn([
            'name'      => 'students_count',
            'label'     => 'Số học sinh',
            'type'      => 'text',
            'wrapper'   => ['element' => 'span', 'class' => 'badge badge-secondary'],
        ]);
        CRUD::addColumn([
            'name'      => 'parishioners_count',
            'label'     => 'Số giáo dân',
            'type'      => 'text',
            'wrapper'   => ['element' => 'span', 'class' => 'badge badge-secondary'],
        ]);
        CRUD::addColumn([
            'name'      => 'teachers_count',
            'label'     => 'Số GLV',
            'type'      => 'text',
            'wrapper'   => ['element' => 'span', 'class' => 'badge badge-secondary'],
        ]);

        CRUD::addClause('withCount', ['students', 'parishioners', 'teachers']);
        CRUD::orderBy('name', 'asc');

        CRUD::addButtonFromView('top', 'export_holy', 'export_holy', 'end');
    }

    /**
     * Xuất Excel danh sách tên thánh (định dạng đồng bộ với /ten-thanh).
     */
    public function export(): BinaryFileResponse
    {
        abort_unless(backpack_user()->can('view_manager'), 403);

        $search = request()->query('name');

        return Excel::download(
            new HolyExport(is_string($search) ? $search : null),
            'DanhSachTenThanh_' . now()->format('dmY_His') . '.xlsx'
        );
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(HolymanagementRequest::class);

        CRUD::addField([
            'name'  => 'name',
            'type'  => 'text',
            'label' => __('backend.holymanagement'),
            'hint'  => 'VD: Maria, Giuse, Phêrô…',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => 'Nhập tên thánh',
            ],
            'tab' => __('backend.general'),
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->setupShowFromListColumns();
    }

    /**
     * Không cho xóa tên thánh đang được sử dụng.
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $holy = Holymanagement::query()
            ->withCount(['students', 'parishioners', 'teachers'])
            ->findOrFail($id);

        $usage = (int) $holy->students_count
            + (int) $holy->parishioners_count
            + (int) $holy->teachers_count;

        if ($usage > 0) {
            return [
                'error' => ['Không thể xóa tên thánh đang được sử dụng (học sinh / giáo dân / GLV).'],
            ];
        }

        return $this->crud->delete($id);
    }
}
