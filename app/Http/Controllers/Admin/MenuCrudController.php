<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MenuRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;

/**
 * Class MenuCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MenuCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
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
        CRUD::setModel(\App\Models\Menu::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/menu');
        CRUD::setEntityNameStrings('menu', 'menus');
        
        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions
         |--------------------------------------------------------------------------
         */
        if (! backpack_user()->can('view_menu')) {
            CRUD::denyAccess(['list']);
        }
        
        if (! backpack_user()->can('delete_menu')) {
            CRUD::removeButton('delete');
        }
        
        if (! backpack_user()->can('create_menu')) {
            CRUD::removeButton('create');
        }
        
        if (backpack_user()->can('update_menu')) {
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
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name')]);
        CRUD::addColumn(['name' => 'status', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.status'), 'function' => function ($entry) {
            if ($entry->status == 0) {
                return '<span class="label label-danger">'.__('backend.hide').'</span>';
            } else {
                return '<span class="label label-success">'.__('backend.display').'</span>';
            }
        }]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(MenuRequest::class);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::addField(['name' => 'name', 'type' => 'text', 'label' => __('backend.name')]);
        CRUD::addField(['name' => 'status', 'type' => 'checkbox', 'default' => 1, 'label' => __('backend.display'), 'inline' => true]);
        CRUD::addField(['name' => 'link', 'type' => 'text', 'label' => __('backend.link')]);
        CRUD::addField(['name' => 'class', 'type' => 'text', 'label' => __('class')]);
        CRUD::addField(['name' => 'style', 'type' => 'text', 'label' => __('style')]);
        CRUD::addField(['name' => 'html', 'type' => 'text', 'label' => __('html')]);
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
