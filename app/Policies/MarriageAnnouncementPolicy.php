<?php

namespace App\Policies;

use App\Models\MarriageAnnouncement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarriageAnnouncementPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageParishioners() || $user->isCatechist();
    }

    public function view(User $user, MarriageAnnouncement $announcement): bool
    {
        if ($user->canManageParishioners() || $user->isCatechist()) {
            return (int) $user->parish_id === (int) $announcement->pid;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function update(User $user, MarriageAnnouncement $announcement): bool
    {
        return $user->canManageParishioners()
            && (int) $user->parish_id === (int) $announcement->pid;
    }

    public function delete(User $user, MarriageAnnouncement $announcement): bool
    {
        return $user->canManageParishioners()
            && (int) $user->parish_id === (int) $announcement->pid;
    }

    public function createMarriage(User $user, MarriageAnnouncement $announcement): bool
    {
        return $this->update($user, $announcement)
            && (int) $announcement->status === 1;
    }
}
