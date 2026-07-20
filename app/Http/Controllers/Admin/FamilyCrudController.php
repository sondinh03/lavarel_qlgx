<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FamilyRequest;
use App\Models\Decen;
use App\Models\Family;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\SetAdmin;
use App\Support\VietnamAddressResolver;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;

/**
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FamilyCrudController extends CrudController
{
    use \App\Http\Controllers\Admin\Concerns\ConfiguresBackpackShow;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;

    public function setup(): void
    {
        CRUD::setModel(Family::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/family');
        CRUD::setEntityNameStrings(__('backend.family'), __('backend.families'));

        $user = backpack_user();

        if ($user->isSuperAdmin()) {
            CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show', 'revisions']);
            CRUD::with('revisionHistory');
            return;
        }

        if (! backpack_user()->can('view_manager')) {
            CRUD::denyAccess(['list']);
        }

        if (backpack_user()->can('delete_manager')) {
            // bulk delete optional
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

        $user = backpack_user();
        if (! empty($user->id)) {
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();
            if (! empty($setadmin)) {
                CRUD::allowAccess('create');
                CRUD::allowAccess('delete');
                CRUD::allowAccess('update');
                CRUD::allowAccess('show');
            } else {
                $decen = Decen::where('use', $user->id)->where('status', '1')->first();
                if (empty($decen?->parish)) {
                    CRUD::denyAccess('create');
                    CRUD::denyAccess('delete');
                    CRUD::denyAccess('update');
                    CRUD::denyAccess('show');
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

        CRUD::with(['parish', 'parishGroup', 'head']);

        CRUD::addButtonFromModelFunction('line', 'open_link', 'openLink', 'beginning');

        CRUD::addColumn(['name' => 'code', 'type' => 'text', 'label' => 'Mã', 'orderable' => true]);
        CRUD::addColumn(['name' => 'name', 'type' => 'text', 'label' => __('backend.name'), 'orderable' => true]);
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
            'name' => 'head_id',
            'type' => 'closure',
            'label' => 'Chủ hộ',
            'orderable' => false,
            'function' => fn ($entry) => $entry->head?->full_name_with_saint ?? '—',
        ]);
        CRUD::addColumn([
            'name' => 'member_count',
            'type' => 'closure',
            'label' => 'Số thành viên',
            'orderable' => false,
            'function' => fn ($entry) => (string) $entry->member_count,
        ]);
        CRUD::addColumn([
            'name' => 'status',
            'type' => 'closure',
            'label' => __('backend.status'),
            'orderable' => false,
            'function' => fn ($entry) => $entry->status ? 'Hoạt động' : 'Ngưng',
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(FamilyRequest::class);
        $this->addFamilyFormFields();
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    private function addFamilyFormFields(): void
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
                'name' => 'code',
                'type' => 'text',
                'label' => 'Mã',
                'attributes' => ['readonly' => 'readonly'],
                'wrapper' => ['class' => 'form-group col-md-4'],
                'tab' => __('backend.general'),
            ]);
        }

        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => __('backend.name'),
            'wrapper' => ['class' => 'form-group col-md-8'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'head_id',
            'type' => 'select2',
            'label' => 'Chủ hộ',
            'entity' => 'head',
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
            'wrapper' => ['class' => 'form-group col-md-12'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'address',
            'type' => 'text',
            'label' => 'Địa chỉ',
            'wrapper' => ['class' => 'form-group col-md-12'],
            'tab' => __('backend.general'),
        ]);

        $provinceOptions = collect(VietnamAddressResolver::provincesForSelect())
            ->pluck('name', 'id')
            ->all();

        CRUD::addField([
            'name' => 'province',
            'type' => 'select_from_array',
            'label' => 'Tỉnh/TP',
            'options' => $provinceOptions,
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'ward_id',
            'type' => 'number',
            'label' => 'Mã xã/phường',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'phone',
            'type' => 'text',
            'label' => __('backend.phone'),
            'wrapper' => ['class' => 'form-group col-md-6'],
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
            'name' => 'status',
            'type' => 'checkbox',
            'label' => 'Hoạt động',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab' => __('backend.general'),
        ]);

        CRUD::addField([
            'name' => 'is_included_in_stats',
            'type' => 'checkbox',
            'label' => 'Được thống kê',
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
