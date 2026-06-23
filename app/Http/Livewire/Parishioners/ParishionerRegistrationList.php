<?php

namespace App\Http\Livewire\Parishioners;

use App\Actions\Parishioner\ApproveParishionerRegistrationAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishionerRegistrationRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ParishionerRegistrationList extends BaseComponent
{
    public string $statusFilter = 'pending';

    public bool $showRejectModal = false;

    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    protected array $allowedSortFields = ['created_at', 'submitted_name', 'status'];

    protected function queryString(): array
    {
        return array_merge([
            'statusFilter' => ['except' => 'pending', 'as' => 'status'],
            'sortField'    => ['except' => 'created_at', 'as' => 'sort'],
            'sortDirection'=> ['except' => 'desc', 'as' => 'dir'],
        ], parent::queryString());
    }

    public function mount(): void
    {
        $this->authorize('viewAny', ParishionerRegistrationRequest::class);
        parent::mount();
        $this->requireParishId();
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
    }

    protected function loadInitialData(): void {}

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $registration = ParishionerRegistrationRequest::query()
            ->where('parish_id', $this->parishId)
            ->findOrFail($id);

        $this->authorize('approve', $registration);

        try {
            $result = app(ApproveParishionerRegistrationAction::class)->handle(
                $registration,
                auth()->user()
            );

            $message = ! empty($result['family'])
                ? 'Đã duyệt và tạo hộ gia đình.'
                : 'Đã duyệt và thêm giáo dân.';
            $this->emit('toast', 'message', $message);

            if (! empty($result['family'])) {
                $this->redirect(route('families.show', $result['family']->id));
            } else {
                $this->redirect(route('parishioners.show', $result['parishioner']));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to approve parishioner registration from list', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            $this->emit('toast', 'error', 'Không thể duyệt yêu cầu. Vui lòng thử lại.');
        }
    }

    public function openRejectModal(int $id): void
    {
        $registration = ParishionerRegistrationRequest::query()
            ->where('parish_id', $this->parishId)
            ->findOrFail($id);

        $this->authorize('reject', $registration);
        $this->rejectingId = $id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }

    public function reject(): void
    {
        if (! $this->rejectingId) {
            return;
        }

        $registration = ParishionerRegistrationRequest::query()
            ->where('parish_id', $this->parishId)
            ->findOrFail($this->rejectingId);

        $this->authorize('reject', $registration);

        $this->validate([
            'rejectionReason' => 'required|string|max:1000',
        ], [
            'rejectionReason.required' => 'Vui lòng nhập lý do từ chối',
        ]);

        $registration->update([
            'status'           => ParishionerRegistrationRequest::STATUS_REJECTED,
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->closeRejectModal();
        $this->emit('toast', 'message', 'Đã từ chối yêu cầu đăng ký.');
    }

    public static function pendingCountForParish(int $parishId): int
    {
        return ParishionerRegistrationRequest::query()
            ->where('parish_id', $parishId)
            ->where('status', ParishionerRegistrationRequest::STATUS_PENDING)
            ->count();
    }

    protected function getRegistrations(): LengthAwarePaginator
    {
        $query = ParishionerRegistrationRequest::query()
            ->where('parish_id', $this->parishId)
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('submitted_name', 'like', $term)
                        ->orWhere('submitted_phone', 'like', $term)
                        ->orWhere('reference_code', 'like', $term);
                });
            });

        $sortField = in_array($this->sortField, $this->allowedSortFields, true)
            ? $this->sortField
            : 'created_at';

        return $query
            ->orderBy($sortField, $this->sortDirection === 'asc' ? 'asc' : 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-registration-list', [
            'registrations' => $this->getRegistrations(),
            'statuses'      => config('parishioner-registration.statuses', []),
            'pendingCount'  => self::pendingCountForParish($this->parishId),
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
