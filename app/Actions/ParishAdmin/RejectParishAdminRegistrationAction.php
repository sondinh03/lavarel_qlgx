<?php

namespace App\Actions\ParishAdmin;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\User;
use App\Notifications\ParishAdminRegistrationRejected;
use App\Services\Admin\SystemOverviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class RejectParishAdminRegistrationAction
{
    public function handle(
        ParishAdminRegistrationRequest $request,
        User $reviewer,
        ?string $rejectionReason = null
    ): ParishAdminRegistrationRequest {
        if (! $request->isPending()) {
            throw new InvalidArgumentException('Yêu cầu đã được xử lý.');
        }

        $rejectedRequest = DB::transaction(function () use ($request, $reviewer, $rejectionReason) {
            $request->update([
                'status'            => ParishAdminRegistrationRequest::STATUS_REJECTED,
                'reviewed_by'       => $reviewer->id,
                'reviewed_at'       => now(),
                'rejection_reason'  => $rejectionReason,
            ]);

            $fresh = $request->fresh();
            app(SystemOverviewService::class)->forget();

            return $fresh;
        });

        try {
            Notification::route('mail', [
                $rejectedRequest->email => $rejectedRequest->name ?: $rejectedRequest->email,
            ])->notify(new ParishAdminRegistrationRejected($rejectedRequest));
        } catch (\Throwable $e) {
            report($e);
        }

        return $rejectedRequest;
    }
}
