<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\NamHocRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class NamHocCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\NamHoc::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/nam-hoc');
        CRUD::setEntityNameStrings(__('backend.namhoc'), __('backend.namhoc'));
    }

    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name'  => 'name',
            'type'  => 'text',
            'label' => __('backend.name'),
        ]);

        // setupListOperation — thêm sau cột name
        CRUD::addColumn([
            'name'      => 'parish',
            'type'      => 'relationship',
            'label'     => 'Giáo xứ',
            'attribute' => 'name',
        ]);

        CRUD::addColumn([
            'name'  => 'semester_1_display',
            'type'  => 'text',
            'label' => 'Học kỳ 1',
        ]);

        CRUD::addColumn([
            'name'  => 'semester_2_display',
            'type'  => 'text',
            'label' => 'Học kỳ 2',
        ]);

        CRUD::addColumn([
            'name'     => 'status_label',
            'type'     => 'text',
            'label'    => __('backend.status'),
        ]);
    }

    protected function setupShowOperation()
    {
        $this->applyStandardShowSettings();

        CRUD::addColumn([
            'name'  => 'name',
            'type'  => 'text',
            'label' => 'Tên năm học',
        ]);

        CRUD::addColumn([
            'name'      => 'parish',
            'type'      => 'relationship',
            'label'     => 'Giáo xứ',
            'attribute' => 'name',
        ]);

        CRUD::addColumn([
            'name'  => 'semester_1_display',
            'type'  => 'text',
            'label' => 'Học kỳ 1',
        ]);

        CRUD::addColumn([
            'name'  => 'semester_2_display',
            'type'  => 'text',
            'label' => 'Học kỳ 2',
        ]);

        CRUD::addColumn([
            'name'  => 'status_label',
            'type'  => 'text',
            'label' => 'Trạng thái',
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(NamHocRequest::class);

        // Tab: Thông tin chung
        CRUD::addField([
            'name'        => 'parish_id',
            'type'        => 'select',
            'label'       => 'Giáo xứ',
            'entity'      => 'parish',
            'attribute'   => 'name',
            'model'       => \App\Models\ParishNew::class,
            'wrapper'     => ['class' => 'form-group col-md-6'],
            'tab'         => 'Thông tin chung',
        ]);

        CRUD::addField([
            'name'    => 'status',
            'type'    => 'radio',
            'label'   => __('backend.status'),
            'options' => \App\Models\NamHoc::STATUS_LABELS,
            'default' => \App\Models\NamHoc::STATUS_ACTIVE,
            'inline'  => true,
            'tab'     => 'Thông tin chung',
        ]);

        // Tab: Học kỳ 1
        CRUD::addField([
            'name'    => 'start_date_one',
            'type'    => 'date',
            'label'   => 'Ngày bắt đầu HK1',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => 'Học kỳ 1',
        ]);

        CRUD::addField([
            'name'    => 'end_date_one',
            'type'    => 'date',
            'label'   => 'Ngày kết thúc HK1',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => 'Học kỳ 1',
        ]);

        // Tab: Học kỳ 2
        CRUD::addField([
            'name'    => 'start_date_two',
            'type'    => 'date',
            'label'   => 'Ngày bắt đầu HK2',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => 'Học kỳ 2',
        ]);

        CRUD::addField([
            'name'    => 'end_date_two',
            'type'    => 'date',
            'label'   => 'Ngày kết thúc HK2',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => 'Học kỳ 2',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
