<?php

namespace App\Services\User;

use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateCatechistAccount
{
    public function create(string $name, string $email, string $password, ?int $parishId = null): User
    {
        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => $password, // plain — User mutator hashes once
            'parish_id' => $parishId,
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'catechist'],
            ['guard_name' => 'web']
        );

        $user->assignRole($role);

        return $user;
    }
}
