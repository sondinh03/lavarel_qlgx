<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ScoreEditLog;
use App\Models\ScoreType;

class ScoreEditLogList extends BaseComponent
{
    protected $usePagination = true;

    public function mount(): void
    {
        $this->authorize('create', ScoreType::class);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        // Không cần preload — danh sách lấy trong render.
    }

    public function render()
    {
        $logs = ScoreEditLog::query()
            ->with([
                'user:id,name',
                'scoreType:id,name',
                'studentClass.student.saint',
                'studentClass.catechismClass:id,name',
            ])
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('scoreType', fn ($t) => $t->where('name', 'like', $term))
                        ->orWhereHas('studentClass.student', function ($s) use ($term) {
                            $s->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term);
                        });
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.score.score-edit-log-list', [
            'logs' => $logs,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
