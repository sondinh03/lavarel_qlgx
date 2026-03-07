<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateParishionerToParishionersNew extends Migration
{
    public function up(): void
    {
        DB::table('parishioners')
            ->where('pid', 31)
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                $insert = $rows->map(fn($row) => [
                    'id'                   => $row->id,

                    // Thông tin cá nhân
                    'last_name'            => $row->last_name,
                    'first_name'           => $row->name,
                    'gender'               => $row->sex == 1 ? 'male' : 'female',
                    'birthday'             => $row->birthday,
                    'saint_id'             => $row->holy,
                    'phone'                => $row->phone ? (string) $row->phone : null,
                    'email'                => $row->email,
                    'cccd'                 => $row->cccd ? (string) $row->cccd : null,
                    'avatar_path'          => $row->image,
                    'note'                 => $row->note,

                    // Phân loại
                    'parish_id'            => $row->pid,
                    'deanery_id'           => $row->deid,
                    'diocese_id'           => $row->did,
                    'parish_area_id'       => $row->paid,
                    'ethnic'               => $row->ethnic,
                    'career'               => $row->career,
                    'education_level'      => $row->level,
                    'catechism_level'      => $row->study,
                    'position'             => $row->position,
                    'language'             => $row->language,
                    'holy_order_status'    => $row->holy,
                    'is_new_convert'       => (bool) $row->new_convert,
                    'is_included_in_stats' => (bool) $row->statistical,
                    'married'              => $row->married,
                    'level'                => $row->level,
                    'status'               => $row->status,

                    // Địa chỉ thường trú
                    'permanent_ward_id'    => $row->ward,
                    'permanent_province'   => $row->province,
                    'permanent_residence'  => $row->residence,

                    // Địa chỉ tạm trú
                    'temporary_ward_id'    => $row->resi_ward,
                    'temporary_province'   => $row->resi_province,
                    'temporary_residence'  => null,

                    // Quê quán & gia đình
                    'origin'               => $row->origin,
                    'father_name'          => $row->father,
                    'mother_name'          => $row->mother,

                    // Giáo xứ
                    'is_active'            => (bool) $row->status,

                    'created_at'           => $row->created_at,
                    'updated_at'           => $row->updated_at,
                ])->toArray();

                DB::table('parishioners_new')->insertOrIgnore($insert);
            });
    }

    public function down(): void
    {
        DB::table('parishioners_new')->where('parish_id', 31)->delete();
    }
}
