<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishManagementRequest;
use App\Http\Controllers\ParishManagementController;
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

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ParishNew::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parish-management');
        CRUD::setEntityNameStrings(__('backend.parish_management'), __('backend.parish_management'));
        CRUD::orderBy('id', 'desc');

        /*
         |--------------------------------------------------------------------------
         | Check Roles & Permissions (dùng Spatie HasRoles)
         |--------------------------------------------------------------------------
         */
        $user = backpack_user();

        // Super admin có toàn quyền
        if ($user->isSuperAdmin()) {
            CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show', 'revisions']);
            CRUD::with('revisionHistory');
            return;
        }

        // Parish admin chỉ quản lý parish của mình
        if ($user->isParishAdmin()) {
            CRUD::allowAccess(['list', 'show', 'update']);
            CRUD::denyAccess(['create', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('delete');
            return;
        }

        // Các role khác (catechist, v.v.) chỉ xem
        if ($user->canManage()) {
            CRUD::allowAccess(['list', 'show']);
            CRUD::denyAccess(['create', 'update', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('update');
            CRUD::removeButton('delete');
        } else {
            // Không có quyền gì
            CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
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
        $user = backpack_user();

        // Parish admin chỉ thấy parish của mình
        if ($user->isParishAdmin() && !$user->isSuperAdmin()) {
            if (!empty($user->parish_id)) {
                CRUD::addClause('where', 'id', $user->parish_id);
            } else {
                CRUD::addClause('where', 'id', 0);
            }
        }

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
            'name'      => 'deanery_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.deanerys'),
            'function'  => function ($entry) {
                if (!empty($entry->deanery_id)) {
                    $deanery = DB::table('deanerys')
                        ->where('id', $entry->deanery_id)
                        ->where('status', 1)
                        ->first();
                    return $deanery?->name ?? '—';
                }
                return '—';
            },
        ]);

        CRUD::addColumn([
            'name'      => 'diocese_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.diocese'),
            'function'  => function ($entry) {
                if (!empty($entry->diocese_id)) {
                    $diocese = DB::table('dioceses')
                        ->where('id', $entry->diocese_id)
                        ->where('status', 1)
                        ->first();
                    return $diocese?->name ?? '—';
                }
                return '—';
            },
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
            'function'  => function ($entry) {
                return $entry->status == 0
                    ? __('backend.draft')
                    : __('backend.publish');
            },
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
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

        // --- Code giáo xứ ---
        CRUD::addField([
            'name'    => 'code',
            'type'    => 'text',
            'label'   => __('backend.code') ?? 'Mã giáo xứ',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Giáo phận ---
        $array_diocese = DB::table('dioceses')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();
        $array_diocese = json_decode(json_encode($array_diocese, true), true);

        $array_dio = [];
        foreach ($array_diocese as $item) {
            $array_dio[$item['id']] = $item['name'];
        }

        CRUD::addField([
            'name'    => 'diocese_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.diocese'),
            'options' => $array_dio,
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Giáo hạt (load theo diocese_id) ---
        $deanerys = $this->GetDeanerys(request()->route('id'));
        if (empty($deanerys)) {
            $firstDiocese = reset($array_diocese);
            $deanerys = $this->GetDeanery_first($firstDiocese['id'] ?? null);
        }

        CRUD::addField([
            'name'    => 'deanery_id',
            'type'    => 'select_from_array',
            'options' => $deanerys,
            'label'   => __('backend.deanerys'),
            'wrapper' => ['class' => 'form-group col-md-4'],
            'tab'     => __('backend.general'),
        ]);

        // --- Tỉnh/thành phố ---
        @include(resource_path() . '/cities/tinh_thanhpho.php');

        CRUD::addField([
            'name'             => 'province',
            'type'             => 'select_from_array',
            'label'            => __('backend.province'),
            'options'          => $tinh_thanhpho,
            'allows_multiple'  => false,
            'wrapper'          => ['class' => 'form-group col-md-4'],
            'tab'              => __('backend.general'),
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
            'options' => [
                0 => __('backend.draft'),
                1 => __('backend.publish'),
            ],
            'default' => 1,
            'inline'  => true,
            'tab'     => __('backend.general'),
        ]);

        // --- AJAX: load xã theo tỉnh & giáo hạt theo giáo phận ---
?>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

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
                            $("select[name='ward'] option[value]").remove();
                        },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $("select[name='ward']").append(
                                    "<option value=" + value.xaid + ">" + value.name + "</option>"
                                );
                            });
                        }
                    });
                });

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
                            $("select[name='deanery_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $("select[name='deanery_id']").append(
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

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Lấy danh sách xã/phường theo tỉnh của parish đang edit.
     */
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

    /**
     * Lấy danh sách giáo hạt theo giáo phận của parish đang edit.
     */
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

            foreach (json_decode(json_encode($deanerys, true), true) as $item) {
                $array_dea[$item['id']] = $item['name'];
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
                ->get()
                ->toArray();

            foreach (json_decode(json_encode($deanerys, true), true) as $item) {
                $array_dea[$item['id']] = $item['name'];
            }
        }
        return $array_dea;
    }
}
