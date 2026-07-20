<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SlugRequest;
use App\Models\Slug;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;

/**
 * Class SlugCrudController
 *
 * @property-read CrudPanel $crud
 */
class SlugCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;
    
    /**
     * @throws Exception
     */
    public function setup()
    {
        /*
         |--------------------------------------------------------------------------
         | CrudPanel Basic Information
         |--------------------------------------------------------------------------
         */
        CRUD::setModel(Slug::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/slug');
        CRUD::setEntityNameStrings('slug', 'slugs');
        CRUD::orderBy('id', 'desc');
    }
    
    protected function setupListOperation()
    {
        // calls to addColumn, addFilter, addButton, etc
        CRUD::setFromDb();
    }
    
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SlugRequest::class);
        
        // calls to addField
    }
    
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->setupShowFromListColumns();
    }
}
