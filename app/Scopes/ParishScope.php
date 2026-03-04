<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class ParishScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Không filter khi chạy console (migrate, seeder, queue...)
        if (app()->runningInConsole()) {
            return;
        }

        // Chưa login thì bỏ qua
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Super admin thấy tất cả
        if ($user->hasRole('super_admin')) {
            return;
        }

        // Model phải có parish_id mới filter
        if (Schema::hasColumn($model->getTable(), 'parish_id')) {
            $builder->where(
                $model->getTable() . '.parish_id',
                $user->parish_id
            );
        }
    }
}
