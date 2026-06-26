<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishManagementRequest;
use App\Models\ParishNew;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class ParishManagementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ParishManagementCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\ParishNew::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parish-management'); // giữ nguyên route cũ
        CRUD::setEntityNameStrings(__('backend.parish_management'), __('backend.parish_management'));
        CRUD::orderBy('id', 'desc');

        $user = backpack_user();

        if ($user->isSuperAdmin()) {
            CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show']);
            return;
        }

        if ($user->isParishAdmin()) {
            CRUD::allowAccess(['list', 'show', 'update']);
            CRUD::denyAccess(['create', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('delete');
            return;
        }

        if ($user->canManage()) {
            CRUD::allowAccess(['list', 'show']);
            CRUD::denyAccess(['create', 'update', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('update');
            CRUD::removeButton('delete');
        } else {
            CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
        }
    }

    protected function setupListOperation()
    {
        $user = backpack_user();

        if ($user->isParishAdmin() && !$user->isSuperAdmin()) {
            CRUD::addClause('where', 'id', !empty($user->parish_id) ? $user->parish_id : 0);
        }

        // Eager load tránh N+1
        $this->crud->query->with(['deanery', 'diocese']);

        CRUD::addColumn([
            'name'      => 'image',
            'type'      => 'image',
            'orderable' => false,
            'label'     => __('backend.image'),
        ]);

        CRUD::addColumn([
            'name'      => 'name',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.name'),
            'limit'     => 255,
        ]);

        CRUD::addColumn([
            'name'      => 'code',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.code') ?? 'Mã',
        ]);

        CRUD::addColumn([
            'name'      => 'deanery_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.deanerys'),
            'function'  => fn($entry) => $entry->deanery?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'      => 'diocese_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.diocese'),
            'function'  => fn($entry) => $entry->diocese?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'      => 'parish_priest_name',
            'type'      => 'text',
            'orderable' => false,
            'label'     => 'Cha xứ',
        ]);

        CRUD::addColumn([
            'name'      => 'phone',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.phone'),
        ]);

        CRUD::addColumn([
            'name'      => 'status',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.status'),
            'function'  => fn($entry) => $entry->status == 1
                ? __('backend.publish')
                : __('backend.draft'),
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ParishManagementRequest::class);

        // --- Tên giáo xứ ---
        CRUD::addField([
            'name'    => 'name',
            'type'    => 'text',
            'label'   => __('backend.name'),
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Mã giáo xứ ---
        CRUD::addField([
            'name'    => 'code',
            'type'    => 'text',
            'label'   => __('backend.code') ?? 'Mã giáo xứ',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Giáo phận ---
        $array_dio = [];
        foreach (DB::table('dioceses')->where('status', 1)->orderBy('id')->get() as $item) {
            $array_dio[$item->id] = $item->name;
        }

        CRUD::addField([
            'name'    => 'diocese_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.diocese'),
            'options' => $array_dio,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Giáo hạt ---
        $deanerys = $this->GetDeanerys(request()->route('id'));
        if (empty($deanerys)) {
            $firstDioceseId = array_key_first($array_dio);
            $deanerys = $this->GetDeanery_first($firstDioceseId);
        }

        CRUD::addField([
            'name'    => 'deanery_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.deanerys'),
            'options' => $deanerys,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'parish_priest_name',
            'type'    => 'text',
            'label'   => 'Cha xứ',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Tỉnh/thành phố ---
        @include(resource_path() . '/cities/tinh_thanhpho.php');

        CRUD::addField([
            'name'            => 'province',
            'type'            => 'select_from_array',
            'label'           => __('backend.province'),
            'options'         => $tinh_thanhpho,
            'allows_multiple' => false,
            'wrapper'         => ['class' => 'form-group col-md-4'],
            'tab'             => __('backend.general'),
        ]);

        // --- Xã/phường ---
        $xaphuong = $this->GetXa(request()->route('id'));

        CRUD::addField([
            'name'    => 'ward',
            'type'    => 'select_from_array',
            'label'   => __('backend.ward'),
            'options' => $xaphuong,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Điện thoại ---
        CRUD::addField([
            'name'    => 'phone',
            'type'    => 'text',
            'label'   => __('backend.phone'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => __('backend.general'),
        ]);

        // --- Hình ảnh ---
        CRUD::addField([
            'name'    => 'image',
            'type'    => 'browse_custom',
            'mimes'   => ['image'],
            'label'   => __('backend.images'),
            'wrapper' => ['class' => 'form-group col-md-6'],
            'tab'     => __('backend.general'),
        ]);

        // --- Trạng thái ---
        CRUD::addField([
            'name'    => 'status',
            'type'    => 'radio',
            'label'   => __('backend.status'),
            'options' => [0 => __('backend.draft'), 1 => __('backend.publish')],
            'default' => 1,
            'inline'  => true,
            'tab'     => __('backend.general'),
        ]);

?>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // Đổi tỉnh → load xã/phường
                $("select[name='province']").change(function() {
                    var province = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/ParishManagement",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            province: province
                        },
                        beforeSend: function() {
                            $("select[name='ward'] option").remove();
                        },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $("select[name='ward']").append(
                                    "<option value='" + value.xaid + "'>" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });

                // Đổi giáo phận → load giáo hạt
                $("select[name='diocese_id']").change(function() {
                    var diocese = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/ParishManagement",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            diocese: diocese
                        },
                        beforeSend: function() {
                            $("select[name='deanery_id'] option").remove();
                        },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $("select[name='deanery_id']").append(
                                    "<option value='" + value.id + "'>" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });

            });
        </script>
<?php
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function GetXa($id): array
    {
        @include(resource_path() . '/cities/xa_phuong_thitran.php');
        $array_xa = [];
        if (!empty($id)) {
            $parish = DB::table('parishes')->where('id', $id)->first();
            if ($parish && !empty($parish->province)) {
                foreach ($xa_phuong_thitran as $xa) {
                    if ($xa['matp'] == $parish->province) {
                        $array_xa[$xa['xaid']] = $xa['name'];
                    }
                }
            }
        }
        return $array_xa;
    }

    public function GetDeanerys($id): array
    {
        $array_dea = [];
        if (!empty($id)) {
            $deanerys = DB::table('parishes')
                ->select('deanerys.id', 'deanerys.did', 'deanerys.name')
                ->rightJoin('deanerys', 'deanerys.did', '=', 'parishes.diocese_id')
                ->where('parishes.id', '=', $id)
                ->where('deanerys.status', '=', 1)
                ->get()
                ->toArray();

            foreach ($deanerys as $item) {
                $array_dea[$item->id] = $item->name;
            }
        }
        return $array_dea;
    }

    public function GetDeanery_first($dioceseId): array
    {
        $array_dea = [];
        if (!empty($dioceseId)) {
            $deanerys = DB::table('deanerys')
                ->select('id', 'did', 'name')
                ->where('did', '=', $dioceseId)
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            foreach ($deanerys as $item) {
                $array_dea[$item->id] = $item->name;
            }
        }
        return $array_dea;
    }
}
