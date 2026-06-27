<?php

namespace App\Http\Livewire\Parishioners;

use App\Actions\Parishioner\ApproveParishionerRegistrationAction;
use App\Models\Association;
use App\Models\Marriage;
use App\Models\ParishGroup;
use App\Models\ParishionerRegistrationRequest;
use App\Models\Sacrament;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ParishionerRegistrationShow extends Component
{
    use AuthorizesRequests;

    public ParishionerRegistrationRequest $registration;

    public string $rejectionReason = '';

    public string $adminNote = '';

    public bool $showRejectModal = false;

    public bool $showApproveModal = false;

    public function mount(ParishionerRegistrationRequest $registration): void
    {
        $this->authorize('view', $registration);

        $parishId = auth()->user()?->parishId();
        if (! $parishId || (int) $registration->parish_id !== (int) $parishId) {
            abort(404);
        }

        $this->registration = $registration;
        $this->adminNote = $registration->admin_note ?? '';
    }

    public function openApproveModal(): void
    {
        $this->authorize('approve', $this->registration);
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->registration);

        try {
            $result = app(ApproveParishionerRegistrationAction::class)->handle(
                $this->registration,
                auth()->user(),
                trim($this->adminNote) ?: null
            );

            $this->registration = $result['request'];
            $this->showApproveModal = false;
            $message = ! empty($result['family'])
                ? 'Đã duyệt và tạo hộ gia đình trong hệ thống.'
                : 'Đã duyệt và thêm giáo dân vào hệ thống.';
            $this->emit('toast', 'message', $message);

            if (! empty($result['family'])) {
                $this->redirect(route('families.show', $result['family']->id));
            } else {
                $this->redirect(route('parishioners.show', $result['parishioner']));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to approve parishioner registration', [
                'id'    => $this->registration->id,
                'error' => $e->getMessage(),
            ]);
            $this->emit('toast', 'error', 'Không thể duyệt yêu cầu. Vui lòng thử lại.');
        }
    }

    public function openRejectModal(): void
    {
        $this->authorize('reject', $this->registration);
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectionReason = '';
    }

    public function reject(): void
    {
        $this->authorize('reject', $this->registration);

        $this->validate([
            'rejectionReason' => 'required|string|max:1000',
        ], [
            'rejectionReason.required' => 'Vui lòng nhập lý do từ chối',
        ]);

        $this->registration->update([
            'status'           => ParishionerRegistrationRequest::STATUS_REJECTED,
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'admin_note'       => trim($this->adminNote) ?: null,
        ]);

        $this->registration->refresh();
        $this->showRejectModal = false;
        $this->emit('toast', 'message', 'Đã từ chối yêu cầu đăng ký.');
    }

    public function render()
    {
        $payload = $this->registration->payload;
        $isFamilyRegister = ($payload['version'] ?? 1) >= 2;
        $familyData = $payload['family'] ?? [];
        $members = $isFamilyRegister ? ($payload['members'] ?? []) : [];
        $parishAreaId = $isFamilyRegister
            ? ($familyData['parish_area_id'] ?? null)
            : ($payload['parish_area_id'] ?? null);
        $parishGroupName = $parishAreaId
            ? ParishGroup::find((int) $parishAreaId)?->name
            : null;
        $associationIds = collect($members)->pluck('association_id')->filter()->unique()->map(fn ($id) => (int) $id);
        $associationNames = $associationIds->isNotEmpty()
            ? Association::whereIn('id', $associationIds)->pluck('name', 'id')->toArray()
            : [];

        return view('livewire.parishioners.parishioner-registration-show', [
            'payload'          => $payload,
            'isFamilyRegister' => $isFamilyRegister,
            'familyData'       => $familyData,
            'members'          => $members,
            'parishGroupName'  => $parishGroupName,
            'associationNames' => $associationNames,
            'marriages'        => $this->registration->marriages ?? [],
            'sacraments'       => $this->registration->sacraments ?? [],
            'typeOptions'      => Sacrament::typeOptions(),
            'marriageStatuses' => \App\Models\Marriage::statusOptions(),
            'familyRoles'      => config('parishioner-registration.family_roles', []),
            'marriedLabels'    => config('parishioner.married', []),
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
