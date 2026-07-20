<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SetAdminRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Decents;
use Illuminate\Support\Facades\DB;

/**
 * Class SetAdminCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SetAdminCrudController extends CrudController
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
        CRUD::setModel(\App\Models\SetAdmin::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/set-admin');
        CRUD::setEntityNameStrings('set admin', 'set admins');
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
        CRUD::addColumn(['name' => 'use', 'type' => 'closure', 'label' => __('backend.user'), 'function' => function ($entry) {
            if(!empty($entry->use)){
                $array_user = array();
                $user = DB::table('users')
                ->where('id', $entry->use)
                ->orderBy('id', 'ASC')
                ->first();
                $array_user = $user->name . ' - ' . $user->email;
                
                return $array_user;
            }
        }]);
            
        CRUD::addColumn(['name' => 'status', 'type' => 'closure', 'label' => __('backend.status'), 'function' => function ($entry) {
            if ($entry->status == 0) {
                return __('backend.draft');
            } else {
                return __('backend.publish');
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
        CRUD::setValidation(SetAdminRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::addField([
            'name'                  => 'use',
            'type'                  => 'select2_from_ajax',
            'label'                 => __('backend.user'),
            'data_source'           => url("api/Decen"),
            'model'                 => Decents::class,
            'delay'                 => 500,
            'placeholder'           => "Chọn quản lý",
            'minimum_input_length'  => 1,
            'attribute'             => "use",
            'wrapper'               => [
                'class' => 'form-group col-md-3',
            ],
            'tab'                   => __('backend.general'),
        ]);
        
        
        CRUD::addField([
            'name' => 'status',
            'type' => 'radio',
            'label' => __('backend.status'),
            'options' => [
                0 => __('backend.draft'),
                1 => __('backend.publish')
            ],
            'default'   => 1,
            'inline' => true,
            'tab' => __('backend.general')
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
