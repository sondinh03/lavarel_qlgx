<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Http\Controllers\StudentController;
use App\Models\Lop;
use App\Models\Parishioners;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\SetAdmin;
use App\Models\Parish;

/**
 * Class StudentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Student::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/student');
        CRUD::setEntityNameStrings(__('backend.student'), __('backend.students'));
        
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
                }else{
                }
            }
            $this->crud->orderBy('name', 'asc');
        }
        //$this->crud->orderBy('name', 'asc');
        
        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
        CRUD::addColumn(['name' => 'holy', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.holymanagement'), 'function' => function ($entry) {
            if(!empty($entry->holy)){
                $array_holy = DB::table('holymanagements')
                ->where('id', $entry->holy)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_holy->name;
            }
        }]);
        CRUD::addColumn(['name' => 'last_name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.last_name'), 'limit' => 255]);
        
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.first_name'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'last_name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.lop'), 'limit' => 255]);
        
        /*
        CRUD::addColumn(['name' => 'lop', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.lop'), 'function' => function ($entry) {
            if(!empty($entry->lop)){
                $lop = Lop::where('id', $entry->lop)
                ->orderBy('id', 'ASC')
                ->first();
                return $lop->name;
            }
        }]);
        */
        CRUD::addColumn(['name' => 'paid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.parish'), 'function' => function ($entry) {
            if(!empty($entry->paid)){
                $parish = Parish::where('status', '1')
                ->where('id', $entry->paid)
                ->orderBy('id', 'ASC')
                ->first();
                if(!empty($parish->name)){
                    return $parish->name;
                }else{
                    return '';
                }
            }
        }]);
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
        CRUD::setValidation(StudentRequest::class);

        

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
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-right-0',
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
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'      => 'giaoxu',
            'type'      => 'hidden',
            'tab'       => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'                  => 'lop',
            'type'                  => 'select_from_lop_ajax',
            'entity'                => false, // the method that defines the relationship in your Model
            'attribute'             => "lop", // foreign key attribute that is shown to user
            'data_source'           => url("api/Student"), // url to controller search function (with /{id} should return model)
            //             /'pivot'         => false,
            'placeholder'           => "Chọn lớp",
            'minimum_input_length'  => 1,
            'model'                 => Lop::class,
            'label'                 => __('backend.lop'),
            'wrapper'               => [
                'class'             => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0',
            ],
            'tab'                   => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'                      => 'chon_student',
            'type'                      => 'select_from_student_ajax',
            'entity'                    => false, // the method that defines the relationship in your Model
            'attribute'                 => "chon_student", // foreign key attribute that is shown to user
            'data_source'               => url("api/Students"), // url to controller search function (with /{id} should return model)
            //             /'pivot'         => false,
            'placeholder'               => "Chọn giáo dân",
            'minimum_input_length'      => 1,
            'model'                     => Student::class,
            'label'                     => __('backend.chon_student'),
            'wrapper'                   => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-left-0',
            ],
            'tab'                       => __('backend.general'),
        ]);
        
        $array_holy = DB::table('holymanagements')
        ->orderBy('id', 'ASC')
        ->get()
        ->toArray();
        $array_holy = array_values($array_holy);
        $array_holy = json_decode(json_encode($array_holy, true), true);
        $array_ho = array();
        foreach($array_holy as $item){
            $array_ho[$item['id']] = $item['name'];
        }
        CRUD::addField([
            'name' => 'holy',
            'type' => 'select_from_array',
            'label' => __('backend.holymanagement'),
            'attribute' => 'holy',
            'options' => $array_ho,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'last_name',
            'type' => 'text',
            'label' => __('backend.last_name'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.fullname'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'sex',
            'type' => 'radio',
            'label' => __('backend.status'),
            'options' => [
                0 => __('backend.female'),
                1 => __('backend.male')
            ],
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-bottom-0 border-left-0',
            ],
            'default'   => 0,
            'inline' => true,
            'tab' => __('backend.general')
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-top-0',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name' => 'mahv',
            'type' => 'text',
            'label' => __('backend.mahv'),
            'attributes' => [
                'placeholder' => __('backend.mahv'),
                'readonly'    => 'readonly',
                'disabled'    => 'disabled',
            ], // change the HTML attributes of your input
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'magd',
            'type' => 'text',
            'label' => __('backend.magd'),
            'attributes' => [
                'placeholder' => __('backend.magd'),
                'readonly'    => 'readonly',
                'disabled'    => 'disabled',
            ], // change the HTML attributes of your input
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'magdcg',
            'type' => 'text',
            'label' => __('backend.magdcg'),
            'attributes' => [
                'placeholder' => __('backend.magdcg'),
                'readonly'    => 'readonly',
                'disabled'    => 'disabled',
            ], // change the HTML attributes of your input
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'father',
            'type' => 'text',
            'label' => __('backend.father'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'mother',
            'type' => 'text',
            'label' => __('backend.mother'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-bottom-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'cccd',
            'type' => 'text',
            'label' => __('backend.cccd'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'phone',
            'type' => 'number',
            'label' => __('backend.phone'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'email',
            'type' => 'email',
            'label' => __('backend.email'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'origin',
            'type' => 'text',
            'label' => __('backend.address'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-left-0',
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-left-0',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
        
        $xaphuong = $this->GetXa(request()->route('id'));
        
        CRUD::addField([
            'name' => 'ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            'model' => Student::class,
            'attribute' => 'ward',
            'options'   => $xaphuong,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-top-0 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        // rua toi
        CRUD::addField([
            'name' => 'baptism_date',
            'type'  => 'date_picker',
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'label' => __('backend.baptism_date'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'baptism_number',
            'type' => 'number',
            'label' => __('backend.baptism_number'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-danger py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_sacrament_givers = DB::table('sacrament_givers')
        ->orderBy('id', 'ASC')
        ->get()
        ->toArray();
        $array_sacrament_givers = array_values($array_sacrament_givers);
        $array_sacrament_givers = json_decode(json_encode($array_sacrament_givers, true), true);
        $array_sacrament = array(
            '' => '-----',
        );
        foreach($array_sacrament_givers as $item){
            $array_sacrament[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'baptism_giver',
            'type' => 'select_from_array',
            'label' => __('backend.baptism_giver'),
            'default' => '',
            'options'   => $array_sacrament,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_sponsors = DB::table('sponsors')
        ->orderBy('id', 'ASC')
        ->get()
        ->toArray();
        $array_sponsors = array_values($array_sponsors);
        $array_sponsors = json_decode(json_encode($array_sponsors, true), true);
        $array_spon = array(
            '' => '-----',
        );
        foreach($array_sponsors as $item){
            $array_spon[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'baptism_sponsor',
            'type' => 'select_from_array',
            'label' => __('backend.baptism_sponsor'),
            'default' => '',
            'options'   => $array_spon,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0',
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
            'name' => 'baptism_dioceses',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'baptism_dioceses',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-danger py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $deanerys_baptism = $this->GetDeanerys_Baptism(request()->route('id'));
        
        CRUD::addField([
            'name' => 'baptism_deanerys',
            'type' => 'select_from_array',
            'attribute' => 'baptism_deanerys',
            'options' => $deanerys_baptism,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $parish_baptism = $this->GetParish_Baptism(request()->route('id'));
        
        CRUD::addField([
            'name' => 'baptism_parish',
            'type' => 'select_from_array',
            'attribute' => 'baptism_parish',
            'options' => $parish_baptism,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        // them suc
        CRUD::addField([
            'name' => 'more_power_date',
            'type'  => 'date_picker',
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'label' => __('backend.more_power_date'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'more_power_number',
            'type' => 'number',
            'label' => __('backend.more_power_number'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'more_power_giver',
            'type' => 'select_from_array',
            'options'   => $array_sacrament,
            'label' => __('backend.more_power_giver'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'more_power_sponsor',
            'type' => 'select_from_array',
            'options'   => $array_spon,
            'label' => __('backend.more_power_sponsor'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'more_power_dioceses',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'more_power_dioceses',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $deanerys_morepower = $this->GetDeanerys_morepower(request()->route('id'));
        
        CRUD::addField([
            'name' => 'more_power_deanerys',
            'type' => 'select_from_array',
            'attribute' => 'more_power_deanerys',
            'options' => $deanerys_morepower,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $parish_morepower = $this->GetParish_morepower(request()->route('id'));
        
        CRUD::addField([
            'name' => 'more_power_parish',
            'type' => 'select_from_array',
            'attribute' => 'more_power_parish',
            'options' => $parish_morepower,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]); 
        
        CRUD::addField([
            'name' => 'promise_day',
            'type'  => 'date_picker',
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'label' => __('backend.promise_day'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'note',
            'type' => 'text',
            'label' => __('backend.note'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-info py-2 border-left-0',
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
            'controller' => StudentController::class,
            'model' => Student::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
        ]);
        
        ?>
        <style type="text/css">
            .border-primary{
                border-color: #7c69ef;
                border-width: 3px;
                border-style: solid;
            }
            .border-success{
                border-color: #42ba96;
                border-width: 3px;
                border-style: solid;
            }
            .border-danger{
                border-color: #df4759;
                border-width: 3px;
                border-style: solid;
            }
            .border-warning{
                bborder-color: #ffc107;
                border-width: 3px;
                border-style: solid;
            }            
            .border-info{
                border-color: #467fd0;
                border-width: 3px;
                border-style: solid;
            }            
            .border-dark{
                border-color: #161c2d;
                border-width: 3px;
                border-style: solid;
            }
            .border-secondary{
                border-width: 3px;
                border-style: solid;
            }
        </style>
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
                $("select[name='baptism_dioceses']").change(function() {
                    var $option = $(this).find('option:selected');
                    var baptism_dioceses = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Student",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {baptism_dioceses:baptism_dioceses},
                        beforeSend: function(){
                            $("select[name='baptism_deanerys'] option[value]").remove();
                            $("select[name='baptism_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='baptism_deanerys']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='baptism_deanerys']").change(function() {
                    var $option = $(this).find('option:selected');
                    var baptism_deanerys = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Student",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {baptism_deanerys:baptism_deanerys},
                        beforeSend: function(){
                            $("select[name='baptism_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='baptism_parish']").append(
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
                $("select[name='more_power_dioceses']").change(function() {
                    var $option = $(this).find('option:selected');
                    var more_power_dioceses = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Student",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {more_power_dioceses:more_power_dioceses},
                        beforeSend: function(){
                            $("select[name='more_power_deanerys'] option[value]").remove();
                            $("select[name='more_power_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='more_power_deanerys']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='more_power_deanerys']").change(function() {
                    var $option = $(this).find('option:selected');
                    var more_power_deanerys = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Student",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {more_power_deanerys:more_power_deanerys},
                        beforeSend: function(){
                            $("select[name='more_power_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='more_power_parish']").append(
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
				$("select[name='pid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var giaoxu = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $('input[name="giaoxu"]').val(giaoxu).trigger('change');
                });
                
                var selectgiaoxu = $('select[name="pid"]').find(":selected").val();
                if(selectgiaoxu == ''){
                	$('input[name="giaoxu"]').val(selectgiaoxu).trigger('change');
                }else{
                $('input[name="giaoxu"]').val(selectgiaoxu);
                }
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
    			$('#select2-chon_student-bt-container').on('select2:select', function (e) {
                  // Do something
                  alert('ok');
                });
                $('select[name="chon_student"]').on('change', function (e) {
                    var optionSelected = $("option:selected", this);
                    var valueSelected = this.value;
					alert('ok');
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
            $student = DB::table('student')
            ->where('id', '=', $id)
            ->get()->first();

            $array_deanerys = DB::table('deanerys')
            ->where('did', '=', $student->did)
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
            $student = DB::table('student')
            ->where('id', '=', $id)
            ->get()->first();
            
            $array_parish = DB::table('parishs')
            ->where('did', '=', $student->did)
            ->where('deid', '=', $student->deid)
            ->where('pid', '=', $student->pid)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetXa($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            $parish_managements = DB::table('student')->where('id', $id)->get()->toArray();
            
            $province = $parish_managements[0]->province;
            
            foreach($xa_phuong_thitran as $xa){
                if($xa['matp'] == $province){
                    $array_xa[$xa['xaid']] = $xa['name'];
                }
            }
        }
        return $array_xa;
    }
    
    public function GetDeanerys_Baptism($id){
        $array_dea = array(
            '' => '-----'
        );
        if(!empty($id)){
            $array_deanerys = DB::table('deanerys')
            ->join('dioceses', 'deanerys.did', '=', 'dioceses.id')
            ->join('student', 'dioceses.id', '=', 'student.baptism_dioceses')
            ->select('deanerys.*')
            ->where('student.id', '=', $id)
            ->get()->toArray();;
            
            if(!empty($array_deanerys)){
                $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
                foreach($array_deanerys as $item){
                    $array_dea[$item['id']] = $item['name'];
                }
            }
        }
        return $array_dea;
    }
    
    
    public function GetParish_Baptism($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('parish_managements')
            ->join('deanerys', 'parish_managements.deanerys', '=', 'deanerys.id')
            ->join('student', 'deanerys.id', '=', 'student.baptism_deanerys')
            ->select('parish_managements.*')
            ->where('student.id', '=', $id)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    
    public function GetDeanerys_morepower($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('deanerys')
            ->join('dioceses', 'deanerys.did', '=', 'dioceses.id')
            ->join('student', 'dioceses.id', '=', 'student.more_power_dioceses')
            ->select('deanerys.*')
            ->where('student.id', '=', $id)
            ->get()->toArray();;
            
            if(!empty($array_deanerys)){
                $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
                foreach($array_deanerys as $item){
                    $array_dea[$item['id']] = $item['name'];
                }
            }
        }
        return $array_dea;
    }
    
    
    public function GetParish_morepower($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('parish_managements')
            ->join('deanerys', 'parish_managements.deanerys', '=', 'deanerys.id')
            ->join('student', 'deanerys.id', '=', 'student.more_power_deanerys')
            ->select('parish_managements.*')
            ->where('student.id', '=', $id)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
}
