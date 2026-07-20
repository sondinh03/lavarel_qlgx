<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LopRequest;
use App\Models\CatechismClass;
use App\Models\Decen;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ParishNew;
use App\Models\SetAdmin;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LopCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(CatechismClass::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lop');
        CRUD::setEntityNameStrings(__('backend.lop'), __('backend.lops'));

        $user = backpack_user();
        if (! empty($user->id)) {
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();
            if (! empty($setadmin)) {
                CRUD::allowAccess(['create', 'delete', 'update', 'show']);
            } else {
                $decen = Decen::where('use', $user->id)->where('status', '1')->first();
                if (empty($decen?->parish)) {
                    CRUD::denyAccess(['create', 'delete', 'update', 'show']);
                }
            }
        }
    }

    protected function setupListOperation(): void
    {
        if (! $this->crud->getRequest()->has('order')) {
            $parishId = $this->resolveDecenParishId();
            if ($parishId !== null) {
                CRUD::addClause('where', 'parish_id', $parishId);
            }
        }

        CRUD::with(['parish', 'schoolYear', 'gradeLevel']);

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');

        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'label' => __('backend.name'), 'orderable' => true]);
        CRUD::addColumn([
            'name' => 'school_year_id',
            'type' => 'closure',
            'label' => __('backend.namhoc'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->schoolYear?->name ?? '—',
        ]);
        CRUD::addColumn([
            'name' => 'grade_level_id',
            'type' => 'closure',
            'label' => __('backend.Block'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->gradeLevel?->name ?? '—',
        ]);
        CRUD::addColumn([
            'name' => 'parish_id',
            'type' => 'closure',
            'label' => __('backend.parish_managements'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->parish?->name ?? '—',
        ]);
        CRUD::addColumn(['name' => 'capacity', 'type' => 'number', 'label' => 'Sĩ số', 'orderable' => false]);
        CRUD::addColumn([
            'name' => 'is_active',
            'type' => 'closure',
            'label' => __('backend.status'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->is_active ? 'Hoạt động' : 'Ngưng',
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(LopRequest::class);
        $this->addClassFormFields();
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    private function addClassFormFields(): void
    {
        $scopedParishId = $this->resolveDecenParishId();

        CRUD::addField([
            'name' => 'parish_id',
            'type' => 'select_from_array',
            'label' => __('backend.parish_managements'),
            'options' => $this->parishOptions(),
            'allows_null' => false,
            'default' => $scopedParishId,
            'attributes' => $scopedParishId ? ['readonly' => 'readonly'] : [],
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'school_year_id',
            'type' => 'select_from_array',
            'label' => __('backend.namhoc'),
            'options' => $this->schoolYearOptions(),
            'allows_null' => false,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'grade_level_id',
            'type' => 'select_from_array',
            'label' => __('backend.Block'),
            'options' => $this->gradeLevelOptions(),
            'allows_null' => false,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.name'),
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'capacity',
            'type' => 'number',
            'label' => 'Sĩ số tối đa',
            'attributes' => ['min' => 0],
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'type' => 'checkbox',
            'label' => 'Hoạt động',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);
    }

    private function resolveDecenParishId(): ?int
    {
        $user = backpack_user();
        if (empty($user->id)) {
            return null;
        }

        $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();
        if (! empty($setadmin)) {
            return null;
        }

        $decen = Decen::where('use', $user->id)->where('status', '1')->first();
        if (! empty($decen?->parish) && ! empty($decen->pid)) {
            return (int) $decen->pid;
        }

        return null;
    }

    private function parishOptions(): array
    {
        $query = ParishNew::query()->where('status', 1)->orderBy('name');
        $scopedParishId = $this->resolveDecenParishId();
        if ($scopedParishId) {
            $query->where('id', $scopedParishId);
        }

        return $query->pluck('name', 'id')->all();
    }

    private function schoolYearOptions(): array
    {
        return NamHoc::query()
            ->where('status', 1)
            ->orderByDesc('id')
            ->pluck('name', 'id')
            ->all();
    }

    private function gradeLevelOptions(): array
    {
        return GradeLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->all();
    }

    protected function setupShowOperation()
    {
        $this->setupShowFromListColumns();
    }
}
