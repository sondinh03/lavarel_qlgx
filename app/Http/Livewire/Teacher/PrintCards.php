<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishNew;
use App\Models\Teacher;
use App\Support\CardTheme;

/**
 * In thẻ giáo lý viên CR80.
 *
 * Query: ?ids=1,2,3
 */
class PrintCards extends BaseComponent
{
    public ?string $ids = null;

    public string $parishName = '';

    /** URL logo ban giáo lý — parishes.image, fallback public/images/logo-tntt.png */
    public ?string $parishLogoUrl = null;

    /** Màu chủ đạo: green | blue | yellow | red */
    public string $theme = CardTheme::DEFAULT;

    /** @var \Illuminate\Support\Collection */
    public $teachers;

    protected $usePagination = false;

    public function mount(): void
    {
        $this->teachers = collect();

        parent::mount();
        $this->theme = CardTheme::normalize($this->theme);
        $this->requireManager();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadTeachers();
        $this->resolveParishInfo();
    }

    protected function queryString(): array
    {
        return array_merge([
            'ids'   => ['except' => null],
            'theme' => ['except' => CardTheme::DEFAULT],
        ], parent::queryString());
    }

    public function updatedTheme(string $value): void
    {
        $this->theme = CardTheme::normalize($value);
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
                'phone_number',
                'saint_id',
                'parish_id',
                'parish_group_id',
                'avatar_path',
                'qr_token',
            ]);
    }

    protected function resolveParishInfo(): void
    {
        $this->parishName = '';
        $this->parishLogoUrl = asset('images/logo-tntt.png');

        if (!$this->parishId) {
            return;
        }

        $parish = ParishNew::query()->whereKey($this->parishId)->first(['id', 'name', 'image']);
        if (!$parish) {
            return;
        }

        $this->parishName = (string) ($parish->name ?? '');
        $logoPath = trim((string) ($parish->image ?? ''));
        if ($logoPath !== '') {
            $this->parishLogoUrl = media_url($logoPath);
        }
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
            'teachers'      => $this->teachers,
            'parishName'    => $this->parishName,
            'parishLogoUrl' => $this->parishLogoUrl,
            'theme'         => $this->theme,
            'themes'        => CardTheme::all(),
            'colors'        => CardTheme::resolve($this->theme),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
