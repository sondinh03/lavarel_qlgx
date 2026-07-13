<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin bỏ qua tất cả checks
     */
    public function before(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Xem danh sách phiên
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    /**
     * Xem chi tiết — admin / GLV cùng xứ xem mọi phiên trong giáo xứ
     */
    public function view(User $user, AttendanceSession $session): bool
    {
        $class = $session->catechismClass;

        if (!$class) {
            return false;
        }

        if ($user->canManageCatechism() || $user->isCatechist()) {
            return (int) $user->parish_id === (int) $class->parish_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    public function update(User $user, AttendanceSession $session): bool
    {
        $class = $session->catechismClass;

        return $user->canManageCatechism()
            && $class
            && (int) $user->parish_id === (int) $class->parish_id;
    }

    public function delete(User $user, AttendanceSession $session): bool
    {
        $class = $session->catechismClass;

        return $user->canManageCatechism()
            && $class
            && (int) $user->parish_id === (int) $class->parish_id;
    }
}
