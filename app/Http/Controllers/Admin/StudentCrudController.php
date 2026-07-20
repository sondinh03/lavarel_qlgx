<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use App\Models\Decen;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\SetAdmin;
use App\Models\StudentNew;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(StudentNew::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/student');
        CRUD::setEntityNameStrings(__('backend.student'), __('backend.students'));

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
            $this->crud->orderBy('last_name', 'asc');
        }

        CRUD::with(['parish', 'parishGroup', 'saint', 'parishioner']);

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');

        CRUD::addColumn(['name' => 'student_code', 'type' => 'text', 'label' => 'Mã', 'orderable' => true]);
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
        CRUD::addColumn([
            'name' => 'birthday',
            'type' => 'date',
            'label' => __('backend.birthday'),
            'orderable' => false,
        ]);
        CRUD::addColumn([
            'name' => 'gender',
            'type' => 'closure',
            'label' => __('backend.gender'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->gender_text,
        ]);
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
        CRUD::setValidation(StudentRequest::class);
        $this->addStudentFormFields();
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    private function addStudentFormFields(): void
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

        if (CRUD::getCurrentOperation() === 'update') {
            CRUD::addField([
                'name' => 'student_code',
                'type' => 'text',
                'label' => 'Mã thiếu nhi',
                'attributes' => ['readonly' => 'readonly'],
                'wrapper' => ['class' => 'form-group col-md-4'],
                'tab' => __('backend.general'),
            ]);
        }

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
            'name' => 'father_name',
            'type' => 'text',
            'label' => 'Tên cha',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'mother_name',
            'type' => 'text',
            'label' => 'Tên mẹ',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'gender',
            'type' => 'radio',
            'label' => __('backend.gender'),
            'options' => ['male' => 'Nam', 'female' => 'Nữ'],
            'default' => 'male',
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
            'name' => 'phone',
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
            'name' => 'parishioner_id',
            'type' => 'select2',
            'label' => 'Liên kết giáo dân',
            'entity' => 'parishioner',
            'attribute' => 'full_name_with_saint',
            'model' => Parishioner::class,
            'options' => function ($query) use ($parishIdForGroups, $scopedParishId) {
                $parishId = $parishIdForGroups ?? $scopedParishId;
                if ($parishId) {
                    $query->where('parish_id', $parishId);
                }

                return $query->orderBy('last_name')->orderBy('first_name')->limit(300);
            },
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-4'],
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

    protected function setupShowOperation()
    {
        $this->setupShowFromListColumns();
    }
}
