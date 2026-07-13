<?php

namespace App\Actions\ParishAdmin;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\ParishNew;
use App\Models\User;
use App\Notifications\ParishAdminRegistrationApproved;
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

        $allowedRoles = array_keys(config('parish-admin-registration.roles', []));
        $roles = array_values(array_intersect($request->requested_roles ?? [], $allowedRoles));

        if ($roles === []) {
            $roles = ['parish_admin'];
        }

        return DB::transaction(function () use ($request, $reviewer, $roles) {
            $parishId = $request->parish_id;

            if (! $parishId) {
                $name = trim((string) $request->custom_parish_name);

                if ($name === '' || ! $request->diocese_id || ! $request->deanery_id) {
                    throw new InvalidArgumentException('Thiếu giáo phận, giáo hạt hoặc tên giáo xứ mới để tạo.');
                }

                $parish = ParishNew::create([
                    'name'       => $name,
                    'diocese_id' => $request->diocese_id,
                    'deanery_id' => $request->deanery_id,
                    'status'     => true,
                ]);

                $parishId = $parish->id;
            }

            $displayName = trim((string) $request->name);
            if ($displayName === '') {
                $displayName = strstr($request->email, '@', true) ?: $request->email;
            }

            $user = User::create([
                'name'      => $displayName,
                'email'     => $request->email,
                'parish_id' => $parishId,
                // password_hash đã Hash::make() lúc đăng ký; mutator User tránh hash lại.
                'password'  => $request->password_hash,
            ]);

            $user->syncRoles($roles);

            $request->update([
                'status'      => ParishAdminRegistrationRequest::STATUS_APPROVED,
                'parish_id'   => $parishId,
                'user_id'     => $user->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $freshRequest = $request->fresh();
            notify_users($user, new ParishAdminRegistrationApproved($freshRequest));
            app(\App\Services\Admin\SystemOverviewService::class)->forget();

            return [
                'user'    => $user,
                'request' => $freshRequest,
            ];
        });
    }
}
