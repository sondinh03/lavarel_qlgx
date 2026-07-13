<?php

namespace App\Policies;

use App\Models\Holymanagement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HolymanagementPolicy
{
    use HandlesAuthorization;

    /**
     * Super admin bypass tất cả
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function create(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function update(User $user, Holymanagement $holy): bool
    {
        return $user->canManageParishioners();
    }

    public function delete(User $user, Holymanagement $holy): bool
    {
        return false;
    }
}
