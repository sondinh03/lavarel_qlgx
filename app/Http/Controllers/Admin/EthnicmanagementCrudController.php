<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EthnicmanagementRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Exception;

/**
 * Class EthnicmanagementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EthnicmanagementCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Ethnicmanagement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/ethnicmanagement');
        CRUD::setEntityNameStrings(__('backend.ethnicmanagement'), __('backend.ethnicmanagements'));
        
        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions
         |--------------------------------------------------------------------------
         */
        if (! backpack_user()->can('view_decen')) {
            CRUD::denyAccess(['list']);
        }
        
        if (backpack_user()->can('delete_decen')) {
            //CRUD::enableBulkActions();
            //CRUD::addBulkDeleteButton();
        } else {
            CRUD::removeButton('delete');
        }
        
        if (! backpack_user()->can('create_decen')) {
            CRUD::removeButton('create');
        }
        
        if (backpack_user()->can('update_decen')) {
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
        

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
        
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'label' => __('backend.name'), 'limit' => 255]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(EthnicmanagementRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.name'),
            'wrapper' => [
                'class' => 'form-group col-md-4',
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
}
