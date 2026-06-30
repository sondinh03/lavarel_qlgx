<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishionersRequest;
use App\Models\Parishioner;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Backpack\ReviseOperation\ReviseOperation;

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

    public function setup()
    {
        CRUD::setModel(\App\Models\Parishioner::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/parishioners');
        CRUD::setEntityNameStrings(__('backend.parishioners'), __('backend.parishioners'));

        $user = backpack_user();

        // Super admin có toàn quyền
        if ($user->isSuperAdmin()) {
            CRUD::allowAccess(['list', 'create', 'update', 'delete', 'show', 'revisions']);
            CRUD::with('revisionHistory');
            return;
        }

        // Parish admin quản lý giáo dân của parish mình
        if ($user->isParishAdmin()) {
            CRUD::allowAccess(['list', 'show', 'create', 'update']);
            CRUD::denyAccess(['delete']);
            CRUD::removeButton('delete');
            return;
        }

        // Catechist chỉ xem
        if ($user->isCatechist()) {
            CRUD::allowAccess(['list', 'show']);
            CRUD::denyAccess(['create', 'update', 'delete']);
            CRUD::removeButton('create');
            CRUD::removeButton('update');
            CRUD::removeButton('delete');
            return;
        }

        // Không có quyền
        CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
    }

    protected function setupListOperation()
    {
        $user = backpack_user();

        // Parish admin chỉ thấy giáo dân của parish mình
        if ($user->isParishAdmin() && !$user->isSuperAdmin()) {
            if (!empty($user->parish_id)) {
                CRUD::addClause('where', 'parish_id', $user->parish_id);
            } else {
                CRUD::addClause('where', 'parish_id', 0);
            }
        }

        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('first_name', 'asc');
        }

        // Eager load tránh N+1
        $this->crud->query->with(['saint', 'parish']);

        CRUD::addColumn([
            'name'      => 'avatar_path',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.image'),
            'function'  => function ($entry) {
                $url = $entry->avatar_path ? media_url($entry->avatar_path) : null;
                if (!$url) {
                    return '—';
                }
                return '<img src="' . e($url) . '" style="max-height:40px;border-radius:4px" alt="">';
            },
            'escaped'   => false,
        ]);

        CRUD::addColumn([
            'name'      => 'saint_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.holymanagement'),
            'function'  => fn($entry) => $entry->saint?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'      => 'last_name',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.last_name'),
            'limit'     => 255,
        ]);

        CRUD::addColumn([
            'name'      => 'first_name',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.first_name'),
            'limit'     => 255,
        ]);

        CRUD::addColumn([
            'name'      => 'gender',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.sex'),
            'function'  => fn($entry) => $entry->gender === 'female'
                ? __('backend.female')
                : __('backend.male'),
        ]);

        CRUD::addColumn([
            'name'      => 'birthday',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.birthday'),
            'function'  => fn($entry) => $entry->birthday
                ? $entry->birthday->format('d-m-Y')
                : '',
        ]);

        CRUD::addColumn([
            'name'      => 'phone',
            'type'      => 'text',
            'orderable' => false,
            'label'     => __('backend.phone'),
        ]);

        CRUD::addColumn([
            'name'      => 'parish_id',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.parish_management'),
            'function'  => fn($entry) => $entry->parish?->name ?? '—',
        ]);

        CRUD::addColumn([
            'name'      => 'status',
            'type'      => 'closure',
            'orderable' => false,
            'label'     => __('backend.status'),
            'function'  => fn($entry) => $entry->status
                ? __('backend.publish')
                : __('backend.draft'),
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ParishionersRequest::class);

        // ---------------------------------------------------------------
        // NHÓM 1: Phân loại giáo xứ (border xanh dương)
        // ---------------------------------------------------------------

        // Giáo phận
        $array_dio = $this->getDioceseOptions();

        CRUD::addField([
            'name'    => 'diocese_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.diocese'),
            'options' => $array_dio,
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // Giáo hạt
        $deanerys = $this->GetDeanerys(request()->route('id'));
        CRUD::addField([
            'name'    => 'deanery_id',
            'type'    => 'select_from_array',
            'options' => array_merge(['' => '------'], $deanerys),
            'label'   => __('backend.deanerys'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // Giáo xứ (parish_id)
        $parish = $this->GetParish(request()->route('id'));
        CRUD::addField([
            'name'    => 'parish_id',
            'type'    => 'select_from_array',
            'options' => $parish,
            'label'   => __('backend.parish_managements'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // Giáo họ (parish_area_id)
        $parishAreas = $this->GetParishAreas(request()->route('id'));
        CRUD::addField([
            'name'    => 'parish_area_id',
            'type'    => 'select_from_array',
            'options' => $parishAreas,
            'label'   => __('backend.parish'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 2: Thông tin cá nhân
        // ---------------------------------------------------------------

        // Thánh bổn mạng
        $array_ho = $this->getSaintOptions();
        CRUD::addField([
            'name'    => 'saint_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.holymanagement'),
            'options' => $array_ho,
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'last_name',
            'type'    => 'text',
            'label'   => __('backend.last_name'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'first_name',
            'type'    => 'text',
            'label'   => __('backend.first_name'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'avatar_path',
            'type'    => 'browse_custom',
            'mimes'   => ['image'],
            'label'   => __('backend.images'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'gender',
            'type'    => 'radio',
            'label'   => __('backend.sex'),
            'options' => [
                'female' => __('backend.female'),
                'male'   => __('backend.male'),
            ],
            'default' => 'female',
            'inline'  => true,
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'                => 'birthday',
            'type'                => 'date_picker',
            'label'               => __('backend.birthday'),
            'date_picker_options' => ['todayBtn' => 'linked', 'format' => 'dd-mm-yyyy', 'language' => 'vi'],
            'wrapper'             => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-right-0 border-top-0'],
            'tab'                 => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'cccd',
            'type'    => 'text',
            'label'   => __('backend.cccd'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'father_name',
            'type'    => 'text',
            'label'   => __('backend.father'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'mother_name',
            'type'    => 'text',
            'label'   => __('backend.mother'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'phone',
            'type'    => 'text',
            'label'   => __('backend.phone'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-right-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'email',
            'type'    => 'email',
            'label'   => __('backend.email'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-primary py-2 border-left-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 3: Địa chỉ nguyên quán (border đỏ)
        // ---------------------------------------------------------------

        CRUD::addField([
            'name'    => 'origin',
            'type'    => 'text',
            'label'   => __('backend.origin'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        @include(resource_path() . '/cities/tinh_thanhpho.php');

        CRUD::addField([
            'name'            => 'permanent_province',
            'type'            => 'select_from_array',
            'label'           => __('backend.province'),
            'options'         => $tinh_thanhpho,
            'allows_multiple' => false,
            'wrapper'         => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0'],
            'tab'             => __('backend.general'),
        ]);

        $xaPermanent = $this->GetXaPermanent(request()->route('id'));
        CRUD::addField([
            'name'    => 'permanent_ward_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.ward'),
            'options' => $xaPermanent,
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'permanent_residence',
            'type'    => 'text',
            'label'   => __('backend.residence'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 4: Địa chỉ tạm trú (border đỏ tiếp)
        // ---------------------------------------------------------------

        CRUD::addField([
            'name'            => 'temporary_province',
            'type'            => 'select_from_array',
            'label'           => __('backend.province'),
            'options'         => $tinh_thanhpho,
            'allows_multiple' => false,
            'wrapper'         => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-right-0 border-bottom-0 border-top-0'],
            'tab'             => __('backend.general'),
        ]);

        $xaTemporary = $this->GetXaTemporary(request()->route('id'));
        CRUD::addField([
            'name'    => 'temporary_ward_id',
            'type'    => 'select_from_array',
            'label'   => __('backend.ward'),
            'options' => $xaTemporary,
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'temporary_residence',
            'type'    => 'text',
            'label'   => __('backend.residence'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 5: Phân loại xã hội (border đỏ tiếp)
        // ---------------------------------------------------------------

        CRUD::addField([
            'name'    => 'ethnic',
            'type'    => 'select_from_array',
            'label'   => __('backend.ethnicmanagement'),
            'options' => $this->getOptions('ethnicmanagements'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'language',
            'type'    => 'select_from_array',
            'label'   => __('backend.languagemanagement'),
            'options' => $this->getOptions('languagemanagements'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-right-0 border-bottom-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'education_level',
            'type'    => 'select_from_array',
            'label'   => __('backend.levelmanagement'),
            'options' => $this->getOptions('levelmanagements'),
            'wrapper' => ['class' => 'form-group col-md-2 mb-0 border-danger py-2 border-left-0 border-top-0 border-bottom-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'career',
            'type'    => 'select_from_array',
            'label'   => __('backend.careermanagement'),
            'options' => $this->getOptions('careermanagements'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-top-0 border-right-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'position',
            'type'    => 'select_from_array',
            'label'   => __('backend.positionmanagement'),
            'options' => $this->getOptions('positionmanagements'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-right-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'catechism_level',
            'type'    => 'select_from_array',
            'label'   => __('backend.study'),
            'options' => ['1' => 'Đang học', '2' => 'Đã học xong', '3' => 'Nghỉ học'],
            'default' => '1',
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-danger py-2 border-left-0 border-top-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 6: Trạng thái đặc biệt (border xanh lá)
        // ---------------------------------------------------------------

        CRUD::addField([
            'name'    => 'is_new_convert',
            'type'    => 'checkbox',
            'label'   => __('backend.new_convert'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-success py-2 border-right-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'married',
            'type'    => 'checkbox',
            'label'   => __('backend.married'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'is_included_in_stats',
            'type'    => 'checkbox',
            'label'   => __('backend.statistical'),
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0 border-right-0'],
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'    => 'note',
            'type'    => 'text',
            'label'   => __('backend.note_parishioners'),
            'default' => '',
            'wrapper' => ['class' => 'form-group col-md-3 mb-0 border-success py-2 border-left-0'],
            'tab'     => __('backend.general'),
        ]);

        // ---------------------------------------------------------------
        // NHÓM 7: Trạng thái & Ngày tham gia
        // ---------------------------------------------------------------

        CRUD::addField([
            'name'    => 'status',
            'type'    => 'radio',
            'label'   => __('backend.status'),
            'options' => [0 => __('backend.draft'), 1 => __('backend.publish')],
            'default' => 1,
            'inline'  => true,
            'tab'     => __('backend.general'),
        ]);

        CRUD::addField([
            'name'                => 'joined_date',
            'type'                => 'date_picker',
            'label'               => 'Ngày gia nhập',
            'date_picker_options' => ['todayBtn' => 'linked', 'format' => 'dd-mm-yyyy', 'language' => 'vi'],
            'wrapper'             => ['class' => 'form-group col-md-3'],
            'tab'                 => __('backend.general'),
        ]);

?>
        <style type="text/css">
            .border-primary {
                border-color: #7c69ef;
                border-width: 3px;
                border-style: solid;
            }

            .border-success {
                border-color: #42ba96;
                border-width: 3px;
                border-style: solid;
            }

            .border-danger {
                border-color: #df4759;
                border-width: 3px;
                border-style: solid;
            }

            .border-warning {
                border-color: #ffc107;
                border-width: 3px;
                border-style: solid;
            }

            .border-info {
                border-color: #467fd0;
                border-width: 3px;
                border-style: solid;
            }

            .border-dark {
                border-color: #161c2d;
                border-width: 3px;
                border-style: solid;
            }

            .border-secondary {
                border-width: 3px;
                border-style: solid;
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // Đổi giáo phận → load giáo hạt
                $("select[name='diocese_id']").change(function() {
                    var diocese = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/Parishioners",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            did: diocese
                        },
                        beforeSend: function() {
                            $("select[name='deanery_id'] option[value]").remove();
                            $("select[name='parish_id'] option[value]").remove();
                            $("select[name='parish_area_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $("select[name='deanery_id']").append('<option value="">-- Chọn giáo hạt --</option>');
                            $.each(data, function(k, v) {
                                $("select[name='deanery_id']").append("<option value=" + v.id + ">" + v.name + "</option>");
                            });
                        }
                    });
                });

                // Đổi giáo hạt → load giáo xứ
                $("select[name='deanery_id']").change(function() {
                    var deid = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/Parishioners",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            deid: deid
                        },
                        beforeSend: function() {
                            $("select[name='parish_id'] option[value]").remove();
                            $("select[name='parish_area_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $("select[name='parish_id']").append('<option value="">-- Chọn giáo xứ --</option>');
                            $.each(data, function(k, v) {
                                $("select[name='parish_id']").append("<option value=" + v.id + ">" + v.name + "</option>");
                            });
                        }
                    });
                });

                // Đổi giáo xứ → load giáo họ
                $("select[name='parish_id']").change(function() {
                    var pid = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/Parishioners",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            pid: pid
                        },
                        beforeSend: function() {
                            $("select[name='parish_area_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $("select[name='parish_area_id']").append('<option value="">-- Chọn giáo họ --</option>');
                            $.each(data, function(k, v) {
                                $("select[name='parish_area_id']").append("<option value=" + v.id + ">" + v.name + "</option>");
                            });
                        }
                    });
                });

                // Đổi tỉnh nguyên quán → load xã
                $("select[name='permanent_province']").change(function() {
                    var province = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/Parishioners",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            province: province
                        },
                        beforeSend: function() {
                            $("select[name='permanent_ward_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $.each(data, function(k, v) {
                                $("select[name='permanent_ward_id']").append("<option value=" + v.xaid + ">" + v.name + "</option>");
                            });
                        }
                    });
                });

                // Đổi tỉnh tạm trú → load xã
                $("select[name='temporary_province']").change(function() {
                    var province = $(this).find('option:selected').val();
                    $.ajax({
                        url: "/api/Parishioners",
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            province: province
                        },
                        beforeSend: function() {
                            $("select[name='temporary_ward_id'] option[value]").remove();
                        },
                        success: function(data) {
                            $.each(data, function(k, v) {
                                $("select[name='temporary_ward_id']").append("<option value=" + v.xaid + ">" + v.name + "</option>");
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

    /**
     * Helper dùng chung: lấy {id => name} từ một table đơn giản.
     */
    private function getOptions(string $table): array
    {
        $rows = DB::table($table)->orderBy('id')->get();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->id] = $row->name;
        }
        return $result;
    }

    private function getDioceseOptions(): array
    {
        $rows = DB::table('dioceses')->where('status', 1)->orderBy('id')->get();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->id] = $row->name;
        }
        return $result;
    }

    private function getSaintOptions(): array
    {
        $rows = DB::table('holymanagements')->orderBy('id')->get();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->id] = $row->name;
        }
        return $result;
    }

    /**
     * Load giáo hạt theo diocese_id của parishioner đang edit.
     */
    public function GetDeanerys($id): array
    {
        $array_dea = [];
        if (!empty($id)) {
            $parishioner = DB::table('parishioners_new')
                ->where('id', $id)
                ->first();

            if ($parishioner) {
                $rows = DB::table('deanerys')
                    ->where('did', $parishioner->diocese_id)
                    ->where('status', 1)
                    ->orderBy('id')
                    ->get();
                foreach ($rows as $row) {
                    $array_dea[$row->id] = $row->name;
                }
            }
        }
        return $array_dea;
    }

    /**
     * Load giáo xứ theo deanery_id + diocese_id của parishioner đang edit.
     */
    public function GetParish($id): array
    {
        $array_par = [];
        if (!empty($id)) {
            $parishioner = DB::table('parishioners_new')
                ->where('id', $id)
                ->first();

            if ($parishioner) {
                $rows = DB::table('parishes')
                    ->where('diocese_id', $parishioner->diocese_id)
                    ->where('deanery_id', $parishioner->deanery_id)
                    ->where('status', 1)
                    ->orderBy('id')
                    ->get();
                foreach ($rows as $row) {
                    $array_par[$row->id] = $row->name;
                }
            }
        }
        return $array_par;
    }

    /**
     * Load giáo họ theo parish_id của parishioner đang edit.
     */
    public function GetParishAreas($id): array
    {
        $array_areas = [];
        if (!empty($id)) {
            $parishioner = DB::table('parishioners_new')
                ->where('id', $id)
                ->first();

            if ($parishioner) {
                $rows = DB::table('parish_groups')
                    ->where('parish_id', $parishioner->parish_id)
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get();
                foreach ($rows as $row) {
                    $array_areas[$row->id] = $row->name;
                }
            }
        }
        return $array_areas;
    }

    /**
     * Load xã/phường theo permanent_province của parishioner đang edit.
     */
    public function GetXaPermanent($id): array
    {
        @include(resource_path() . '/cities/xa_phuong_thitran.php');
        $array_xa = [];
        if (!empty($id)) {
            $parishioner = DB::table('parishioners_new')->where('id', $id)->first();
            if ($parishioner && !empty($parishioner->permanent_province)) {
                foreach ($xa_phuong_thitran as $xa) {
                    if ($xa['matp'] == $parishioner->permanent_province) {
                        $array_xa[$xa['xaid']] = $xa['name'];
                    }
                }
            }
        }
        return $array_xa;
    }

    /**
     * Load xã/phường theo temporary_province của parishioner đang edit.
     */
    public function GetXaTemporary($id): array
    {
        @include(resource_path() . '/cities/xa_phuong_thitran.php');
        $array_xa = [];
        if (!empty($id)) {
            $parishioner = DB::table('parishioners_new')->where('id', $id)->first();
            if ($parishioner && !empty($parishioner->temporary_province)) {
                foreach ($xa_phuong_thitran as $xa) {
                    if ($xa['matp'] == $parishioner->temporary_province) {
                        $array_xa[$xa['xaid']] = $xa['name'];
                    }
                }
            }
        }
        return $array_xa;
    }
}
