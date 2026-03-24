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

    /**
     * parish_admin và super_admin đều xem được
     */
    public function viewAny(User $user): bool
    {
        return $user->isParishAdmin() || $user->isSuperAdmin();
    }

    /**
     * Chỉ super_admin — đã handle trong before()
     * parish_admin không được tạo
     */
    public function create(User $user): bool
    {
        // return false;
        return $user->isParishAdmin() || $user->isSuperAdmin();
    }

    /**
     * Chỉ super_admin — đã handle trong before()
     */
    public function update(User $user, Holymanagement $holy): bool
    {
        // return false;
        return $user->isParishAdmin() || $user->isSuperAdmin();
    }

    /**
     * Chỉ super_admin — đã handle trong before()
     */
    public function delete(User $user, Holymanagement $holy): bool
    {
        return false;
    }
}
