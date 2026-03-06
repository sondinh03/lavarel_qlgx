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
     * parish_admin: xem học sinh trong xứ mình
     * catechist: xem học sinh trong lớp mình dạy
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin')
            || $user->hasRole('catechist');
    }

    /**
     * Xem chi tiết 1 học sinh
     */
    public function view(User $user, StudentNew $student): bool
    {
        if ($user->hasRole('parish_admin') || $user->hasRole('catechist')) {
            return $user->parish_id === $student->parish_id;
        }

        // catechist chỉ xem học sinh trong lớp mình dạy
        // if ($user->hasRole('catechist')) {
        //     return $student->classes()
        //         ->whereHas('teachers', fn($q) => $q->where('user_id', $user->id))
        //         ->exists();
        // }

        return false;
    }

    /**
     * Tạo học sinh mới - chỉ parish_admin
     */
    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    /**
     * Sửa học sinh - parish_admin trong cùng xứ
     */
    public function update(User $user, StudentNew $student): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $student->parish_id;
    }

    /**
     * Xóa học sinh - parish_admin trong cùng xứ
     * Cân nhắc: có thể set false nếu không muốn cho xóa thật
     */
    public function delete(User $user, StudentNew $student): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $student->parish_id;
    }
}
