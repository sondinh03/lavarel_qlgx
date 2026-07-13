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
    public function before(?User $user): ?bool
    {
        if ($user?->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageParishioners()
            || $user->isCatechist();
    }

    /**
     * Hồ sơ giáo dân công khai (kể cả khách chưa đăng nhập).
     */
    public function view(?User $user, Parishioner $parishioner): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function update(User $user, Parishioner $parishioner): bool
    {
        return $user->canManageParishioners()
            && $user->parish_id === $parishioner->parish_id;
    }

    public function delete(User $user, Parishioner $parishioner): bool
    {
        return $user->canManageParishioners()
            && $user->parish_id === $parishioner->parish_id;
    }
}
