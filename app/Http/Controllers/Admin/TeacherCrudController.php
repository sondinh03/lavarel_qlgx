<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TeacherRequest;
use App\Models\Decen;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\ParishNew;
use App\Models\SetAdmin;
use App\Models\Teacher;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TeacherCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Teacher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/teacher');
        CRUD::setEntityNameStrings(__('backend.teacher'), __('backend.teachers'));

        if (! backpack_user()->can('view_manager')) {
            CRUD::denyAccess(['list']);
        }

        if (! backpack_user()->can('delete_manager')) {
            CRUD::removeButton('delete');
        }

        if (! backpack_user()->can('create_manager')) {
            CRUD::removeButton('create');
        }

        if (backpack_user()->can('update_manager')) {
            CRUD::allowAccess(['show']);
        } else {
            CRUD::removeButton('update');
            CRUD::allowAccess(['show']);
        }

        $user = backpack_user();
        if (! empty($user->id)) {
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();
            if (! empty($setadmin)) {
                CRUD::allowAccess(['create', 'delete', 'update', 'show']);
            } else {
                $decen = Decen::where('use', $user->id)->where('status', '1')->first();
                if (empty($decen?->student)) {
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

        CRUD::with(['parish', 'parishGroup', 'saint']);

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');

        CRUD::addColumn([
            'name' => 'full_name',
            'type' => 'closure',
            'label' => __('backend.fullname'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->full_name_with_saint,
        ]);
        CRUD::addColumn([
            'name' => 'parish_id',
            'type' => 'closure',
            'label' => __('backend.parish_managements'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->parish?->name ?? '—',
        ]);
        CRUD::addColumn([
            'name' => 'parish_group_id',
            'type' => 'closure',
            'label' => 'Giáo họ',
            'orderable' => false,
            'function' => fn ($entry) => $entry->parishGroup?->name ?? '—',
        ]);
        CRUD::addColumn(['name' => 'phone_number', 'type' => 'text', 'label' => __('backend.phone'), 'orderable' => false]);
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
        CRUD::setValidation(TeacherRequest::class);
        $this->addTeacherFormFields();
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    private function addTeacherFormFields(): void
    {
        $scopedParishId = $this->resolveDecenParishId();
        $entryParishId = CRUD::getCurrentEntry()?->parish_id;
        $parishIdForGroups = $entryParishId ?? $scopedParishId;

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
            'name' => 'parish_group_id',
            'type' => 'select_from_array',
            'label' => 'Giáo họ',
            'options' => $this->parishGroupOptions($parishIdForGroups),
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'last_name',
            'type' => 'text',
            'label' => __('backend.last_name'),
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'first_name',
            'type' => 'text',
            'label' => __('backend.first_name'),
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'saint_id',
            'type' => 'select2',
            'label' => __('backend.holymanagement'),
            'entity' => 'saint',
            'attribute' => 'name',
            'model' => Holymanagement::class,
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'gender',
            'type' => 'radio',
            'label' => __('backend.gender'),
            'options' => ['male' => 'Nam', 'female' => 'Nữ'],
            'inline' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'birthday',
            'type' => 'date',
            'label' => __('backend.birthday'),
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'phone_number',
            'type' => 'text',
            'label' => __('backend.phone'),
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'address',
            'type' => 'text',
            'label' => 'Địa chỉ',
            'wrapper' => ['class' => 'form-group col-md-12'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'note',
            'type' => 'textarea',
            'label' => __('backend.note'),
            'wrapper' => ['class' => 'form-group col-md-12'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'type' => 'checkbox',
            'label' => 'Hoạt động',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
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

    private function parishGroupOptions(?int $parishId): array
    {
        if (! $parishId) {
            return [];
        }

        return ParishGroup::query()
            ->where('parish_id', $parishId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
