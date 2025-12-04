<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LopRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Lop;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Block;
use App\Models\Decen;
use Illuminate\Support\Facades\Auth;
use App\Models\SetAdmin;
use App\Models\NamHoc;

/**
 * Class LopCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LopCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Lop::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lop');
        CRUD::setEntityNameStrings(__('backend.lop'), __('backend.lops'));

        //if (Auth::check()){
        $user = backpack_user();
        if (!empty($user->id)) {
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
            if (!empty($setadmin)) {
                CRUD::allowAccess('create');
                CRUD::allowAccess('delete');
                CRUD::allowAccess('update');
                CRUD::allowAccess('show');
            } else {
                $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                if (empty($decen->parish)) {
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
        if (! $this->crud->getRequest()->has('order')) {
            //$this->crud->orderBy('updated_at', 'desc');
            $user = backpack_user();
            if (!empty($user->id)) {
                $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
                if (empty($setadmin)) {
                    $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                    if (!empty($decen->student)) {
                        CRUD::addClause('where', 'pid', $decen->pid);
                    } else {
                        CRUD::addClause('where', 'pid', 0);
                    }
                }
            }
        }

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'block', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.block'), 'function' => function ($entry) {
            if (!empty($entry->block)) {
                $block = Block::where('id', $entry->block)->where('status', 1)->get()->first();
                $namhoc = NamHoc::where('id', $block->namhoc)->where('status', 1)->get()->first();
                return $block->name . ' (' . $namhoc->name . ')';
            }
        }]);
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.parish_managements'), 'function' => function ($entry) {
            if (!empty($entry->pid)) {
                $array_parish = DB::table('parish_managements')
                    ->where('status', '1')
                    ->where('id', $entry->pid)
                    ->orderBy('id', 'ASC')
                    ->first();
                return $array_parish->name;
            }
        }]);
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
            if (!empty($entry->deid)) {
                $array_deanerys = DB::table('deanerys')
                    ->where('status', '1')
                    ->where('id', $entry->deid)
                    ->orderBy('id', 'ASC')
                    ->first();
                return $array_deanerys->name;
            }
        }]);
        CRUD::addColumn(['name' => 'diocese', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.diocese'), 'function' => function ($entry) {
            if (!empty($entry->did)) {
                $array_diocese = DB::table('dioceses')
                    ->where('status', '1')
                    ->where('id', $entry->did)
                    ->orderBy('id', 'ASC')
                    ->first();
                return $array_diocese->name;
            }
        }]);
        //CRUD::addColumn(['name' => 'schoolyear', 'type' => 'text', 'orderable' => false, 'label' => __('backend.schoolyear'), 'limit' => 255]);
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
        CRUD::setValidation(LopRequest::class);

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
        foreach ($array_diocese as $item) {
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

        $array_dea = array();

        foreach ($array_deanerys as $key => $item) {
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

        $array_parish = array();
        foreach ($parish as $key => $item) {
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

        CRUD::addField([
            'name'      => 'giaoxu',
            'type'      => 'hidden',
            'tab'       => __('backend.general'),
        ]);

        CRUD::addField([
            'name'                      => 'block',
            'type'                      => 'select_from_lop_ajax',
            'entity'                    => false, // the method that defines the relationship in your Model
            'attribute'                 => "block", // foreign key attribute that is shown to user
            'data_source'               => url("api/Blocks"), // url to controller search function (with /{id} should return model)
            //             /'pivot'         => false,
            'placeholder'               => "Chọn khối",
            'minimum_input_length'      => 1,
            'model'                     => Block::class,
            'label'                     => __('backend.Block'),
            'wrapper'                   => [
                'class' => 'form-group col-md-3',
            ],
            'tab'                       => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.lop'),
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.general'),
        ]);
        /*
        CRUD::addField([
            'name'                      => 'schoolyear',
            'type'                      => 'select_from_lop_ajax',
            'entity'                    => false, // the method that defines the relationship in your Model
            'attribute'                 => "schoolyear", // foreign key attribute that is shown to user
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
        */
        CRUD::addField([
            'name'       => 'start_date_one,end_date_one', // two columns with a comma
            'type'       => 'date_range_array',
            'start_name' => 'start_date_one', // the db column that holds the start_date
            'end_name' => 'end_date_one', // the db column that holds the end_date
            //'default'   => 'start_name', 'end_name',
            'start_default' => Carbon::now()->format('Y-m-d'), // default value for start_date
            'end_default' => Carbon::now()->format('Y-m-d'), // default value for end_date
            'label' => __('backend.school_year_one'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.general'),
        ]);


        CRUD::addField([
            'name'       => 'start_date_two,end_date_two', // two columns with a comma
            'start_name' => 'start_date_two', // the db column that holds the start_date
            'end_name' => 'end_date_two', // the db column that holds the end_date
            'type'       => 'date_range_array',
            'label'     => __('backend.school_year_two'),
            'wrapper'   => [
                'class' => 'form-group col-md-3',
            ],
            'tab'       => __('backend.general'),
        ]);

        CRUD::addField([
            'name'      => 'symbol',
            'type'      => 'text',
            'label'     => __('backend.symbol'),
            'default'   => '',
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'       => __('backend.general'),
        ]);

        CRUD::addField([
            'name'        => 'teacher', // a unique identifier (usually the method that defines the relationship in your Model)
            'label'       => __('backend.teachers'), // Table column heading
            'type'        => "select_from_ajax_multiple",
            'entity'      => false, // the method that defines the relationship in your Model
            'attribute'   => "teacher", // foreign key attribute that is shown to user
            'data_source' => url("api/Lop"), // url to controller search function (with /{id} should return model)
            //'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
            'model'                 => Teacher::class, // foreign key model
            'placeholder'           => "Chọn giáo viên", // placeholder for the select
            'minimum_input_length'  => 1, // minimum characters to type before querying results
            //'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => __('backend.general'),
        ]);

        // CRUD::addField([
        //     'name'        => 'teacher',
        //     'label'       => __('backend.teachers'),
        //     'type'        => 'select_from_ajax_multiple',
        //     'attribute'   => 'name',      // tên trường hiển thị từ model Teacher
        //     'data_source' => url("api/Lop"), // url trả về JSON teacher
        //     'model'       => Teacher::class,
        //     'entity'      => null,        // không dùng relation
        //     'pivot'       => false,       // vì không có bảng pivot
        //     'wrapper' => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'tab' => __('backend.general'),
        // ]);

        CRUD::addField([
            'name'      => 'note',
            'type'      => 'text',
            'label'     => __('backend.note'),
            'default'   => '',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
            'tab'       => __('backend.general'),
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
            'controller' => LopController::class,
            'model' => Lop::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
        ]);

?>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("select[name='did']").change(function() {
                    var $option = $(this).find('option:selected');
                    var did = $option.val(); //to get content of "value" attrib
                    var text = $option.text(); //to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Family",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            did: did
                        },
                        beforeSend: function() {
                            $("select[name='deid'] option[value]").remove();
                            $("select[name='pid'] option[value]").remove();
                        },
                        success: function(data) {
                            $("select[name='deid']").append('<option value="">-- Chọn giáo hạt --</option>');
                            $.each(data, function(key, value) {
                                $("select[name='deid']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });

                $("select[name='deid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deid = $option.val(); //to get content of "value" attrib
                    var text = $option.text(); //to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Family",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            deid: deid
                        },
                        beforeSend: function() {
                            $("select[name='pid'] option[value]").remove();
                        },
                        success: function(data) {
                            $("select[name='pid']").append('<option value="">-- Chọn giáo xứ --</option>');
                            $.each(data, function(key, value) {
                                $("select[name='pid']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
            });
        </script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("select[name='pid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var giaoxu = $option.val(); //to get content of "value" attrib
                    var text = $option.text(); //to get <option>Text</option> content
                    $('input[name="giaoxu"]').val(giaoxu).trigger('change');
                });

                var selectgiaoxu = $('select[name="pid"]').find(":selected").val();
                if (selectgiaoxu == '') {
                    $('input[name="giaoxu"]').val(selectgiaoxu).trigger('change');
                } else {
                    $('input[name="giaoxu"]').val(selectgiaoxu);
                }
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

    public function GetDeanerys($id)
    {
        $array_dea = array();
        if (!empty($id)) {
            $teacher = DB::table('lop')
                ->where('id', '=', $id)
                ->get()->first();

            $array_deanerys = DB::table('deanerys')
                ->where('did', '=', $teacher->did)
                ->get()->toArray();

            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach ($array_deanerys as $item) {
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }

    public function GetParish($id)
    {
        $array_par = array();
        if (!empty($id)) {
            $teacher = DB::table('lop')
                ->where('id', '=', $id)
                ->get()->first();

            $array_parish = DB::table('parish_managements')
                ->where('diocese', '=', $teacher->did)
                ->where('deanerys', '=', $teacher->deid)
                ->get()->toArray();

            $array_parish = json_decode(json_encode($array_parish, true), true);

            foreach ($array_parish as $item) {
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
}
