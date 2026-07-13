<?php

namespace App\Policies;

use App\Models\CatechismClass;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CatechismClassPolicy
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
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    /**
     * Xem chi tiết — admin giáo lý / GLV cùng xứ xem mọi lớp trong giáo xứ
     */
    public function view(User $user, CatechismClass $class): bool
    {
        if ($user->canManageCatechism() || $user->isCatechist()) {
            return $user->parish_id === $class->parish_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    public function update(User $user, CatechismClass $class): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $class->parish_id;
    }

    public function delete(User $user, CatechismClass $class): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $class->parish_id;
    }
}
