<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateCatechistAccount
{
    public function create(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'catechist'],
            ['guard_name' => 'web']
        );

        $user->assignRole($role);

        return $user;
    }
}
