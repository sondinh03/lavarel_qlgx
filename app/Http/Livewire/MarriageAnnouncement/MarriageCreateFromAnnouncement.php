<?php

namespace App\Http\Livewire\MarriageAnnouncement;

use App\Actions\MarriageAnnouncement\CreateMarriageFromAnnouncementAction;
use App\Actions\MarriageAnnouncement\SaveMarriageAnnouncementAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\Marriage;
use App\Models\MarriageAnnouncement;
use Override;

class MarriageCreateFromAnnouncement extends BaseComponent
{
    public int $announcementId = 0;
    public ?MarriageAnnouncement $announcement = null;

    public ?string $married_date         = null;
    public ?string $certificate_number   = null;
    public ?string $marriage_parish_name = null;
    public         $parish_id            = null;
    public ?string $place_province       = null;
    public         $place_ward_id        = null;
    public ?string $priest_witness       = null;
    public ?string $witness_1            = null;
    public ?string $witness_2            = null;
    public ?string $note                 = null;
    public string  $marriage_status      = 'valid';

    public bool $showSuccessModal = false;
    public ?int $createdFamilyId  = null;
    public ?int $createdMarriageId = null;
    public ?int $groomId           = null;
    public array $processWarnings  = [];

    protected $usePagination = false;

    protected function rules(): array
    {
        return [
            'married_date'       => 'nullable|date',
            'certificate_number' => 'nullable|string|max:50',
            'parish_id'          => 'nullable|integer|exists:parishes,id',
            'marriage_parish_name' => 'nullable|string|max:100',
            'place_province'     => 'nullable|string|max:100',
            'place_ward_id'      => 'nullable|integer',
            'priest_witness'     => 'nullable|string|max:100',
            'witness_1'          => 'nullable|string|max:100',
            'witness_2'          => 'nullable|string|max:100',
            'note'               => 'nullable|string|max:2000',
            'marriage_status'    => 'required|in:valid,invalid,widowed,divorced',
        ];
    }

    public function mount($id = null): void
    {
        $this->announcementId = (int) $id;
        parent::mount();
    }

    #[Override]
    protected function loadInitialData(): void
    {
        $this->announcement = MarriageAnnouncement::with(['parishioners.parishioner', 'assignedPriest'])
            ->findOrFail($this->announcementId);

        $this->authorize('createMarriage', $this->announcement);

        $this->parish_id        = $this->announcement->pid;
        $this->priest_witness   = SaveMarriageAnnouncementAction::priestName($this->announcement->priest);
        $this->marriage_status  = Marriage::STATUS_VALID;
    }

    public function save(): void
    {
        $this->authorize('createMarriage', $this->announcement);
        $this->validate();

        try {
            $result = app(CreateMarriageFromAnnouncementAction::class)->handle($this->announcement, [
                'married_date'       => $this->married_date,
                'certificate_number' => $this->certificate_number,
                'parish_id'          => $this->parish_id,
                'parish_name'        => $this->marriage_parish_name,
                'place_province'     => $this->place_province,
                'place_ward_id'      => $this->place_ward_id,
                'priest_witness'     => $this->priest_witness,
                'witness_1'          => $this->witness_1,
                'witness_2'          => $this->witness_2,
                'note'               => $this->note,
                'status'             => $this->marriage_status,
            ]);

            $marriage = $result->marriage;
            $this->createdMarriageId = $marriage->id;
            $this->groomId           = $marriage->husband_id;
            $this->createdFamilyId   = $result->family?->id;
            $this->processWarnings   = $result->warnings;
            $this->showSuccessModal  = true;
            $this->emit('toast', 'message', 'Đã tạo hôn phối chính thức.');
        } catch (\InvalidArgumentException $e) {
            $this->emit('toast', 'warning', $e->getMessage());
        } catch (\Exception $e) {
            $this->logError($e, 'Create marriage from announcement failed');
            $this->emit('toast', 'error', 'Có lỗi khi tạo hôn phối.');
        }
    }

    public function closeSuccessModal(): void
    {
        if ($this->groomId) {
            $this->redirect(route('parishioners.show', $this->groomId) . '?tab=marriage');
            return;
        }

        $this->showSuccessModal = false;
    }

    public function render()
    {
        return view('livewire.marriage-announcement.marriage-create-from-announcement', [
            'announcement' => $this->announcement,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
