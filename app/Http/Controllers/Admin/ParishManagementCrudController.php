<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishManagementRequest;
use App\Http\Controllers\ParishManagementController;
use App\Models\ParishManagement;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use Illuminate\Support\Facades\DB;
use App\Models\SetAdmin;
use App\Models\Decen;
/**
 * Class ParishManagementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ParishManagementCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ParishManagement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parish-management');
        CRUD::setEntityNameStrings(__('backend.parish_managements'), __('backend.parish_managements'));
        CRUD::orderBy('id', 'desc');
        
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
                if(empty($decen->student)){
                    CRUD::denyAccess('create');
                    CRUD::denyAccess('delete');
                    CRUD::denyAccess('update');
                    CRUD::denyAccess('show');
                    //CRUD::denyAccess('create');
                    //CRUD::allowAccess(['show']);
                }
            }
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
        
        CRUD::setModel(ParishManagement::class);
        
        if (! $this->crud->getRequest()->has('order')){
            //$this->crud->orderBy('updated_at', 'desc');
            $user = backpack_user();
            if(!empty($user->id)){
                $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
                if(empty($setadmin)){
                    $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                    if(!empty($decen->parish)){
                        CRUD::addClause('where', 'id', $decen->pid);
                    }else{
                        CRUD::addClause('where', 'id', 0);
                    }
                }
            }
        }
        
        CRUD::addColumn(['name' => 'image', 'type' => 'image', 'orderable' => false, 'label' => __('backend.image')]);
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            if(!empty($entry->deanerys)){
                $array_deanerys = DB::table('deanerys')
                ->where('status', '1')
                ->where('id', $entry->deanerys)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_deanerys->name;
            }
        }]);
        CRUD::addColumn(['name' => 'diocese', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.diocese'), 'function' => function ($entry) {
            if(!empty($entry->diocese)){
                $array_diocese = DB::table('dioceses')
                    ->where('status', '1')
                    ->where('id', $entry->diocese)
                    ->orderBy('id', 'ASC')
                    ->first();
                return $array_diocese->name;
            }
        }]);
        CRUD::addColumn(['name' => 'phone', 'type' => 'text', 'orderable' => false, 'label' => __('backend.phone')]);
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
        CRUD::setValidation(ParishManagementRequest::class);
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
            'name' => 'diocese',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'diocese',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $deanerys = $this->GetDeanerys(request()->route('id'));
        if(empty($deanerys)){
            $first_value = reset($array_diocese);
            $deanerys = $this->GetDeanery_first($first_value);
        }
        
        CRUD::addField([
            'name' => 'deanerys',
            'type' => 'select_from_array',
            'attribute' => 'deanerys',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'tab' => __('backend.general'),
        ]);  
        
        @include(resource_path().'/cities/tinh_thanhpho.php');
        
        CRUD::addField([
            'name' => 'province',
            'type' => 'select_from_array',
            'label' => __('backend.province'),
            'attribute' => 'province',
            'options' => $tinh_thanhpho,
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
        
        $xaphuong = $this->GetXa(request()->route('id'));
        
        CRUD::addField([
            'name' => 'ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            'model' => ParishManagement::class,
            'attribute' => 'ward',
            'options'   => $xaphuong,
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'phone',
            'type' => 'text',
            'label' => __('backend.phone'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => __('backend.general'),
        ]);
        CRUD::addField([
            'name' => 'image',
            'type' => 'browse_custom',
            'mimes' => ['image'],
            'label' => __('backend.images'),
            'wrapper' => [
                'class' => 'form-group col-md-6',
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
            'controller' => ParishManagementController::class,
            'model' => ParishManagement::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
        ]);
        
        ?>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
			jQuery(document).ready(function($){
                $("select[name='province']").change(function() {
                    var $option = $(this).find('option:selected');
                    var province = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/ParishManagement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {province:province},
                        beforeSend: function(){
                            $("select[name='ward'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='ward']").append(
                                    "<option value=" + value.xaid + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='diocese']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/ParishManagement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {diocese:diocese},
                        beforeSend: function(){
                            $("select[name='deanerys'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='deanerys']").append(
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
    
    public function GetXa($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            $parish_managements = DB::table('parish_managements')->where('id', $id)->get()->toArray();
            
            $province = $parish_managements[0]->province;
            
            foreach($xa_phuong_thitran as $xa){
                if($xa['matp'] == $province){
                    $array_xa[$xa['xaid']] = $xa['name'];
                }
            }
        }
        return $array_xa;
    }
    
    public function GetDeanerys($id){        
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('parish_managements')
                ->select('deanerys.id', 'deanerys.did', 'deanerys.name')
                ->rightJoin('deanerys', 'deanerys.did', '=', 'parish_managements.diocese')
                ->where('parish_managements.id', '=', $id)
                ->where('deanerys.status', '=', 1)
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
}
