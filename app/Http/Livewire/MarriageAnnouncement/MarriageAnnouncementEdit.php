<?php

namespace App\Http\Livewire\MarriageAnnouncement;

use App\Actions\MarriageAnnouncement\SaveMarriageAnnouncementAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Http\Livewire\MarriageAnnouncement\Concerns\ManagesMarriageAnnouncementForm;
use App\Models\MarriageAnnouncement;
use App\Models\ParishNew;
use Override;

class MarriageAnnouncementEdit extends BaseComponent
{
    use ManagesMarriageAnnouncementForm;

    public ?int $announcementId = null;
    public bool $isEdit = false;

    protected $usePagination = false;

    public function mount(?int $id = null): void
    {
        $this->announcementId = $id;
        $this->isEdit         = $id !== null;

        if ($this->isEdit) {
            $announcement = MarriageAnnouncement::findOrFail($id);
            $this->authorize('update', $announcement);
        } else {
            $this->authorize('create', MarriageAnnouncement::class);
        }

        parent::mount();
        $this->requireParishId();
    }

    #[Override]
    protected function loadInitialData(): void
    {
        if ($this->isEdit) {
            $this->mapAnnouncementToForm(MarriageAnnouncement::findOrFail($this->announcementId));
        } else {
            $parish = ParishNew::find($this->parishId);
            if ($parish) {
                $this->did  = $parish->diocese_id;
                $this->deid = $parish->deanery_id;
                $this->pid  = $parish->id;
            }
            $this->loadAnnouncementDropdowns();
        }
    }

    public function save(): void
    {
        if ($this->isEdit) {
            $this->authorize('update', MarriageAnnouncement::findOrFail($this->announcementId));
        } else {
            $this->authorize('create', MarriageAnnouncement::class);
        }

        $this->validate($this->announcementFormRules(), $this->announcementFormMessages());
        $this->validateAnnouncementDates();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            $payload = $this->buildAnnouncementPayload();
            $announcement = app(SaveMarriageAnnouncementAction::class)->handle(
                $this->announcementId,
                $payload['header'],
                $payload['groom'],
                $payload['bride']
            );

            $this->emit('toast', 'message', $this->isEdit ? 'Đã cập nhật hồ sơ rao.' : 'Đã tạo hồ sơ rao hôn phối.');
            $this->redirect(route('marriage-announcements.show', $announcement->id));
        } catch (\Exception $e) {
            $this->logError($e, 'Save marriage announcement failed');
            $this->emit('toast', 'error', 'Có lỗi khi lưu hồ sơ.');
        }
    }

    public function cancel(): void
    {
        $this->isEdit && $this->announcementId
            ? $this->redirect(route('marriage-announcements.show', $this->announcementId))
            : $this->redirect(route('marriage-announcements.index'));
    }

    public function render()
    {
        return view('livewire.marriage-announcement.marriage-announcement-form', [
            'isEdit' => $this->isEdit,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
