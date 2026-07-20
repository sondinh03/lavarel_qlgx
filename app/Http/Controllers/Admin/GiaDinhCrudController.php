<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GiaDinhRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class GiaDinhCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class GiaDinhCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\GiaDinh::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/gia-dinh');
        CRUD::setEntityNameStrings('gia dinh', 'gia dinhs');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('male');
        CRUD::column('female');
        CRUD::column('children');
        CRUD::column('household');
        CRUD::column('name');
        CRUD::column('idpa');
        CRUD::column('sohokhau');
        CRUD::column('dien');
        CRUD::column('phone');
        CRUD::column('address');
        CRUD::column('ward');
        CRUD::column('province');
        CRUD::column('noio');
        CRUD::column('thongke');
        CRUD::column('tinhtrang');
        CRUD::column('note');
        CRUD::column('image');
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(GiaDinhRequest::class);

        CRUD::field('male');
        CRUD::field('female');
        CRUD::field('children');
        CRUD::field('household');
        CRUD::field('name');
        CRUD::field('idpa');
        CRUD::field('sohokhau');
        CRUD::field('dien');
        CRUD::field('phone');
        CRUD::field('address');
        CRUD::field('ward');
        CRUD::field('province');
        CRUD::field('noio');
        CRUD::field('thongke');
        CRUD::field('tinhtrang');
        CRUD::field('note');
        CRUD::field('image');
        CRUD::field('status');
        CRUD::field('created_at');
        CRUD::field('updated_at');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
}
