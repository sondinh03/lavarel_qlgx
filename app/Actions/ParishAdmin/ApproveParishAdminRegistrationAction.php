<?php

namespace App\Actions\ParishAdmin;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproveParishAdminRegistrationAction
{
    /**
     * @return array{user: User, request: ParishAdminRegistrationRequest}
     */
    public function handle(ParishAdminRegistrationRequest $request, User $reviewer): array
    {
        if (! $request->isPending()) {
            throw new InvalidArgumentException('Yêu cầu đã được xử lý.');
        }

        if (User::whereRaw('LOWER(email) = ?', [strtolower(trim($request->email))])->exists()) {
            throw new InvalidArgumentException('Email đã được sử dụng trong hệ thống.');
        }

        return DB::transaction(function () use ($request, $reviewer) {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'parish_id' => $request->parish_id,
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => $request->password_hash]);

            $user = $user->fresh();
            $user->assignRole('parish_admin');

            $request->update([
                'status'      => ParishAdminRegistrationRequest::STATUS_APPROVED,
                'user_id'     => $user->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return [
                'user'    => $user,
                'request' => $request->fresh(),
            ];
        });
    }
}
