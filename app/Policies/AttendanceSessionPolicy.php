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
            || app(\App\Services\CatechistAccess::class)->canCreateAttendanceSessions($user);
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
        return app(\App\Services\CatechistAccess::class)->canCreateAttendanceSessions($user);
    }

    public function update(User $user, AttendanceSession $session): bool
    {
        $class = $session->catechismClass;

        if (! $class || (int) $user->parish_id !== (int) $class->parish_id) {
            return false;
        }

        return app(\App\Services\CatechistAccess::class)->canCreateAttendanceSessions($user);
    }

    public function delete(User $user, AttendanceSession $session): bool
    {
        $class = $session->catechismClass;

        return $user->canManageCatechism()
            && $class
            && (int) $user->parish_id === (int) $class->parish_id;
    }
}
