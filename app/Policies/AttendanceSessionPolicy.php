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
     * Xem danh sách lớp
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin')
            || $user->hasRole('catechist');
    }

    /**
     * Xem chi tiết 
     */
    public function view(User $user, AttendanceSession $session): bool
    {
        // parish_admin xem mọi phiên điểm danh trong xứ
        if ($user->hasRole('parish_admin')) {
            return $user->parish_id === $session->class->parish_id;
        }

        // catechist chỉ xem lớp mình dạy
        if ($user->hasRole('catechist')) {
            return $session->class->teachers()
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    public function update(User $user, AttendanceSession $session): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $session->class->parish_id;
    }

    public function delete(User $user, AttendanceSession $session): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $session->class->parish_id;
    }
}
