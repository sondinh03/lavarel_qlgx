<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceEditLog;
use App\Models\AttendanceSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceEditLogList extends BaseComponent
{
    protected $usePagination = true;

    public ?string $viewingBatchId = null;

    public function mount(): void
    {
        $this->authorize('create', AttendanceSession::class);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        // Danh sách lấy trong render.
    }

    public function openBatchDetail(string $batchId): void
    {
        $this->viewingBatchId = $batchId;
    }

    public function closeBatchDetail(): void
    {
        $this->viewingBatchId = null;
    }

    public function updatedSearch(): void
    {
        $this->viewingBatchId = null;
        parent::updatedSearch();
    }

    public function render()
    {
        $batches = $this->buildBatchesQuery()
            ->paginate($this->perPage);

        $samples = $this->loadBatchSamples($batches->getCollection());

        $detailLogs = $this->viewingBatchId
            ? $this->loadBatchDetailLogs($this->viewingBatchId)
            : collect();

        return view('livewire.attendance.attendance-edit-log-list', [
            'batches'    => $batches,
            'samples'    => $samples,
            'detailLogs' => $detailLogs,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }

    protected function buildBatchesQuery()
    {
        return AttendanceEditLog::query()
            ->select([
                'batch_id',
                DB::raw('MAX(created_at) as batch_at'),
                DB::raw('MAX(user_id) as user_id'),
                DB::raw('COUNT(*) as changes_count'),
                DB::raw('COUNT(DISTINCT session_id) as session_count'),
                DB::raw('MIN(session_id) as sample_session_id'),
            ])
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('student', function ($s) use ($term) {
                            $s->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term);
                        })
                        ->orWhereHas('session.catechismClass', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->whereNotNull('batch_id')
            ->groupBy('batch_id')
            ->orderByDesc('batch_at');
    }

    /**
     * @param  Collection<int, object>  $batchRows
     * @return Collection<string, AttendanceEditLog>
     */
    protected function loadBatchSamples(Collection $batchRows): Collection
    {
        $batchIds = $batchRows->pluck('batch_id')->filter()->values();
        if ($batchIds->isEmpty()) {
            return collect();
        }

        $ids = AttendanceEditLog::query()
            ->select(DB::raw('MIN(id) as id'))
            ->whereIn('batch_id', $batchIds)
            ->groupBy('batch_id')
            ->pluck('id');

        return AttendanceEditLog::query()
            ->with([
                'user:id,name',
                'session.catechismClass:id,name',
            ])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('batch_id');
    }

    protected function loadBatchDetailLogs(string $batchId): Collection
    {
        $query = AttendanceEditLog::query()
            ->with([
                'user:id,name',
                'student.saint',
                'session.catechismClass:id,name',
            ])
            ->where('batch_id', $batchId)
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->orderBy('id');

        return $query->get();
    }
}
