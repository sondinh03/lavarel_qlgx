<?php

namespace App\Policies;

use App\Models\ParishionerRegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParishionerRegistrationRequestPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    public function view(User $user, ParishionerRegistrationRequest $request): bool
    {
        return $user->hasRole('parish_admin')
            && (int) $user->parish_id === (int) $request->parish_id;
    }

    public function approve(User $user, ParishionerRegistrationRequest $request): bool
    {
        return $user->hasRole('parish_admin')
            && (int) $user->parish_id === (int) $request->parish_id
            && $request->isPending();
    }

    public function reject(User $user, ParishionerRegistrationRequest $request): bool
    {
        return $this->approve($user, $request);
    }
}
