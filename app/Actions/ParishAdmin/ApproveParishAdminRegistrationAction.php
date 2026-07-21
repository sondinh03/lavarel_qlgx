<?php

namespace App\Actions\ParishAdmin;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\ParishGroup;
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
    public function handle(
        ParishAdminRegistrationRequest $request,
        User $reviewer,
        ?string $parishCode = null
    ): array {
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

        $normalizedCode = strtoupper(trim((string) $parishCode));

        $result = DB::transaction(function () use ($request, $reviewer, $roles, $normalizedCode) {
            $parishId = $request->parish_id;

            if (! $parishId) {
                $name = trim((string) $request->custom_parish_name);

                if ($name === '' || ! $request->diocese_id || ! $request->deanery_id) {
                    throw new InvalidArgumentException('Thiếu giáo phận, giáo hạt hoặc tên giáo xứ mới để tạo.');
                }

                if ($normalizedCode === '') {
                    throw new InvalidArgumentException('Vui lòng nhập mã giáo xứ trước khi duyệt.');
                }

                if (ParishNew::query()->where('code', $normalizedCode)->exists()) {
                    throw new InvalidArgumentException('Mã giáo xứ đã tồn tại.');
                }

                $parish = ParishNew::create([
                    'name'       => $name,
                    'code'       => $normalizedCode,
                    'diocese_id' => $request->diocese_id,
                    'deanery_id' => $request->deanery_id,
                    'status'     => true,
                ]);

                $parishId = $parish->id;

                foreach ($request->requestedParishGroupNames() as $groupName) {
                    $exists = ParishGroup::query()
                        ->where('parish_id', $parishId)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($groupName)])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    ParishGroup::create([
                        'parish_id' => $parishId,
                        'name'      => $groupName,
                        'status'    => true,
                    ]);
                }
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
            app(\App\Services\Admin\SystemOverviewService::class)->forget();

            return [
                'user'    => $user,
                'request' => $freshRequest,
            ];
        });

        try {
            notify_users($result['user'], new ParishAdminRegistrationApproved($result['request']));
        } catch (\Throwable $e) {
            report($e);
        }

        return $result;
    }
}
