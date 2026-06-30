<?php

namespace App\Http\Controllers\Admin;

use App\Models\ParishGroup;
use App\Models\ParishNew;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ParishGroupCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(ParishGroup::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parish-group');
        CRUD::setEntityNameStrings('Giáo họ', 'Giáo họ');
        CRUD::orderBy('name', 'asc');

        if (! backpack_user()?->isSuperAdmin()) {
            CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
            return;
        }

        CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show']);
    }

    protected function setupListOperation(): void
    {
        $this->crud->query->with('parish');

        CRUD::addColumn([
            'name'  => 'name',
            'type'  => 'text',
            'label' => 'Tên giáo họ',
        ]);

        CRUD::addColumn([
            'name'     => 'parish_id',
            'type'     => 'closure',
            'label'    => __('backend.parish_management'),
            'function' => fn ($entry) => $entry->parish?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'     => 'status',
            'type'     => 'closure',
            'label'    => __('backend.status'),
            'function' => fn ($entry) => $entry->status ? __('backend.publish') : __('backend.draft'),
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::addField([
            'name'    => 'parish_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.parish_management'),
            'options' => ParishNew::query()->where('status', 1)->orderBy('name')->pluck('name', 'id')->all(),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'name',
            'type'    => 'text',
            'label'   => __('backend.name'),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'status',
            'type'    => 'checkbox',
            'label'   => __('backend.publish'),
            'default' => true,
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
