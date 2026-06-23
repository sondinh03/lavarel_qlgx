<?php

namespace App\Http\Livewire\MarriageAnnouncement;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\MarriageAnnouncement;
use Carbon\Carbon;
use Override;

class MarriageAnnouncementShow extends BaseComponent
{
    public int $announcementId = 0;
    public ?MarriageAnnouncement $announcement = null;

    protected $usePagination = false;

    public function mount($id = null): void
    {
        $this->announcementId = (int) $id;
        parent::mount();
    }

    #[Override]
    protected function loadInitialData(): void
    {
        $this->announcement = MarriageAnnouncement::with([
            'assignedPriest',
            'parishioners.parishioner.saint',
            'slug',
        ])->findOrFail($this->announcementId);

        $this->authorize('view', $this->announcement);
    }

    public function formatDate(?string $value): ?string
    {
        if (! $value) {
            return null;
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
        return view('livewire.marriage-announcement.marriage-announcement-show', [
            'item'              => $this->announcement,
            'canManage'         => auth()->user()?->can('update', $this->announcement),
            'canCreateMarriage' => auth()->user()?->can('createMarriage', $this->announcement),
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
