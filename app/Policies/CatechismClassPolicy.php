<?php

namespace App\Policies;

use App\Models\CatechismClass;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CatechismClassPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin')
            || $user->hasRole('catechist');
    }

    public function view(User $user, CatechismClass $class): bool
    {
        // parish_admin xem mọi lớp trong xứ
        if ($user->hasRole('parish_admin')) {
            return $user->parish_id === $class->parish_id;
        }

        // catechist chỉ xem lớp mình dạy
        if ($user->hasRole('catechist')) {
            return $class->teachers()
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    public function update(User $user, CatechismClass $class): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $class->parish_id;
    }

    public function delete(): bool
    {
        // khuyến nghị: vẫn không cho delete thật
        return false;
    }
}
