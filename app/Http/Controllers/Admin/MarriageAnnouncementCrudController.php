<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MarriageAnnouncementRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\MarriageAnnouncement;
use App\Models\Priest;
use App\Models\Female;
use App\Models\Male;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MarriageAnnouncementController;
use App\Models\Marriage;
use App\Models\Decen;
use App\Models\SetAdmin;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\ParishManagement;
use App\Models\Parish;

/**
 * Class MarriageAnnouncementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MarriageAnnouncementCrudController extends CrudController
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
        CRUD::setModel(\App\Models\MarriageAnnouncement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/marriage-announcement');
        CRUD::setEntityNameStrings(__('backend.marriage_announcement'), __('backend.marriage_announcements'));
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
        //CRUD::addColumn(['name' => 'priest', 'type' => 'text', 'label' => __('backend.priest'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'priest', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.priest'), 'function' => function ($entry) {
            if(!empty($entry->priest)){
                $array_sacrament_givers = DB::table('sacrament_givers')
                ->where('id', $entry->priest)
                ->orderBy('id', 'ASC')
                ->first();
                
                return $array_sacrament_givers->name;
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
        CRUD::addColumn(['name' => 'deanerys', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.deanerys'), 'function' => function ($entry) {
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
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(MarriageAnnouncementRequest::class);

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
            'tab'                   => __('backend.general'),
        ]);
        
        
        CRUD::addField([
            'name'  => 'announcements_one',
            'type'  => 'date',
            'label' => __('backend.announcements_one'),
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
            'tab'   => __('backend.general')
        ]);
        
        
        CRUD::addField([
            'name'  => 'announcements_two',
            'type'  => 'date',
            'label' => __('backend.announcements_two'),
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
            'tab'   => __('backend.general')
        ]);
        
        
        CRUD::addField([
            'name'  => 'announcements_three',
            'type'  => 'date',
            'label' => __('backend.announcements_three'),
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
            'tab'   => __('backend.general')
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
        
        
        // Nữ
        CRUD::addField([
            'name' => 'female',
            'type' => 'select_from_ajax',
            'label' => __('backend.female'),
            'data_source' => url("api/Female"),
            'model'                => Female::class,
            'delay'     => 500,
            'placeholder'          => "Chọn nữ",
            'minimum_input_length'  => 2,
            'attribute'   => "female",
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'       => __('backend.female'),
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
            'name' => 'female_diocese',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'female_diocese',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.female'),
        ]);
        
        $deanerys = $this->GetDeanerys_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_deanery',
            'type' => 'select_from_array',
            'attribute' => 'female_deanery',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.female'),
        ]);
        
        
        $female_parish = $this->GetParish_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishmanagements',
            'type' => 'select_from_array',
            'attribute' => 'female_parishmanagements',
            'options' => $female_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        
        $parishs = $this->GetParishs_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishs',
            'type' => 'select_from_array',
            'attribute' => 'female_parishs',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        CRUD::addField([
            'name'      => 'female_giaoxu',
            'type'      => 'hidden',
            'tab'       => __('backend.female'),
        ]);
        
        CRUD::addField([
            'name'      => 'female_old',
            'type'      => 'text',
            'label' => 'Nguyên quán',
            'tab'       => __('backend.female'),
            'wrapper' => [
                'class' => 'form-group col-md-12 border-top mb-0 pt-3',
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                'class' => 'form-control d-none',
            ]
        ]);
        
        CRUD::addField([
            'name' => 'female_dioceseold',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'female_dioceseold',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        $deanerys = $this->GetDeanerys_Old_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_deaneryold',
            'type' => 'select_from_array',
            'attribute' => 'female_deaneryold',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        $female_parish = $this->GetParish_Old_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishmanagementsold',
            'type' => 'select_from_array',
            'attribute' => 'female_parishmanagementsold',
            'options' => $female_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        $parishs = $this->GetParishs_Old_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishsold',
            'type' => 'select_from_array',
            'attribute' => 'female_parishsold',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        CRUD::addField([
            'name'      => 'female_before',
            'type'      => 'text',
            'label' => 'Trước đây',
            'tab'       => __('backend.female'),
            'wrapper' => [
                'class' => 'form-group col-md-12 border-top mb-0 pt-3',
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                'class' => 'form-control d-none',
            ]
        ]);
        
        CRUD::addField([
            'name' => 'female_diocesebefore',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'female_diocesebefore',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        $deanerys = $this->GetDeanerys_Before_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_deanerybefore',
            'type' => 'select_from_array',
            'attribute' => 'female_deanerybefore',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        
        $female_parish = $this->GetParish_Before_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishmanagementsbefore',
            'type' => 'select_from_array',
            'attribute' => 'female_parishmanagementsbefore',
            'options' => $female_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        
        $parishs = $this->GetParishs_Before_Female(request()->route('id'));
        
        CRUD::addField([
            'name' => 'female_parishsbefore',
            'type' => 'select_from_array',
            'attribute' => 'female_parishsbefore',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.female'),
        ]);
        
        // Nam
        
        CRUD::addField([
            'name' => 'male',
            'type' => 'select_from_ajax',
            'label' => __('backend.male'),
            'data_source' => url("api/Male"),
            'model'                => Male::class,
            'delay' => 500,
            'placeholder'          => "Chọn Nam",
            'minimum_input_length'  => 2,
            'attribute'   => "male",
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
            'tab' => __('backend.male'),
        ]);
        
        CRUD::addField([
            'name'      => 'male_diocese',
            'type'      => 'select_from_array',
            'label'     => __('backend.diocese'),
            'attribute' => 'male_diocese',
            'options'   => $array_dio,
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'       => __('backend.male'),
        ]);
        
        $deanerys = $this->GetDeanerys_Male(request()->route('id'));
        
        CRUD::addField([
            'name'      => 'male_deanery',
            'type'      => 'select_from_array',
            'attribute' => 'male_deanery',
            'options'   => $deanerys,
            'label'     => __('backend.deanerys'),
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'tab'       => __('backend.male'),
        ]);
        
        
        $parish = $this->GetParish_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishmanagements',
            'type' => 'select_from_array',
            'attribute' => 'male_parishmanagements',
            'options' => $parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        $parishs = $this->GetParishs_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishs',
            'type' => 'select_from_array',
            'attribute' => 'male_parishs',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        CRUD::addField([
            'name' => 'male_giaoxu',
            'type' => 'hidden',
            'tab' => __('backend.male'),
        ]);
        
        CRUD::addField([
            'name'      => 'male_old',
            'type'      => 'text',
            'label' => 'Nguyên quán',
            'tab'       => __('backend.male'),
            'wrapper' => [
                'class' => 'form-group col-md-12 border-top mb-0 pt-3',
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                'class' => 'form-control d-none',
            ]
        ]);
        
        CRUD::addField([
            'name' => 'male_dioceseold',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'male_dioceseold',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        $deanerys = $this->GetDeanerys_Old_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_deaneryold',
            'type' => 'select_from_array',
            'attribute' => 'male_deaneryold',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        $male_parish = $this->GetParish_Old_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishmanagementsold',
            'type' => 'select_from_array',
            'attribute' => 'male_parishmanagementsold',
            'options' => $male_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        $parishs = $this->GetParishs_Old_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishsold',
            'type' => 'select_from_array',
            'attribute' => 'male_parishsold',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        CRUD::addField([
            'name'      => 'male_before',
            'type'      => 'text',
            'label' => 'Trước đây',
            'tab'       => __('backend.male'),
            'wrapper' => [
                'class' => 'form-group col-md-12 border-top mb-0 pt-3',
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                'class' => 'form-control d-none',
            ]
        ]);
        
        CRUD::addField([
            'name' => 'male_diocesebefore',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'male_diocesebefore',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        $deanerys = $this->GetDeanerys_Before_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_deanerybefore',
            'type' => 'select_from_array',
            'attribute' => 'male_deanerybefore',
            'options' => $deanerys,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        
        $male_parish = $this->GetParish_Before_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishmanagementsbefore',
            'type' => 'select_from_array',
            'attribute' => 'male_parishmanagementsbefore',
            'options' => $male_parish,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        
        $parishs = $this->GetParishs_Before_Male(request()->route('id'));
        
        CRUD::addField([
            'name' => 'male_parishsbefore',
            'type' => 'select_from_array',
            'attribute' => 'male_parishsbefore',
            'options' => $parishs,
            'label' => __('backend.parish'),
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
            'tab' => __('backend.male'),
        ]);
        
        // Slug
        CRUD::addfield([
            'name' => 'slug',
            'type' => 'slug',
            'source' => 'name',
            'controller' => MarriageAnnouncementController::class,
            'model' => MarriageAnnouncement::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            'tab' => 'SEO',
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
		
        <script type="text/javascript">
			jQuery(document).ready(function($){                
                $("select[name='female_diocese']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='female_deanery'] option[value]").remove();
                            $("select[name='female_parishmanagements'] option[value]").remove();
                            $("select[name='female_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_deanery']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_deanery']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_deanery']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='female_parishmanagements'] option[value]").remove();      
                            $("select[name='female_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishmanagements']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishmanagements']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_parishmanagements']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='female_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishs']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishs']").append(
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
				$("select[name='female_parishmanagements']").change(function() {
                    var $option = $(this).find('option:selected');
                    var giaoxu = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $('input[name="female_giaoxu"]').val(giaoxu).trigger('change');
                });
                
                var selectgiaoxu = $('select[name="female_parishmanagements"]').find(":selected").val();
                if(selectgiaoxu != ''){
                	$('input[name="female_giaoxu"]').val(selectgiaoxu).trigger('change');
                }
			});
		</script>
		
		<script type="text/javascript">
			jQuery(document).ready(function($){                
                $("select[name='male_diocese']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='male_deanery'] option[value]").remove();
                            $("select[name='male_parishmanagements'] option[value]").remove();
                            $("select[name='male_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_deanery']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_deanery']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_deanery']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='male_parishmanagements'] option[value]").remove();      
                            $("select[name='male_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishmanagements']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishmanagements']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_parishmanagements']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='male_parishs'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishs']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishs']").append(
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
				$("select[name='male_parishmanagements']").change(function() {
                    var $option = $(this).find('option:selected');
                    var giaoxu = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $('input[name="male_giaoxu"]').val(giaoxu).trigger('change');
                });
                
                var selectgiaoxunam = $('select[name="male_parishmanagements"]').find(":selected").val();
                if(selectgiaoxunam == ''){
                	$('input[name="male_giaoxu"]').val(selectgiaoxu).trigger('change');
                }
			});
		</script>
		
		
		<script type="text/javascript">
			jQuery(document).ready(function($){                
                $("select[name='female_dioceseold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='female_deaneryold'] option[value]").remove();
                            $("select[name='female_parishmanagementsold'] option[value]").remove();
                            $("select[name='female_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_deaneryold']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_deaneryold']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_deaneryold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='female_parishmanagementsold'] option[value]").remove();      
                            $("select[name='female_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishmanagementsold']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishmanagementsold']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_parishmanagementsold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='female_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishsold']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishsold']").append(
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
                $("select[name='female_diocesebefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='female_deanerybefore'] option[value]").remove();
                            $("select[name='female_parishmanagementsbefore'] option[value]").remove();
                            $("select[name='female_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_deanerybefore']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_deanerybefore']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_deanerybefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='female_parishmanagementsbefore'] option[value]").remove();      
                            $("select[name='female_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishmanagementsbefore']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishmanagementsbefore']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='female_parishmanagementsbefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='female_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='female_parishsbefore']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='female_parishsbefore']").append(
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
                $("select[name='male_dioceseold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='male_deaneryold'] option[value]").remove();
                            $("select[name='male_parishmanagementsold'] option[value]").remove();
                            $("select[name='male_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_deaneryold']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_deaneryold']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_deaneryold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {female_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='male_parishmanagementsold'] option[value]").remove();      
                            $("select[name='male_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishmanagementsold']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishmanagementsold']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_parishmanagementsold']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='male_parishsold'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishsold']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishsold']").append(
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
                $("select[name='male_diocesebefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var diocese = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_diocese:diocese},
                        beforeSend: function(){
                            $("select[name='male_deanerybefore'] option[value]").remove();
                            $("select[name='male_parishmanagementsbefore'] option[value]").remove();
                            $("select[name='male_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_deanerybefore']").append('<option value="">-- Chọn giáo hạt --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_deanerybefore']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_deanerybefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var deanery = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_deanery:deanery},
                        beforeSend: function(){
                            $("select[name='male_parishmanagementsbefore'] option[value]").remove();      
                            $("select[name='male_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishmanagementsbefore']").append('<option value="">-- Chọn giáo xứ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishmanagementsbefore']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='male_parishmanagementsbefore']").change(function() {
                    var $option = $(this).find('option:selected');
                    var parish_management = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/MarriageAnnouncement",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {male_parish_management:parish_management},
                        beforeSend: function(){
                            $("select[name='male_parishsbefore'] option[value]").remove();
                        },
                        success: function(data) {
                        	$("select[name='male_parishsbefore']").append('<option value="">-- Chọn giáo họ --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='male_parishsbefore']").append(
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
    
    
    public function GetDeanerys_Female($id){
        $array_dea = array();
        if(!empty($id)){            
            $array_deanerys = DB::table('marriage_announcements_parishioners')
            ->Join('deanerys', 'marriage_announcements_parishioners.dioceses', '=', 'deanerys.did')
            ->where('deanerys.status', '=', 1)
            ->where('marriage_announcements_parishioners.sex', '=', 0)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetDeanerys_Old_Female($id){
        $array_dea = array();
        if(!empty($id)){            
            $array_deanerys = DB::table('marriage_announcements_parishioners')
            ->Join('deanerys', 'marriage_announcements_parishioners.diocesesold', '=', 'deanerys.did')
            ->where('deanerys.status', '=', 1)
            ->where('marriage_announcements_parishioners.sex', '=', 0)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetDeanerys_Before_Female($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('marriage_announcements_parishioners')
            ->Join('deanerys', 'marriage_announcements_parishioners.diocesesbefore', '=', 'deanerys.did')
            ->where('deanerys.status', '=', 1)
            ->where('marriage_announcements_parishioners.sex', '=', 0)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetParish_Female($id){        
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerys)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParish_Old_Female($id){
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerysold)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParish_Before_Female($id){
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerysbefore)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParishs_Female($id){
        
        $array_par = array();
        if(!empty($id)){
            
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagements)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    
    public function GetParishs_Old_Female($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagementsold)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    public function GetParishs_Before_Female($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagementsbefore)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    public function GetDeanerys_Male($id){
        $array_dea = array();
        if(!empty($id)){
            $array_giaohat = DB::table('marriage_announcements_parishioners')
                ->where('idannouncement', '=', $id)
                ->where('sex', '=', 1)
                ->where('status', '=', 1)
                ->get()->first();
            
            if(!empty($array_giaohat)){
                $array_deanerys = Deanery::where('did', $array_giaohat->dioceses)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetDeanerys_Old_Male($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('marriage_announcements_parishioners')
            ->Join('deanerys', 'marriage_announcements_parishioners.diocesesold', '=', 'deanerys.did')
            ->where('deanerys.status', '=', 1)
            ->where('marriage_announcements_parishioners.sex', '=', 1)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetDeanerys_Before_Male($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('marriage_announcements_parishioners')
            ->Join('deanerys', 'marriage_announcements_parishioners.diocesesbefore', '=', 'deanerys.did')
            ->where('deanerys.status', '=', 1)
            ->where('marriage_announcements_parishioners.sex', '=', 1)
            ->get()->toArray();
            
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            foreach($array_deanerys as $item){
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
    
    public function GetParish_Male($id){
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 1)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerys)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParish_Old_Male($id){
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 1)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerysold)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetParish_Before_Male($id){
        $array_par = array();
        if(!empty($id)){
            $array_giaoho = MarriageAnnouncementParishioners::where('idannouncement', $id)
            ->where('sex', '=', 0)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_giaoho)){
                $array_parish = ParishManagement::where('deanerys', $array_giaoho->deanerysbefore)
                ->where('status', 1)
                ->get()->toArray();
            }
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
       
    public function GetParishs_Male($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 1)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagements)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    public function GetParishs_Old_Male($id){
        
        $array_par = array();
        if(!empty($id)){            
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 1)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagementsold)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    public function GetParishs_Before_Male($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = MarriageAnnouncementParishioners::where('idannouncement', '=', $id)
            ->where('sex', '=', 1)
            ->where('status', '=', 1)
            ->get()->first();
            
            if(!empty($array_parish)){
                $array_parish = Parish::where('pid', $array_parish->parishmanagementsbefore)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish = json_decode(json_encode($array_parish, true), true);
                
                foreach($array_parish as $item){
                    $array_par[$item['id']] = $item['name'];
                }
            }
        }
        return $array_par;
    }
    
    public function GetDeanerys($id){
        $array_dea = array();
        if(!empty($id)){
            $parishioners = MarriageAnnouncement::select('id', 'did', 'deid', 'pid', 'name')
                ->where('id', '=', $id)
                ->where('status', 1)
                ->get()->first();
                
            $array_deanerys = DB::table('dioceses')
                ->Join('deanerys', 'dioceses.id', '=', 'deanerys.did')
                ->where('dioceses.id', '=', $parishioners->did)
                ->where('deanerys.status', '=', 1)
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
            $parishioners = MarriageAnnouncement::select('id', 'did', 'deid', 'pid', 'name')
                ->where('id', '=', $id)
                ->where('status', 1)
                ->get()->first();
                
            $array_parish = DB::table('parish_managements')
                ->where('diocese', '=', $parishioners->did)
                ->where('deanerys', '=', $parishioners->deid)
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
