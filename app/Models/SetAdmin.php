<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SetAdmin
 *
 * Model đại diện cho bảng set_admins
 *
 * Mục đích:
 * - Xác định user nào là ADMIN TỔNG của hệ thống
 * - Admin tổng có toàn quyền trên mọi giáo xứ và dữ liệu
 *
 * Quy ước:
 * - use    : khóa ngoại trỏ tới users.id
 * - status : 1 = admin đang active, 0 = đã bị thu quyền
 *
 * Lưu ý kiến trúc:
 * - Đây là phân quyền "cứng" (system-level)
 * - Không dùng chung với role mềm (Spatie roles)
 */
class SetAdmin extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'set_admins';
    protected $guarded = ['id'];
    protected $fillable = [
        'use',     // user id
        'status',  // 1 = active, 0 = inactive
    ];

    // protected $casts = [
    //     'status' => 'boolean',
    // ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
