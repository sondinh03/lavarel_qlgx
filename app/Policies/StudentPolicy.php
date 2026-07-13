<?php

namespace App\Policies;

use App\Models\StudentNew;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * SuperAdmin bỏ qua tất cả checks
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Xem danh sách học sinh
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    /**
     * Xem chi tiết 1 học sinh trong cùng xứ
     */
    public function view(User $user, StudentNew $student): bool
    {
        if ($user->canManageCatechism() || $user->isCatechist()) {
            return $user->parish_id === $student->parish_id;
        }

        return false;
    }

    /**
     * Tạo học sinh mới
     */
    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    /**
     * Sửa học sinh trong cùng xứ
     */
    public function update(User $user, StudentNew $student): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $student->parish_id;
    }

    /**
     * Xóa học sinh trong cùng xứ
     */
    public function delete(User $user, StudentNew $student): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $student->parish_id;
    }
}
