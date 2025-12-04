<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishionersRequest;
use App\Http\Controllers\ParishionersController;
use App\Models\Parishioners;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Backpack\ReviseOperation\ReviseOperation;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\Holymanagement;
use App\Models\SetAdmin;
use App\Models\Parish;

/**
 * Class ParishionersCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ParishionersCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Parishioners::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parishioners');
        CRUD::setEntityNameStrings(__('backend.parishioners'), __('backend.parishioners'));
        
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
            //$user = Auth::user();
            $user = backpack_user();            
            //if(!empty($user->id) AND $user->id > 1){
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
            $this->crud->orderBy('name', 'asc');
        }
        
        //$this->crud->removeAllButtonsFromStack('line');
        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
        CRUD::addColumn(['name' => 'holy', 'type' => 'closure', 'label' => __('backend.holymanagement'), 'function' => function ($entry) {
            if(!empty($entry->holy)){
                $array_holy = Holymanagement::where('id', $entry->holy)
                ->orderBy('id', 'ASC')
                ->get()
                ->first();
                return $array_holy->name;
            }
        }]);
            
        CRUD::addColumn(['name' => 'last_name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.last_name'), 'limit' => 255]);
        
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'orderable' => false, 'label' => __('backend.first_name'), 'limit' => 255]);
        
        //CRUD::addColumn(['name' => 'name', 'type' => 'text', 'label' => __('backend.fullname'), 'limit' => 255]);
        CRUD::addColumn(['name' => 'sex', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.sex'), 'function' => function ($entry) {
            if ($entry->sex == 0) {
                return __('backend.female');
            } else {
                return __('backend.male');
            }
        }]);
        CRUD::addColumn(['name' => 'birthday', 'type' => 'closure','orderable' => false, 'label' => __('backend.birthday'), 'function' => function ($entry) {
            if ($entry->birthday != 0) {
                $newDate = date("d-m-Y", strtotime($entry->birthday));
                return $newDate;
            } else {
                return '';
            }
        }]);
        CRUD::addColumn(['name' => 'phone', 'type' => 'text', 'orderable' => false, 'label' => __('backend.phone')]);
        CRUD::addColumn(['name' => 'paid', 'type' => 'closure', 'orderable' => false, 'label' => __('backend.parish'), 'function' => function ($entry) {
            if(!empty($entry->paid)){
                $array_parish = Parish::where('status', '1')
                ->where('id', $entry->paid)
                ->orderBy('id', 'ASC')
                ->first();
                return $array_parish->name;
            }
        }]);
        CRUD::addColumn(['name' => 'pid', 'type' => 'closure',  'orderable' => false, 'label' => __('backend.parish_management'), 'function' => function ($entry) {
            if(!empty($entry->pid)){
                $array_parish_managements = DB::table('parish_managements')
                    ->where('status', '1')
                    ->where('id', $entry->pid)
                    ->orderBy('id', 'ASC')
                    ->first();
                return $array_parish_managements->name;
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
        CRUD::setValidation(ParishionersRequest::class);

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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0',
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parish = $this->GetParish(request()->route('id'));
        
        $array_parish = array();
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        
        $parishs = $this->GetParishs(request()->route('id'));
        
        $array_parishs = array();
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
                'class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $association = $this->GetAssociation(request()->route('id'));
        
        CRUD::addField([
            'name' => 'assid',
            'type' => 'select_from_array',
            'attribute' => 'assid',
            'options' => $association,
            'label' => __('backend.association'),
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'last_name',
            'type' => 'text',
            'label' => __('backend.last_name'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);        
        
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.first_name'),
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'image',
            'type' => 'browse_custom',
            'mimes' => ['image'],
            'label' => __('backend.images'),
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name'  => 'sex', 
            'type'  => 'radio', 
            'label' => __('backend.sex'), 
            'options' => [
                0 => __('backend.female'),
                1 => __('backend.male')
            ], 
            'default'   => 0,
            'inline'    => true, 
            'wrapper'   => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-top-0 border-bottom-0',
            ],
            'tab'   => __('backend.general')
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
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0',
            ],
            'tab'   => __('backend.general')
        ]);
        
        CRUD::addField([
            'name' => 'cccd',
            'type' => 'text',
            'label' => __('backend.cccd'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'father',
            'type' => 'text',
            'label' => __('backend.father'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'mother',
            'type' => 'text',
            'label' => __('backend.mother'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'phone',
            'type' => 'number',
            'label' => __('backend.phone'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
                
        CRUD::addField([
            'name' => 'email',
            'type' => 'email',
            'label' => __('backend.email'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'origin',
            'type' => 'text',
            'label' => __('backend.origin'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-right-0 border-bottom-0',
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
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
                
        $xaphuong = $this->GetXaNguyenQuan(request()->route('id'));
        
        CRUD::addField([
            'name' => 'ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            'model' => Parishioners::class,
            'attribute' => 'ward',
            'options'   => $xaphuong,
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'residence',
            'type' => 'text',
            'label' => __('backend.residence'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        @include(resource_path().'/cities/tinh_thanhpho.php');
        
        CRUD::addField([
            'name' => 'resi_province',
            'type' => 'select_from_array',
            'label' => __('backend.province'),
            'attribute' => 'resi_province',
            'options' => $tinh_thanhpho,
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-right-0 border-bottom-0 border-top-0',
            ],
            'allows_multiple' => false,
            'tab' => __('backend.general'),
        ]);
                
        $xaphuong = $this->GetXaTruQuan(request()->route('id'));
        
        CRUD::addField([
            'name' => 'resi_ward',
            'type' => 'select_from_array',
            'label' => __('backend.ward'),
            'model' => Parishioners::class,
            'attribute' => 'resi_ward',
            'options'   => $xaphuong,
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_ethnicmanagements = DB::table('ethnicmanagements')
        ->orderBy('id', 'ASC')
        ->get()
        ->toArray();
        $array_ethnicmanagements = array_values($array_ethnicmanagements);
        $array_ethnicmanagements = json_decode(json_encode($array_ethnicmanagements, true), true);
        $array_ethnic = array();
        foreach($array_ethnicmanagements as $item){
            $array_ethnic[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'ethnic',
            'type' => 'select_from_array',
            'label' => __('backend.ethnicmanagement'),
            'default' => '',
            'options'   => $array_ethnic,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_language = DB::table('languagemanagements')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_language = array_values($array_language);
        $array_language = json_decode(json_encode($array_language, true), true);
        $array_lang = array();
        foreach($array_language as $item){
            $array_lang[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'language',
            'type' => 'select_from_array',
            'label' => __('backend.languagemanagement'),
            'default' => '',
            'options'   => $array_lang,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_levelmanagement = DB::table('levelmanagements')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_levelmanagements = array_values($array_levelmanagement);
        $array_levelmanagements = json_decode(json_encode($array_levelmanagements, true), true);
        $array_level = array();
        foreach($array_levelmanagements as $item){
            $array_level[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'level',
            'type' => 'select_from_array',
            'label' => __('backend.levelmanagement'),
            'default' => '',
            'options'   => $array_level,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-top-0 border-bottom-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_careermanagements = DB::table('careermanagements')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_careermanagements = array_values($array_careermanagements);
        $array_careermanagements = json_decode(json_encode($array_careermanagements, true), true);
        $array_career = array();
        foreach($array_careermanagements as $item){
            $array_career[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'career',
            'type' => 'select_from_array',
            'label' => __('backend.careermanagement'),
            'default' => '',
            'options'   => $array_career,
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-top-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_positionmanagements = DB::table('positionmanagements')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_positionmanagements = array_values($array_positionmanagements);
        $array_positionmanagements = json_decode(json_encode($array_positionmanagements, true), true);
        $array_position = array();
        foreach($array_positionmanagements as $item){
            $array_position[$item['id']] = $item['name'];
        }
        
        CRUD::addField([
            'name' => 'position',
            'type' => 'select_from_array',
            'label' => __('backend.positionmanagement'),
            'default' => '',
            'options'   => $array_position,
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'professional_level',
            'type' => 'text',
            'label' => __('backend.professional_level'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'study',
            'type' => 'select_from_array',
            'label' => __('backend.study'),
            'options'     => ['1' => 'Đang học', '2' => 'Đã học xong', '3' => 'Nghỉ học'],
            'default' => '1',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-top-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'new_convert',
            'type' => 'checkbox',
            'label' => __('backend.new_convert'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'married',
            'type' => 'checkbox',
            'label' => __('backend.married'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'statistical',
            'type' => 'checkbox',
            'label' => __('backend.statistical'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'note',
            'type' => 'text',
            'label' => __('backend.note_parishioners'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0',
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
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'baptism_number',
            'type' => 'number',
            'label' => __('backend.baptism_number'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_sacrament_givers = DB::table('sacrament_givers')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_sacrament_givers = array_values($array_sacrament_givers);
        $array_sacrament_givers = json_decode(json_encode($array_sacrament_givers, true), true);
        $array_sacrament = array();
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
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $array_sponsors = DB::table('sponsors')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_sponsors = array_values($array_sponsors);
        $array_sponsors = json_decode(json_encode($array_sponsors, true), true);
        $array_spon = array();
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
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-1 mb-0 border-warning py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-warning py-2 border-left-0',
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
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'more_power_number',
            'type' => 'number',
            'label' => __('backend.more_power_number'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-1 mb-0 border-info py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-1 mb-0 border-info py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-left-0 border-right-0',
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
                'class' => 'form-group col-md-2 mb-0 border-info py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]); 
        
        // ruoc le
        CRUD::addField([
            'name' => 'communion_date',
            'type'  => 'date_picker',
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'label' => __('backend.communion_date'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'communion_number',
            'type' => 'number',
            'label' => __('backend.communion_number'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'communion_giver',
            'type' => 'select_from_array',
            'options'   => $array_sacrament,
            'label' => __('backend.communion_giver'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'communion_dioceses',
            'type' => 'select_from_array',
            'label' => __('backend.diocese'),
            'attribute' => 'communion_dioceses',
            'options' => $array_dio,
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $deanerys_communion = $this->GetDeanerys_communion(request()->route('id'));
        
        CRUD::addField([
            'name' => 'communion_deanerys',
            'type' => 'select_from_array',
            'attribute' => 'communion_deanerys',
            'options' => $deanerys_communion,
            'label' => __('backend.deanerys'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        $parish_communion = $this->GetParish_communion(request()->route('id'));
        
        CRUD::addField([
            'name' => 'communion_parish',
            'type' => 'select_from_array',
            'attribute' => 'communion_parish',
            'options' => $parish_communion,
            'label' => __('backend.parish_managements'),
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-secondary py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]); 
        
        // xuc dau
        CRUD::addField([
            'name' => 'anoint_date',
            'type'  => 'date_picker',
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'label' => __('backend.anoint_date'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'anoint_status',
            'type' => 'select_from_array',
            'options'   => [
                ''  => 'Chọn tình trạng',
                '1' => 'Nguy tử',
                '2' => 'Thông thường'
            ],
            'label' => __('backend.anoint_status'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'anoint_giver',
            'type' => 'select_from_array',
            'options'   => $array_sacrament,
            'label' => __('backend.anoint_giver'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'anoint_note',
            'type' => 'text',
            'label' => __('backend.anoint_note'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        // song chet
        CRUD::addField([
            'name' => 'die_status',
            'type' => 'checkbox',
            'label' => __('backend.die_status'),
            'default' => '',
            'wrapper' => [
                'class' => 'form-group col-md-2 die_status mb-0 border-dark py-2 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'die_time',
            'type'  => 'date_picker',
            'label' => __('backend.die_time'),
            // optional:
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'vi'
            ],
            'attributes' => [
                'disabled'      => 'disabled',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-2 die_time mb-0 border-dark py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'die_lottery',
            'type' => 'number',
            'label' => __('backend.die_lottery'),
            'default' => '',
            'attributes' => [
                'disabled'      => 'disabled',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-2 mb-0 border-dark py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'die_death',
            'type' => 'text',
            'label' => __('backend.die_death'),
            'default' => '',
            'attributes' => [
                'disabled'      => 'disabled',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-dark py-2 border-left-0 border-right-0',
            ],
            'tab' => __('backend.general'),
        ]);
        
        CRUD::addField([
            'name' => 'die_burial',
            'type' => 'text',
            'label' => __('backend.die_burial'),
            'default' => '',
            'attributes' => [
                'disabled'      => 'disabled',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-3 mb-0 border-dark py-2 border-left-0',
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
            'controller' => ParishionersController::class,
            'model' => Parishioners::class,
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
	           $("input[type='checkbox']").change(function(event) {
                	event.preventDefault();
                    if(this.checked) {
                        $('input[name="die_lottery"]').prop("disabled", false); // Element(s) are now enabled.
                        $('input[name="die_death"]').prop("disabled", false); // Element(s) are now enabled.
                        $('input[name="die_burial"]').prop("disabled", false); // Element(s) are now enabled.
                        $('.die_time input[type="text"]').prop("disabled", false); // Element(s) are now enabled.
                    }else{
                        $('input[name="die_lottery"]').prop("disabled", true); // Element(s) are now enabled.
                        $('input[name="die_death"]').prop("disabled", true); // Element(s) are now enabled.
                        $('input[name="die_burial"]').prop("disabled", true); // Element(s) are now enabled.
                        $('.die_time input[type="text"]').prop("disabled", true); // Element(s) are now enabled.
                    }
                });
                if($('.die_status input[type="checkbox"]').is(':checked')) {
            		$('input[name="die_lottery"]').prop("disabled", false); // Element(s) are now enabled.
                	$('input[name="die_death"]').prop("disabled", false); // Element(s) are now enabled.
                	$('input[name="die_burial"]').prop("disabled", false); // Element(s) are now enabled.
                	$('.die_time input[type="text"]').prop("disabled", false); // Element(s) are now enabled.     
                }
        	});
    	</script>
    	<script type="text/javascript">
			jQuery(document).ready(function($){                
                $("select[name='did']").change(function() {
                    var $option = $(this).find('option:selected');
                    var did = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
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
                        url: "/api/Parishioners",
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
                        url: "/api/Parishioners",
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
                $("select[name='pid']").change(function() {
                    var $option = $(this).find('option:selected');
                    var pid = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {pid_assid:pid},
                        beforeSend: function(){
                            $("select[name='assid'] option[value]").remove();
                        },
                        success: function(data) {                            
                            $("select[name='assid']").append('<option value="">-- Chọn hội đoàn --</option>');
                    		$.each(data, function(key, value){
                                $("select[name='assid']").append(
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
                        url: "/api/Parishioners",
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
                $("select[name='resi_province']").change(function() {
                    var $option = $(this).find('option:selected');
                    var resi_province = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {province:resi_province},
                        beforeSend: function(){
                            $("select[name='resi_ward'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='resi_ward']").append(
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
                $("select[name='baptism_dioceses']").change(function() {
                    var $option = $(this).find('option:selected');
                    var baptism_dioceses = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
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
                        url: "/api/Parishioners",
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
                        url: "/api/Parishioners",
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
                        url: "/api/Parishioners",
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
                $("select[name='communion_dioceses']").change(function() {
                    var $option = $(this).find('option:selected');
                    var communion_dioceses = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {communion_dioceses:communion_dioceses},
                        beforeSend: function(){
                            $("select[name='communion_deanerys'] option[value]").remove();
                            $("select[name='communion_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='communion_deanerys']").append(
                                    "<option value=" + value.id + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });
                
                $("select[name='communion_deanerys']").change(function() {
                    var $option = $(this).find('option:selected');
                    var communion_deanerys = $option.val();//to get content of "value" attrib
                    var text = $option.text();//to get <option>Text</option> content
                    $.ajax({
                        url: "/api/Parishioners",
                        type:'GET',
                        headers: {
                        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {communion_deanerys:communion_deanerys},
                        beforeSend: function(){
                            $("select[name='communion_parish'] option[value]").remove();
                        },
                        success: function(data) {
                    		$.each(data, function(key, value){
                                $("select[name='communion_parish']").append(
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
            $parishioners = DB::table('parishioners')
                ->select('id', 'did', 'deid', 'pid', 'paid', 'name')
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
            $parishioners = DB::table('parishioners')
                ->select('id', 'did', 'deid', 'pid', 'paid', 'name')
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
    
    public function GetParishs($id){
        
        $array_par = array();
        if(!empty($id)){
            $parishioners = DB::table('parishioners')
                ->select('id', 'did', 'deid', 'pid', 'paid', 'name')
                ->where('id', '=', $id)
                ->where('status', 1)
                ->get()->first();
            
            $array_parish = DB::table('parishs')
                ->where('did', '=', $parishioners->did)
                ->where('deid', '=', $parishioners->deid)
                ->where('status', '=', 1)
                ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetAssociation($id){
        $array_ass = $array_parishioners = array();
        if(!empty($id)){
            $array_parishioners = DB::table('parishioners')
                ->where('id', '=', $id)
                ->where('status', '=', 1)
                ->get()->toArray();
            $array_parishioners = json_decode(json_encode($array_parishioners, true), true);
            foreach($array_parishioners as $item){
                $array_parishioners = $item;
            }
            if(!empty($array_parishioners)){
                $array_associations = DB::table('associations')
                    ->where('pid', '=', $array_parishioners['pid'])
                    ->where('status', '=', 1)
                    ->get()->toArray();
                $array_associations = json_decode(json_encode($array_associations, true), true);
                foreach($array_associations as $item){
                    $array_ass[$item['id']] = $item['name'];
                }
            }
        }
        return $array_ass;
    }
    
    public function GetXaNguyenQuan($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            $parish_managements = DB::table('parishioners')->where('id', $id)->get()->toArray();
            
            $province = $parish_managements[0]->province;
            
            foreach($xa_phuong_thitran as $xa){
                if($xa['matp'] == $province){
                    $array_xa[$xa['xaid']] = $xa['name'];
                }
            }
        }
        return $array_xa;
    }
    
    public function GetXaTruQuan($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $array_xa = array();
        if(!empty($id)){
            $parish_managements = DB::table('parishioners')->where('id', $id)->get()->toArray();
            
            $province = $parish_managements[0]->resi_province;
            
            foreach($xa_phuong_thitran as $xa){
                if($xa['matp'] == $province){
                    $array_xa[$xa['xaid']] = $xa['name'];
                }
            }
        }
        return $array_xa;
    }
    
    public function GetDeanerys_Baptism($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('deanerys')
                ->join('dioceses', 'deanerys.did', '=', 'dioceses.id')
                ->join('parishioners', 'dioceses.id', '=', 'parishioners.baptism_dioceses')
                ->select('deanerys.*')
                ->where('parishioners.id', '=', $id)
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
                ->join('parishioners', 'deanerys.id', '=', 'parishioners.baptism_deanerys')
                ->select('parish_managements.*')
                ->where('parishioners.id', '=', $id)
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
            ->join('parishioners', 'dioceses.id', '=', 'parishioners.more_power_dioceses')
            ->select('deanerys.*')
            ->where('parishioners.id', '=', $id)
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
            ->join('parishioners', 'deanerys.id', '=', 'parishioners.more_power_deanerys')
            ->select('parish_managements.*')
            ->where('parishioners.id', '=', $id)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
    
    public function GetDeanerys_communion($id){
        $array_dea = array();
        if(!empty($id)){
            $array_deanerys = DB::table('deanerys')
            ->join('dioceses', 'deanerys.did', '=', 'dioceses.id')
            ->join('parishioners', 'dioceses.id', '=', 'parishioners.communion_dioceses')
            ->select('deanerys.*')
            ->where('parishioners.id', '=', $id)
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
    
    
    public function GetParish_communion($id){
        
        $array_par = array();
        if(!empty($id)){
            $array_parish = DB::table('parish_managements')
            ->join('deanerys', 'parish_managements.deanerys', '=', 'deanerys.id')
            ->join('parishioners', 'deanerys.id', '=', 'parishioners.communion_deanerys')
            ->select('parish_managements.*')
            ->where('parishioners.id', '=', $id)
            ->get()->toArray();
            
            $array_parish = json_decode(json_encode($array_parish, true), true);
            
            foreach($array_parish as $item){
                $array_par[$item['id']] = $item['name'];
            }
        }
        return $array_par;
    }
}
