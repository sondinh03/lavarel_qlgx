<?php

namespace App\Policies;

use App\Models\Parishioners;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy cho Parishioners
 * 
 * Quy tắc phân quyền:
 * - Admin hệ thống: Có quyền làm mọi thứ với mọi giáo xứ
 * - Decen (Admin xứ): Chỉ có quyền với giáo dân trong xứ mình
 * - User thường: Không có quyền gì
 */

class ParishionersPolicy
{
    use HandlesAuthorization;

    /**
     * Xem danh sách giáo dân
     * 
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Admin hệ thống hoặc Decen (quản lý xứ) đều được xem
        return $user->isAdmin() || $user->isDecen();
    }

    /**
     * Xem chi tiết 1 giáo dân
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function view(User $user, Parishioners $parishioner): bool
    {
        // Admin hệ thống: xem được tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Decen: chỉ xem giáo dân trong xứ mình
        if ($user->isDecen()) {
            return $parishioner->pid === $user->parish_id;
        }

        // User thường: không có quyền
        return false;
    }

    /**
     * Tạo giáo dân mới
     * 
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Admin hệ thống hoặc Decen đều có thể tạo
        return $user->isAdmin() || $user->isDecen();
    }

    /**
     * Cập nhật thông tin giáo dân
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function update(User $user, Parishioners $parishioner): bool
    {
        // Admin hệ thống: sửa được tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Decen: chỉ sửa giáo dân trong xứ mình
        if ($user->isDecen()) {
            return $parishioner->pid === $user->parish_id;
        }

        return false;
    }

    /**
     * Xóa giáo dân
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function delete(User $user, Parishioners $parishioner): bool
    {
        // CHỈ Admin hệ thống mới được xóa
        // Decen KHÔNG được xóa (để tránh mất dữ liệu quan trọng)
        return $user->isAdmin();
    }

    /**
     * Khôi phục giáo dân đã xóa (soft delete)
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function restore(User $user, Parishioners $parishioner): bool
    {
        // Chỉ Admin hệ thống
        return $user->isAdmin();
    }

    /**
     * Xóa vĩnh viễn (force delete)
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function forceDelete(User $user, Parishioners $parishioner): bool
    {
        // Chỉ Admin hệ thống
        return $user->isAdmin();
    }

    /**
     * Thay đổi trạng thái (active/inactive)
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function toggleStatus(User $user, Parishioners $parishioner): bool
    {
        // Admin hệ thống: thay đổi được tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Decen: chỉ thay đổi giáo dân trong xứ mình
        if ($user->isDecen()) {
            return $parishioner->pid === $user->parish_id;
        }

        return false;
    }

    /**
     * Upload/thay đổi ảnh đại diện
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function uploadImage(User $user, Parishioners $parishioner): bool
    {
        // Giống quyền update
        return $this->update($user, $parishioner);
    }

    /**
     * Liên kết với học sinh
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function linkStudent(User $user, Parishioners $parishioner): bool
    {
        // Admin hệ thống hoặc Decen của xứ đó
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDecen()) {
            return $parishioner->pid === $user->parish_id;
        }

        return false;
    }

    /**
     * Xem danh sách học sinh liên kết
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function viewStudents(User $user, Parishioners $parishioner): bool
    {
        // Giống quyền view
        return $this->view($user, $parishioner);
    }

    /**
     * Import giáo dân từ Excel
     * 
     * @param User $user
     * @return bool
     */
    public function import(User $user): bool
    {
        // Admin hệ thống hoặc Decen
        return $user->isAdmin() || $user->isDecen();
    }

    /**
     * Export danh sách giáo dân
     * 
     * @param User $user
     * @return bool
     */
    public function export(User $user): bool
    {
        // Admin hệ thống hoặc Decen
        return $user->isAdmin() || $user->isDecen();
    }

    /**
     * Xem thống kê giáo dân
     * 
     * @param User $user
     * @return bool
     */
    public function viewStatistics(User $user): bool
    {
        // Admin hệ thống hoặc Decen
        return $user->isAdmin() || $user->isDecen();
    }

    /**
     * Gửi thông báo/email cho giáo dân
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function sendNotification(User $user, Parishioners $parishioner): bool
    {
        // Admin hệ thống hoặc Decen của xứ đó
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDecen()) {
            return $parishioner->pid === $user->parish_id;
        }

        return false;
    }

    /**
     * Xem lịch sử thay đổi (audit log)
     * 
     * @param User $user
     * @param Parishioners $parishioner
     * @return bool
     */
    public function viewHistory(User $user, Parishioners $parishioner): bool
    {
        // Chỉ Admin hệ thống
        return $user->isAdmin();
    }
}
