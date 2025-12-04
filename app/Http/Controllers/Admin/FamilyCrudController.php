<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FamilyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use App\Models\Children;
use App\Models\Father;
use App\Models\Mother;
use App\Http\Controllers\FamilyController;
use App\Models\Family;
use App\Models\Deanery;
use App\Models\Priest;
use App\Models\Marriage;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\SetAdmin;

/**
 * Class FamilyCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FamilyCrudController extends CrudController
{
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
        CRUD::setModel(\App\Models\Family::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/family');
        CRUD::setEntityNameStrings(__('backend.family'), __('backend.families'));
        
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
        
        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.parish_managements'), 'function' => function ($entry) {
            if(!empty($entry->pid)){
                $array_parish = DB::table('parish_managements')
                ->where('status', '1')
                ->where('id', $entry->pid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_parish->name;
            }
        }]);
        CRUD::addColumn(['name' => 'deid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            if(!empty($entry->deid)){
                $array_deanerys = DB::table('deanerys')
                ->where('status', '1')
                ->where('id', $entry->deid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_deanerys->name;
            }
        }]);
        CRUD::addColumn(['name' => 'did', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.diocese'), 'function' => function ($entry) {
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
        CRUD::setValidation(FamilyRequest::class);
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
        
        
        $parish = $this->GetParish(request()->route('id'));
        if(empty($parish)){
            $first_value = reset($deanerys);
            $first_key = key($deanerys);
            $parish = $this->GetParish_first($first_key);
        }
        
        CRUD::addField([
            'name' => 'pid',
            'type' => 'select_from_array',
            'attribute' => 'pid',
            'options' => $parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parishs = $this->GetParishs(request()->route('id'));
        if(empty($parishs)){
            $first_value = reset($parish);
            $first_key = key($parish);
            $parishs = $this->GetParishs_first($first_key);
        }
        
        CRUD::addField([
            'name' => 'paid',
            'type' => 'select_from_array',
            'attribute' => 'paid',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.name'),
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'father',
            'type' => 'select_from_ajax',
            'label' => __('backend.father'),
            'data_source' => url("api/Father"),
            'model'                => Father::class,
            'delay' => 500,
            'placeholder'          => "Chọn Cha", 
            'minimum_input_length'  => 2,
            'attribute'   => "father",
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'mother',
            'type' => 'select_from_ajax',
            'label' => __('backend.mother'),
            'data_source' => url("api/Mother"),
            'model'                => Mother::class,
            'delay' => 500,
            'placeholder'          => "Chọn Mẹ", 
            'minimum_input_length'  => 2,
            'attribute'   => "mother",
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'idhouse',
            'type' => 'radio',
            'label' => __('backend.chuho'),
            'options' => [
                0 => __('backend.mother'),
                1 => __('backend.father')
            ],
            'default'   => 1,
            'inline' => true,
            'attribute'   => "idhouse",
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general')
        ]);
        
        $family_areas = DB::table('family_areas')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $family_areas = array_values($family_areas);
        $family_areas = json_decode(json_encode($family_areas, true), true);
        $array_areas = array();
        foreach($family_areas as $item){
            $array_areas[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'dien',
            'type' => 'select_from_array',
            'label' => __('backend.family_areas'),
            'attribute' => 'diocese',
            'options' => $array_areas,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'household',
            'type' => 'number',
            'label' => __('backend.household'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'origin',
            'type' => 'text',
            'label' => __('backend.address'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2',
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
                'class' => 'form-group col-md-2',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
                
        $xaphuong = $this->GetXaNguyenQuan(request()->route('id'));
        
        CRUD::addField([
            'name' => 'ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            //'model' => Parishioners::class,
            'attribute' => 'ward',
            'options'   => $xaphuong,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
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
            'name' => 'noio',
            'type' => 'checkbox',
            'label' => __('backend.noio'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);        
        
        CRUD::addField([
            'name' => 'thongke',
            'type' => 'checkbox',
            'label' => __('backend.thongke'),
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
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'image',
            'type' => 'browse_custom',
            'mimes' => ['image'],
            'label' => __('backend.images'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'        => 'children', // a unique identifier (usually the method that defines the relationship in your Model)
            'label'       => __('backend.children'), // Table column heading
            'type'        => "select_from_ajax_multiple",
            'entity'      => 'children', // the method that defines the relationship in your Model
            'attribute'   => "children", // foreign key attribute that is shown to user
            'data_source' => url("api/Children"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?
            
            // OPTIONAL
            'delay' => 500, // the minimum amount of time between ajax requests when searching in the field
            'model'                => Children::class, // foreign key model
            'placeholder'          => "Chọn thành viên", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'wrapper' => [
                'class' => 'form-group col-md-3 childen-ho',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        CRUD::addField([
            'name' => 'songuoi',
            'type' => 'number',
            'label' => __('backend.songuoi'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1',
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
            'controller' => FamilyController::class,
            'model' => Family::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
        ]);
        
        CRUD::addField([
            'name'  => 'date',
            'type'  => 'date',
            'label' => __('backend.date_marriage'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'attribute'   => 'date',
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'   => __('backend.marriage')
        ]);
        
        CRUD::addField([
            'name' => 'sohonphoi',
            'type' => 'number',
            'label' => __('backend.sohonphoi'),
            'default' => '',
            'attribute'   => 'sohonphoi',
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name' => 'marriage_address',
            'type' => 'text',
            'label' => __('backend.address'),
            'default' => '',
            'attribute'   => 'marriage_address',
            //'model'     => Marriage::class,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        @include(resource_path().'/cities/tinh_thanhpho.php');
        
        CRUD::addField([
            'name' => 'marriage_province',
            'type' => 'select_from_array',
            'label' => __('backend.province'),
            'attribute' => 'marriage_province',
            'options' => $tinh_thanhpho,
            'model'     => Marriage::class,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.marriage'),
        ]);
        
        $xaphuong_marriage = $this->GetXaNguyenMarriage(request()->route('id'));
        
        CRUD::addField([
            'name' => 'marriage_ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            'model'     => Marriage::class,
            'attribute' => 'marriage_ward',
            'options'   => $xaphuong_marriage,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name'                  => 'priest',
            'type'                  => 'select_from_ajax',
            'label'                 => __('backend.priest'),
            'data_source'           => url("api/Priest"),
            'model'                 => Priest::class,
            'delay'                 => 500,
            'placeholder'           => "Chọn linh mục",
            'minimum_input_length'  => 1,
            'attribute'             => "priest",
            'wrapper' => [
                'class'     => 'form-group col-md-2',
            ],
            'tab'                   => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name' => 'peopleone',
            'type' => 'text',
            'label' => __('backend.peopleone'),
            //'model'     => Marriage::class,
            'attribute' => 'peopleone',
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name' => 'peopletwo',
            'type' => 'text',
            'label' => __('backend.peopletwo'),
            //'model'     => Marriage::class,
            'attribute' => 'peopletwo',
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name' => 'tinhtrang',
            'type' => 'select_from_array',
            'label' => __('backend.status'),
            'attribute' => 'tinhtrang',
            'options' => array(
                '1' => 'Hợp pháp',
                '2' => 'Hợp thức hóa',
                '3' => 'Chuẩn',
                '4' => 'Không theo phép đạo',
                '5' => 'Ly thân',
                '6' => 'Ly dị',
                '7' => 'Đã được tháo gỡ',
                '8' => 'Không xác định',
            ),
            //'model'     => Marriage::class,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.marriage'),
        ]);
        
        CRUD::addField([
            'name' => 'marriage_note',
            'type' => 'textarea',
            'attribute' => 'marriage_note',
            'label' => __('backend.note'),
            //'model'     => Marriage::class,
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'tab' => __('backend.marriage'),
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
            $array_deanerys = DB::table('parishs')
            ->Join('deanerys', 'parishs.did', '=', 'deanerys.did')
            ->where('parishs.id', '=', $id)
            ->where('parishs.status', '=', 1)
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
    
    public function GetParish_first($id){
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
    
    public function GetParishs($id){
        
        $array_par = array();
        if(!empty($id)){
            /*$array_parish = DB::table('parishs')
                ->where('id', '=', $id)
                ->where('status', '=', 1)
                ->get()->toArray();*/
            
            $array_parishs = DB::table('family')
            ->Join('parishs', 'family.pid', '=', 'parishs.pid')
            ->where('parishs.status', '=', 1)
            ->get()->toArray();
            
            $array_parishs = json_decode(json_encode($array_parishs, true), true);
            
            foreach($array_parishs as $item){
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
    
    public function GetXaNguyenQuan($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            $parish_managements = DB::table('family')->where('id', $id)->get()->toArray();
            
            $province = $parish_managements[0]->province;

            foreach($xa_phuong_thitran as $xa){
                if($xa['matp'] == $province){
                    $array_xa[$xa['xaid']] = $xa['name'];
                }
            }
        }
        return $array_xa;
    }
        
    public function GetXaNguyenMarriage($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            
            $marriage = DB::table('marriage')->where('idfamily', $id)->get()->toArray();
            if(!empty($marriage)){
                
                $province = $marriage[0]->marriage_province;
                
                foreach($xa_phuong_thitran as $xa){
                    if($xa['matp'] == $province){
                        $array_xa[$xa['xaid']] = $xa['name'];
                    }
                }
            }
        }
        return $array_xa;
    }
}
