<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Class Decen
 *
 * ================================
 * DECEN = PARISH ADMIN (QUẢN TRỊ XỨ)
 * ================================
 *
 * Đây KHÔNG phải là role trong bảng users.
 *
 * Decen đại diện cho một tài khoản QUẢN TRỊ 1 GIÁO XỨ,
 * được gán cho một User thông qua cột `use`.
 *
 * Kiến trúc hệ thống:
 * - users        : chỉ dùng cho authentication (đăng nhập)
 * - decen        : quyền nghiệp vụ quản trị giáo xứ
 * - admin        : quản trị tổng toàn hệ thống
 *
 * Một User:
 * - Có thể có hoặc không có bản ghi Decen
 * - Nếu có Decen (status = 1) → là quản trị xứ
 *
 * Authorization rules:
 * - Decen chỉ được thao tác dữ liệu thuộc giáo xứ của mình (pid / parish)
 * - Không có quyền vượt giáo xứ
 * - Không có quyền quản trị tổng
 *
 * LƯU Ý:
 * - "Decen" chỉ là tên nội bộ của hệ thống
 * - Nghiệp vụ thực tế: Parish Admin
 */
class Decen extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'decen';
    protected $guarded = ['id'];
    protected $fillable = [
        'id',

        /**
         * ID của user (users.id)
         * Liên kết Decen ↔ User
         */
        'use',

        /**
         * Các field nghiệp vụ legacy
         * (giữ nguyên theo DB cũ)
         */
        'did',
        'deid',

        /**
         * ID giáo xứ mà Decen quản lý
         */
        'pid',
        'parish',

        /**
         * Quyền liên quan đến học sinh (legacy flag)
         */
        'student',

        /**
         * Trạng thái Decen
         * 1 = active
         * 0 = inactive / revoked
         */
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS (nên dùng)
    |--------------------------------------------------------------------------
    */

    /**
     * User sở hữu quyền quản trị xứ này
     */
    /*
    public function user()
    {
        return $this->belongsTo(User::class, 'use', 'id');
    }
    */

    /**
     * Decen còn hiệu lực hay không
     */
    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    /**
     * ID giáo xứ mà Decen quản lý
     */
    public function parishId(): ?int
    {
        return $this->pid;
    }
}
