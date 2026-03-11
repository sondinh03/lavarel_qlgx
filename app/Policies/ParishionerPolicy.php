<?php

namespace App\Policies;

use App\Models\Parishioner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParishionerPolicy
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
     * Xem danh sách giáo dân
     * parish_admin: xem giáo dân trong xứ mình
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    /**
     * Xem chi tiết một giáo dân
     * parish_admin và catechist chỉ xem giáo dân trong xứ mình
     */
    public function view(User $user, Parishioner $parishioner): bool
    {
        if ($user->hasRole('parish_admin')) {
            return $user->parish_id === $parishioner->parish_id;
        }

        if ($user->hasRole('catechist')) {
            return $parishioner->classes()
                ->whereHas('teachers', fn($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Tạo giáo dân mới
     * chỉ parish_admin trong cùng xứ được tạo giáo dân mới
     */
    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    /**
     * Cập nhật giáo dân - parish_admin trong cùng xứ
     */
    public function update(User $user, Parishioner $parishioner): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $parishioner->parish_id;
    }

    /**
     * Xóa giáo dân - parish_admin trong cùng xứ
     */
    public function delete(User $user, Parishioner $parishioner): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $parishioner->parish_id;
    }
}
