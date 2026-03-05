<?php

namespace App\Policies;

use App\Models\Lop;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * LopPolicy
 *
 * Chính sách phân quyền cho tài nguyên Lớp học (Lop)
 *
 * Nguyên tắc:
 * - Admin tổng: toàn quyền (xem / tạo / sửa / xóa)
 * - Decen (quản trị xứ): chỉ thao tác trong giáo xứ của mình
 * - User thường: không có quyền
 */
class LopPolicy
{
    use HandlesAuthorization;

    /**
     * BEFORE HOOK
     *
     * Được Laravel gọi TRƯỚC mọi method bên dưới.
     *
     * Nếu return true  → cho phép ngay, không check các rule khác
     * Nếu return false → cấm ngay
     * Nếu return null  → tiếp tục kiểm tra method cụ thể
     *
     * Ở đây:
     * - Admin tổng được toàn quyền trên Lop
     */
    public function before(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * VIEW ANY
     *
     * Kiểm tra quyền XEM DANH SÁCH LỚP
     *
     * Áp dụng cho:
     * - Trang danh sách lớp
     * - Các API / Livewire load list Lop
     *
     * Quy tắc:
     * - Chỉ Decen (quản trị xứ) mới được xem
     */
    public function viewAny(User $user): bool
    {
        return $user->isDecen();
    }

    /**
     * VIEW
     *
     * Kiểm tra quyền XEM CHI TIẾT 1 LỚP
     *
     * Điều kiện:
     * - User là Decen
     * - Lớp thuộc giáo xứ của user
     *
     * Ngăn chặn:
     * - Decen xem lớp của xứ khác
     */
    public function view(User $user, Lop $lop): bool
    {
        return $user->isDecen()
            && $user->parish_id === $lop->pid;
    }

    /**
     * CREATE
     *
     * Kiểm tra quyền TẠO LỚP MỚI
     *
     * Áp dụng cho:
     * - Mở form tạo lớp
     * - Lưu lớp mới
     *
     * Quy tắc:
     * - Chỉ Decen mới được tạo lớp
     */
    public function create(User $user): bool
    {
        return $user->isDecen();
    }

    /**
     * UPDATE
     *
     * Kiểm tra quyền CẬP NHẬT LỚP
     *
     * Điều kiện:
     * - User là Decen
     * - Lớp thuộc giáo xứ của user
     *
     * Ngăn chặn:
     * - Sửa lớp của xứ khác
     */
    public function update(User $user, Lop $lop): bool
    {
        return $user->isDecen()
            && $user->parish_id === $lop->pid;
    }

    /**
     * DELETE
     *
     * Kiểm tra quyền XÓA LỚP
     *
     * Chính sách hiện tại:
     * - Chỉ Admin tổng mới được xóa lớp
     *
     * Decen:
     * - KHÔNG BAO GIỜ được xóa (tránh mất dữ liệu lịch sử)
     */
    public function delete(User $user, Lop $lop): bool
    {
        return false;
    }
}
