<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DecenRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Decen;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Decents;

/**
 * Class DecenCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DecenCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\ReviseOperation\ReviseOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Decen::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/decen');
        CRUD::setEntityNameStrings(__('backend.decen'), __('backend.decens'));
        
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
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure', 'label' => __('backend.parish_managements'), 'function' => function ($entry) {
            if(!empty($entry->pid)){
                $array_parish = DB::table('parish_managements')
                ->where('status', '1')
                ->where('id', $entry->pid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_parish->name;
            }
        }]);
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            if(!empty($entry->deid)){
                $array_deanerys = DB::table('deanerys')
                ->where('status', '1')
                ->where('id', $entry->deid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_deanerys->name;
            }
        }]);
        CRUD::addColumn(['name' => 'diocese', 'type' => 'closure', 'label' => __('backend.diocese'), 'function' => function ($entry) {
            if(!empty($entry->did)){
                $array_diocese = DB::table('dioceses')
                ->where('status', '1')
                ->where('id', $entry->did)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_diocese->name;
            }
        }]);
        CRUD::addColumn(['name' => 'parish', 'type' => 'closure', 'label' => __('backend.parish_decen'), 'function' => function ($entry) {
            if ($entry->parish == 0) {
                return __('backend.decen_no');
            } else {
                return __('backend.decen_yes');
            }
        }]);
        CRUD::addColumn(['name' => 'student', 'type' => 'closure', 'label' => __('backend.student_decen'), 'function' => function ($entry) {
            if ($entry->student == 0) {
                return __('backend.decen_no');
            } else {
                return __('backend.decen_yes');
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
        CRUD::setValidation(DecenRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */        
        
        /*
        CRUD::addField([
            'name'                      => 'use',
            'type'                      => 'select2_from_ajax',
            'entity'                    => false, // the method that defines the relationship in your Model
            'attribute'                 => "use", // foreign key attribute that is shown to user
            'data_source'               => url("api/Decen"), // url to controller search function (with /{id} should return model)
            //             /'pivot'         => false,
            'placeholder'               => "Chọn người quản lý",
            'minimum_input_length'      => 1,
            'model'                     => Decen::class,
            'label'                     => __('backend.user'),
            'wrapper'                   => [
                'class' => 'form-group col-md-3',
            ],
            'tab'                       => __('backend.general'),
        ]);
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
        
        $array_diocese = DB::table('dioceses')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        
        $array_diocese = array_values($array_diocese);
        $array_diocese = json_decode(json_encode($array_diocese, true), true);
        $array_dio = array(
            '---',
        );
        foreach($array_diocese as $item){
            $array_dio[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'did',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'did',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $deanerys = $this->GetDeanerys(request()->route('id'));
        
        CRUD::addField([
            'name' => 'deid',
            'type' => 'select_from_array',
            'attribute' => 'deid',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parishs = $this->GetParish(request()->route('id'));
        
        CRUD::addField([
            'name' => 'pid',
            'type' => 'select_from_array',
            'attribute' => 'pid',
            'options' => $parishs,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'parish',
            'type' => 'checkbox',
            'label' => __('backend.parish_decen'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'default' => '',
            'tab' => __('backend.general')
        ]);
        
        CRUD::addField([
            'name' => 'student',
            'type' => 'checkbox',
            'label' => __('backend.student_decen'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'default' => '',
            'tab' => __('backend.general')
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
        
        ?>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
			jQuery(document).ready(function($){                
                $("select[name='did']").change(function() {
                    var $option = $(this).find('option:selected');
                    var did = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parish",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {did:did},
                        beforeSend: function(){
                            $("select[name='deid'] option[value]").remove();
                            $("select[name='pid'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='deid']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='deid']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='deid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deid = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parish",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {deid:deid},
                        beforeSend: function(){
                            $("select[name='pid'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='pid']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='pid']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
			});
		</script>
        <?php
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
    
    public function GetDeanerys($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('decen')
            ->Join('deanerys', 'decen.did', '=', 'deanerys.did')
            ->where('decen.id', '=', $id)
            ->where('decen.status', '=', 1)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetParish($id){
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('decen')
                ->rightJoin('parish_managements', 'parish_managements.deanerys', '=', 'decen.deid')
                ->where('decen.id', '=', $id)
                ->where('parish_managements.status', '=', 1)
                ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    

    protected function setupShowOperation()
    {
        $this->setupShowFromListColumns();
    }
}
