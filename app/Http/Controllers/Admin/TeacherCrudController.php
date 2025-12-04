<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TeacherRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\SetAdmin;
use App\Models\NamHoc;

/**
 * Class TeacherCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TeacherCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Teacher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/teacher');
        CRUD::setEntityNameStrings(__('backend.teacher'), __('backend.teachers'));
        
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
                    if(!empty($decen->student)){
                        CRUD::addClause('where', 'pid', $decen->pid);
                    }else{
                        CRUD::addClause('where', 'pid', 0);
                    }
                }
            }
        }
        
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'namhoc', 'type' => 'closure', 'orderable' => true, 'label' => __('backend.namhoc'), 'function' => function ($entry) {
            if(!empty($entry->namhoc)){
                $namhoc = NamHoc::where('id', $entry->namhoc)->where('status', 1)->get()->first();
                return $namhoc->name;
            }
        }]);
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure', 'orderable' => true, 'label' => __('backend.parish_managements'), 'function' => function ($entry) {
            if(!empty($entry->pid)){
                $array_parish = DB::table('parish_managements')
                ->where('status', '1')
                ->where('id', $entry->pid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_parish->name;
            }
        }]);
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'orderable' => true, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            if(!empty($entry->deid)){
                $array_deanerys = DB::table('deanerys')
                ->where('status', '1')
                ->where('id', $entry->deid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_deanerys->name;
            }
        }]);
        CRUD::addColumn(['name' => 'diocese', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.diocese'), 'function' => function ($entry) {
            if(!empty($entry->did)){
                $array_diocese = DB::table('dioceses')
                ->where('status', '1')
                ->where('id', $entry->did)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_diocese->name;
            }
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
        CRUD::setValidation(TeacherRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        
        $array_diocese = DB::table('dioceses')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_diocese = array_values($array_diocese);
        $array_diocese = json_decode(json_encode($array_diocese, true), true);
        $array_dio = array(
            '' => '------',
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
        
        $array_deanerys = $this->GetDeanerys(request()->route('id'));
        
        $array_dea = array(
            '' => '------',
        );
        
        foreach($array_deanerys as $key => $item){
            $array_dea[$key] = $item;
        }
        
        CRUD::addField([
            'name' => 'deid',
            'type' => 'select_from_array',
            'attribute' => 'deid',
            'options' => $array_dea,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parish = $this->GetParish(request()->route('id'));
        
        $array_parish = array(
            '' => '------',
        );
        foreach($parish as $key => $item){
            $array_parish[$key] = $item;
        }
        
        CRUD::addField([
            'name' => 'pid',
            'type' => 'select_from_array',
            'attribute' => 'pid',
            'options' => $array_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parishs = $this->GetParishs(request()->route('id'));
        $array_parishs = array(
            '' => '------',
        );
        foreach($parishs as $key => $item){
            $array_parishs[$key] = $item;
        }
        
        CRUD::addField([
            'name' => 'paid',
            'type' => 'select_from_array',
            'attribute' => 'paid',
            'options' => $array_parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.fullname'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'  => 'birthday',
            'type'  => 'date_picker',
            'label' => __('backend.birthday'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name'  => 'year',
            'type'  => 'date_picker',
            'label' => __('backend.year_in'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'yyyy',
                'language' => 'vi'
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name' => 'phone',
            'type' => 'number',
            'label' => __('backend.phone'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'note',
            'type' => 'text',
            'label' => __('backend.note'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'                      => 'namhoc',
            'type'                      => 'select_from_lop_ajax',
            'entity'                    => false, // the method that defines the relationship in your Model
            'attribute'                 => "namhoc", // foreign key attribute that is shown to user
            'data_source'               => url("api/NamHoc"), // url to controller search function (with /{id} should return model)
            //             /'pivot'         => false,
            'placeholder'               => "Chọn năm học",
            'minimum_input_length'      => 1,
            'model'                     => NamHoc::class,
            'label'                     => __('backend.schoolyear'),
            'wrapper'                   => [
                   'class' => 'form-group col-md-2',
               ],
            'tab'                       => __('backend.general'),
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
                        url: "/api/Family",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {did:did},
                        beforeSend: function(){
                            $("select[name='deid'] option[value]").remove();
                            $("select[name='pid'] option[value]").remove();
                            $("select[name='assid'] option[value]").remove();
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
                        url: "/api/Family",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {deid:deid},
                        beforeSend: function(){
                            $("select[name='pid'] option[value]").remove();      
                            $("select[name='assid'] option[value]").remove();
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
                
                $("select[name='pid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var pid = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Family",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {pid:pid},
                        beforeSend: function(){
                            $("select[name='paid'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='paid']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='paid']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
        	});
    	</script>
    	<script type="text/javascript">
			jQuery(document).ready(function($){
                $("select[name='province']").change(function() {
                    var $option = $(this).find('option:selected');
                    var province = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Family",
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
			});
		</script>
		
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$("select[name='pid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var giaoxu = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $('input[name="giaoxu"]').val(giaoxu).trigger('change');
                });
                
                var selectgiaoxu = $('select[name="pid"]').find(":selected").val();
                if(selectgiaoxu != ''){
                	$('input[name="giaoxu"]').val(selectgiaoxu).trigger('change');
                }
			});
		</script>
		
		<script type="text/javascript">
			jQuery(document).ready(function($){
                $("select[name='marriage_province']").change(function() {
                    var $option = $(this).find('option:selected');
                    var marriage_province = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Family",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {province:marriage_province},
                        beforeSend: function(){
                            $("select[name='marriage_ward'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='marriage_ward']").append(
                                    "<option value=" + value.xaid + ">" + value.name + "</option>"
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
            $teacher = DB::table('teacher')
            ->where('id', '=', $id)
            ->get()->first();
            
            $array_deanerys = DB::table('deanerys')
            ->where('did', '=', $teacher->did)
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
            $array_parish = DB::table('family')
            ->Join('parish_managements', 'family.deid', '=', 'parish_managements.deanerys')
            ->where('parish_managements.status', '=', 1)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParishs($id){
        $array_par = array();
        if(!empty($id)){
            $teacher = DB::table('teacher')
            ->where('id', '=', $id)
            ->get()->first();
            
            $array_parish = DB::table('parishs')
            ->where('did', '=', $teacher->did)
            ->where('deid', '=', $teacher->deid)
            ->where('pid', '=', $teacher->pid)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
}
