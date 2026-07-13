<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipients
{
    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    public static function parishRoles(int $parishId, array $roles, ?int $exceptUserId = null): Collection
    {
        $query = User::query()
            ->where('parish_id', $parishId)
            ->role($roles);

        if ($exceptUserId) {
            $query->where('id', '!=', $exceptUserId);
        }

        return $query->get();
    }
}
