<?php

namespace App\Http\Livewire\MarriageAnnouncement;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\MarriageAnnouncement;
use App\Models\Priest;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

class MarriageAnnouncementList extends BaseComponent
{
    public string $statusFilter = '';
    public string $yearFilter   = '';

    protected array $allowedSortFields = ['name', 'status', 'created_at', 'announcements_one'];

    protected function queryString(): array
    {
        return array_merge([
            'statusFilter' => ['except' => '', 'as' => 'status'],
            'yearFilter'   => ['except' => '', 'as' => 'year'],
            'sortField'    => ['except' => 'created_at', 'as' => 'sort'],
            'sortDirection'=> ['except' => 'desc', 'as' => 'dir'],
        ], parent::queryString());
    }

    public function mount(): void
    {
        $this->authorize('viewAny', MarriageAnnouncement::class);
        parent::mount();
        $this->requireParishId();
    }

    #[Override]
    public function loadInitialData(): void {}

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedYearFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = '';
        $this->yearFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $announcement = MarriageAnnouncement::findOrFail($id);
        $this->authorize('delete', $announcement);

        try {
            $announcement->parishioners()->delete();
            $announcement->delete();
            $this->emit('toast', 'message', 'Đã xóa hồ sơ rao hôn phối.');
        } catch (\Exception $e) {
            $this->logError($e, 'Delete marriage announcement failed', ['id' => $id]);
            $this->emit('toast', 'error', 'Không thể xóa hồ sơ.');
        }
    }

    protected function baseQuery()
    {
        return MarriageAnnouncement::query()
            ->with(['assignedPriest'])
            ->forParish($this->parishId)
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', (int) $this->statusFilter))
            ->when($this->yearFilter !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereYear('announcements_one', $this->yearFilter)
                        ->orWhereYear('created_at', $this->yearFilter);
                });
            })
            ->when(trim($this->search), function ($q) {
                $term = trim($this->search);
                $q->where('name', 'like', "%{$term}%");
            });
    }

    protected function formatAnnouncementDate(?string $value): string
    {
        if (! $value) {
            return '—';
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return $value;
            }

            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return $value;
        }
    }

    public function render()
    {
        $announcements = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $years = MarriageAnnouncement::forParish($this->parishId)
            ->selectRaw('YEAR(COALESCE(announcements_one, created_at)) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->filter();

        return view('livewire.marriage-announcement.marriage-announcement-list', [
            'announcements' => $announcements,
            'years'         => $years,
            'canManage'     => auth()->user()?->can('create', MarriageAnnouncement::class),
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
