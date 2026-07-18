<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AssociationRequest;
use App\Http\Controllers\AssociationController;
use App\Models\Association;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Backpack\ReviseOperation\ReviseOperation;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\SetAdmin;

/**
 * Class AssociationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AssociationCrudController extends CrudController
{
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
        CRUD::setModel(\App\Models\Association::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/association');
        CRUD::setEntityNameStrings(__('backend.association'), __('backend.associations'));
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
        
        //if (Auth::check()){
            $user = backpack_user();
            if(!empty($user->id)){
                $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
                if(!empty($setadmin)){
                    CRUD::allowAccess('create');
                    CRUD::allowAccess('delete');
                    CRUD::allowAccess('update');
                    CRUD::allowAccess('show');
                }else{
                    $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                    if(empty($decen->parish)){
                        CRUD::denyAccess('create');
                        CRUD::denyAccess('delete');
                        CRUD::denyAccess('update');
                        CRUD::denyAccess('show');
                        //CRUD::denyAccess('create');
                        //CRUD::allowAccess(['show']);
                    }
                }
            }
        //}
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
        if (! $this->crud->getRequest()->has('order')){
            //$this->crud->orderBy('updated_at', 'desc');
            $user = backpack_user();
            if(!empty($user->id)){
                $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
                if(empty($setadmin)){
                    $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                    if(!empty($decen->parish)){
                        CRUD::addClause('where', 'pid', $decen->pid);
                    }else{
                        CRUD::addClause('where', 'pid', 0);
                    }
                }                
            }
        }
        
        CRUD::with(['parish', 'deanery', 'diocese']);

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.parish_managements'), 'function' => function ($entry) {
            return $entry->parish?->name ?? '—';
        }]);
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            return $entry->deanery?->name ?? '—';
        }]);
        CRUD::addColumn(['name' => 'diocese', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.diocese'), 'function' => function ($entry) {
            return $entry->diocese?->name ?? '—';
        }]);
        CRUD::addColumn(['name' => 'status', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.status'), 'function' => function ($entry) {
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
        CRUD::setValidation(AssociationRequest::class);
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
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_diocese = DB::table('dioceses')
        ->where('status', '1')
        ->orderBy('id', 'ASC')
        ->get()
        ->toArray();
        $array_diocese = array_values($array_diocese);
        $array_diocese = json_decode(json_encode($array_diocese, true), true);
        $array_dio = array();
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
        if(empty($deanerys)){
            $first_value = reset($array_diocese);
            $deanerys = $this->GetDeanery_first($first_value);
        }
        
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
        
        
        $parishs = $this->GetParishs(request()->route('id'));
        if(empty($parishs)){
            $first_value = reset($deanerys);
            $first_key = key($deanerys);
            $parishs = $this->GetParishs_first($first_key);
        }
        
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
            'name'  => 'ngaybonmang',
            'type'  => 'date_picker',
            'label' => __('backend.ngaybonmang'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm',
                'language' => 'vi'
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-3',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name'  => 'ngaythanhlap',
            'type'  => 'date_picker',
            'label' => __('backend.ngaythanhlap'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-3',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name' => 'thanhbonmang',
            'type' => 'text',
            'label' => __('backend.thanhbonmang'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'note',
            'type' => 'textarea',
            'attribute' => 'note',
            'label' => __('backend.note'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
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
        
        CRUD::addfield([
            'name' => 'slug',
            'type' => 'slug',
            'source' => 'name',
            'controller' => AssociationController::class,
            'model' => Association::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
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
            $array_deanerys = DB::table('parish_groups')
            ->join('parishes', 'parishes.id', '=', 'parish_groups.parish_id')
            ->join('deanerys', 'deanerys.did', '=', 'parishes.diocese_id')
            ->where('parish_groups.id', '=', $id)
            ->where('parish_groups.status', '=', 1)
            ->select('deanerys.id', 'deanerys.name')
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetDeanery_first($array_dioceses){
        $array_dea = array();
        if(!empty($array_dioceses)){
            $array_deanerys = DB::table('deanerys')
            ->select('id', 'did', 'name')
            ->where('did', '=', $array_dioceses)
            ->where('status', 1)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetParishs($id){
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('parish_groups')
            ->rightJoin('parishes', 'parishes.id', '=', 'parish_groups.parish_id')
            ->where('parish_groups.id', '=', $id)
            ->where('parishes.status', '=', 1)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    public function GetParishs_first($id){
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('parish_managements')
            ->select('id', 'name', 'deanerys', 'diocese')
            ->where('deanerys', '=', $id)
            ->where('status', '=', 1)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
}
