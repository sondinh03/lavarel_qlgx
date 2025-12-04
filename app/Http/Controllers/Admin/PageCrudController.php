<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\PageController;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use App\Traits\PageTemplates;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class PageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PageCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;
    use ReviseOperation;
    use PageTemplates;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Page::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/page');
        CRUD::setEntityNameStrings('page', 'pages');
        
        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions
         |--------------------------------------------------------------------------
         */
        if (! backpack_user()->can('view_page')) {
            CRUD::denyAccess(['list']);
        }
        
        if (! backpack_user()->can('delete_page')) {
            CRUD::denyAccess(['delete']);
        }
        
        if (! backpack_user()->can('create_page')) {
            CRUD::denyAccess(['create']);
            CRUD::denyAccess(['clone']);
        }
        
        if (backpack_user()->can('update_page')) {
            CRUD::allowAccess(['revisions']);
            CRUD::with('revisionHistory');
        } else {
            CRUD::denyAccess(['clone']);
            CRUD::denyAccess(['update']);
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
        CRUD::addColumn([
            'name' => 'name',
            'label' => trans('backpack::pagemanager.name'),
            //'label' => trans('backpack::pagemanager.name'),
            'limit' => 255
        ]);
        CRUD::addColumn([
            'name' => 'template',
            'label' => trans('backpack::pagemanager.template'),
            //'label' => trans('backpack::pagemanager.template'),
            'type' => 'model_function',
            'function_name' => 'getTemplateName',
        ]);
        
        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        //CRUD::setValidation(PageRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        $this->addDefaultPageFields(Request::input('template'));
        $this->useTemplate(Request::input('template'));
        CRUD::setValidation(PageRequest::class);
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
    
    public function addDefaultPageFields(bool|string|null $template = false)
    {
        CRUD::addField([
            'name' => 'template',
            'label' => trans('backpack::pagemanager.template'),
            'type' => 'select_page_template',
            'options' => $this->getTemplatesArray(),
            'value' => $template,
            'allows_null' => false,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::pagemanager.page_name'),
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
            // 'disabled' => 'disabled'
        ]);
        CRUD::addfield([
            'name' => 'slug',
            'type' => 'slug',
            'source' => 'name',
            'controller' => PageController::class,
            'model' => Page::class,
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        CRUD::addField([
            'name' => 'link_canonical',
            'label' => 'Canonical URL',
            'fake' => true,
            'store_in' => 'extras',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        CRUD::addField([
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'Ngày đăng',
            'default' => Carbon::now(),
        ]);
    }
    
    public function useTemplate(bool|string|null $template_name = false)
    {
        $templates = $this->getTemplates();
        
        // set the default template
        if ($template_name == false) {
            $template_name = $templates[0]->name;
        }
        
        // actually use the template
        if ($template_name) {
            $this->{$template_name}();
        }
    }
    
    public function getTemplates(): array
    {
        $templates_trait = new ReflectionClass('App\Traits\PageTemplates');
        $templates = $templates_trait->getMethods(ReflectionMethod::IS_PRIVATE);
        
        if (! count($templates)) {
            abort(503, trans('backpack::pagemanager.template_not_found'));
        }
        
        return $templates;
    }    
    
    public function getTemplatesArray(): array
    {
        $templates = $this->getTemplates();
        
        foreach ($templates as $template) {
            $templates_array[$template->name] = str_replace('_', ' ', Str::title($template->name));
        }
        
        return $templates_array;
    }
}
