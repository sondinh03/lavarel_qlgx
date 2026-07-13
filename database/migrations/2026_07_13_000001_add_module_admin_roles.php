<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['catechism_admin', 'parishioner_admin'] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }

    public function down(): void
    {
        Role::whereIn('name', ['catechism_admin', 'parishioner_admin'])
            ->where('guard_name', 'web')
            ->delete();
    }
};
