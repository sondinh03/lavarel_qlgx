<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RedirectRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;

/**
 * Class RedirectCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RedirectCrudController extends CrudController
{
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
        CRUD::setModel(\App\Models\Redirect::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/redirect');
        CRUD::setEntityNameStrings(__('backend.redirect'), __('backend.redirects'));
        
        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions
         |--------------------------------------------------------------------------
         */
        if (! backpack_user()->can('view_redirect')) {
            CRUD::denyAccess(['list']);
        }
        
        if (! backpack_user()->can('delete_redirect')) {
            CRUD::removeButton('delete');
        }
        
        if (! backpack_user()->can('create_redirect')) {
            CRUD::removeButton('create');
        }
        
        if (backpack_user()->can('update_redirect')) {
            CRUD::allowAccess(['reorder']);
            CRUD::enableReorder('name', 0);
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
        CRUD::setValidation(RedirectRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::field('old_url')->type('text')->prefix(url('/').'/');
        CRUD::field('new_url')->type('text')->wrapper(['class' => 'form-group col-md-8'])->prefix(url('/').'/');
        CRUD::field('type')
        ->type('select_from_array')
        ->options(['301' => 'Permanent (301)', '302' => 'Temporary (302)'])
        ->wrapper(['class' => 'form-group col-md-4']);
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
}
