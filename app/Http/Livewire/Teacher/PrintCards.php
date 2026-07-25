<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishNew;
use App\Models\Teacher;

/**
 * In thẻ giáo lý viên CR80.
 *
 * Query: ?ids=1,2,3
 */
class PrintCards extends BaseComponent
{
    public ?string $ids = null;

    public string $parishName = '';

    /** @var \Illuminate\Support\Collection */
    public $teachers;

    protected $usePagination = false;

    public function mount(): void
    {
        $this->teachers = collect();

        parent::mount();
        $this->requireManager();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadTeachers();
        $this->resolveParishName();
    }

    protected function queryString(): array
    {
        return array_merge([
            'ids' => ['except' => null],
        ], parent::queryString());
    }

    protected function loadTeachers(): void
    {
        if (! $this->ids) {
            $this->teachers = collect();
            session()->flash('error', 'Không có giáo lý viên nào để in');

            return;
        }

        $idList = collect(explode(',', $this->ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($idList === []) {
            $this->teachers = collect();

            return;
        }

        $this->teachers = Teacher::query()
            ->with(['saint', 'parishGroup'])
            ->where('parish_id', $this->parishId)
            ->whereIn('id', $idList)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'last_name',
                'teacher_code',
                'birthday',
                'gender',
                'saint_id',
                'parish_id',
                'parish_group_id',
                'avatar_path',
                'qr_token',
            ]);
    }

    protected function resolveParishName(): void
    {
        $this->parishName = $this->parishId
            ? (ParishNew::query()->whereKey($this->parishId)->value('name') ?? '')
            : '';
    }

    public function printCards(): void
    {
        if ($this->teachers->isEmpty()) {
            session()->flash('warning', 'Không có giáo lý viên nào để in!');

            return;
        }

        $this->dispatchBrowserEvent('trigger-print');
    }

    public function render()
    {
        return view('livewire.teacher.print-cards', [
            'teachers'   => $this->teachers,
            'parishName' => $this->parishName,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
