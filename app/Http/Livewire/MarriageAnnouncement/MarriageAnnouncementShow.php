<?php

namespace App\Http\Livewire\MarriageAnnouncement;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\MarriageAnnouncement;
use App\Presenters\MarriageAnnouncementGioiThieuHonPhoiPresenter;
use Carbon\Carbon;
use Override;

class MarriageAnnouncementShow extends BaseComponent
{
    public int $announcementId = 0;
    public ?MarriageAnnouncement $announcement = null;

    public bool $showGioiThieuHonPhoiModal = false;
    public string $subject_side = 'groom';
    public string $greeting_parish = '';

    public string $a_honorific = 'Anh';
    public string $a_holy_name = '';
    public ?string $a_birthday = null;
    public string $a_birth_place = '';
    public string $a_father_name = '';
    public string $a_mother_name = '';
    public string $a_address = '';
    public string $a_parish_group = '';
    public string $a_parish = '';

    public string $b_honorific = 'Chị';
    public string $b_holy_name = '';
    public ?string $b_birthday = null;
    public string $b_birth_place = '';
    public string $b_father_name = '';
    public string $b_mother_name = '';
    public string $b_address = '';
    public string $b_parish_group = '';
    public string $b_parish = '';

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
            'parishioners.parishioner.parish',
            'parishioners.parishioner.parishGroup',
            'parishioners.parishioner.father.saint',
            'parishioners.parishioner.mother.saint',
            'slug',
        ])->findOrFail($this->announcementId);

        $this->authorize('view', $this->announcement);
    }

    public function openGioiThieuHonPhoiModal(): void
    {
        $this->authorize('update', $this->announcement);

        $groom = $this->announcement->groomParticipant();
        $bride = $this->announcement->brideParticipant();

        if (! $groom?->displayName() || ! $bride?->displayName()) {
            $this->emit('toast', 'error', 'Hồ sơ cần đủ bên nam và bên nữ trước khi xuất giấy giới thiệu.');

            return;
        }

        $this->subject_side = $this->defaultSubjectSide();
        $this->greeting_parish = '';
        $this->fillSidesFromSubject();
        $this->resetErrorBag();
        $this->showGioiThieuHonPhoiModal = true;
    }

    public function updatedSubjectSide(): void
    {
        $this->fillSidesFromSubject();
    }

    public function exportGioiThieuHonPhoi()
    {
        $this->authorize('update', $this->announcement);

        $this->validate([
            'subject_side'    => 'required|in:groom,bride',
            'greeting_parish' => 'nullable|string|max:255',
            'a_holy_name'     => 'required|string|max:200',
            'a_birthday'      => 'required|date',
            'a_honorific'     => 'nullable|string|max:20',
            'a_birth_place'   => 'nullable|string|max:255',
            'a_father_name'   => 'nullable|string|max:200',
            'a_mother_name'   => 'nullable|string|max:200',
            'a_address'       => 'nullable|string|max:255',
            'a_parish_group'  => 'nullable|string|max:255',
            'a_parish'        => 'nullable|string|max:255',
            'b_holy_name'     => 'required|string|max:200',
            'b_birthday'      => 'required|date',
            'b_honorific'     => 'nullable|string|max:20',
            'b_birth_place'   => 'nullable|string|max:255',
            'b_father_name'   => 'nullable|string|max:200',
            'b_mother_name'   => 'nullable|string|max:200',
            'b_address'       => 'nullable|string|max:255',
            'b_parish_group'  => 'nullable|string|max:255',
            'b_parish'        => 'nullable|string|max:255',
        ], [
            'a_holy_name.required' => 'Vui lòng nhập họ tên đương sự',
            'a_birthday.required'  => 'Vui lòng nhập ngày sinh đương sự',
            'b_holy_name.required' => 'Vui lòng nhập họ tên người kết bạn',
            'b_birthday.required'  => 'Vui lòng nhập ngày sinh người kết bạn',
        ]);

        $url = route('marriage-announcements.export-gioi-thieu-hon-phoi', [
            'id'              => $this->announcement->id,
            'subject_side'    => $this->subject_side,
            'greeting_parish' => $this->greeting_parish,
            'a_honorific'     => $this->a_honorific,
            'a_holy_name'     => $this->a_holy_name,
            'a_birthday'      => $this->a_birthday,
            'a_birth_place'   => $this->a_birth_place,
            'a_father_name'   => $this->a_father_name,
            'a_mother_name'   => $this->a_mother_name,
            'a_address'       => $this->a_address,
            'a_parish_group'  => $this->a_parish_group,
            'a_parish'        => $this->a_parish,
            'b_honorific'     => $this->b_honorific,
            'b_holy_name'     => $this->b_holy_name,
            'b_birthday'      => $this->b_birthday,
            'b_birth_place'   => $this->b_birth_place,
            'b_father_name'   => $this->b_father_name,
            'b_mother_name'   => $this->b_mother_name,
            'b_address'       => $this->b_address,
            'b_parish_group'  => $this->b_parish_group,
            'b_parish'        => $this->b_parish,
        ]);

        $this->showGioiThieuHonPhoiModal = false;

        return redirect()->to($url);
    }

    private function defaultSubjectSide(): string
    {
        $parishId = (int) $this->announcement->pid;
        $groom = $this->announcement->groomParticipant();
        $bride = $this->announcement->brideParticipant();

        if ($groom?->parishioner && (int) $groom->parishioner->parish_id === $parishId) {
            return 'groom';
        }
        if ($bride?->parishioner && (int) $bride->parishioner->parish_id === $parishId) {
            return 'bride';
        }

        return 'groom';
    }

    private function fillSidesFromSubject(): void
    {
        $groomData = MarriageAnnouncementGioiThieuHonPhoiPresenter::personFromParticipant(
            $this->announcement->groomParticipant(),
            'Anh'
        );
        $brideData = MarriageAnnouncementGioiThieuHonPhoiPresenter::personFromParticipant(
            $this->announcement->brideParticipant(),
            'Chị'
        );

        $a = $this->subject_side === 'bride' ? $brideData : $groomData;
        $b = $this->subject_side === 'bride' ? $groomData : $brideData;

        $this->applySideToForm('a', $a);
        $this->applySideToForm('b', $b);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applySideToForm(string $prefix, array $data): void
    {
        $this->{$prefix . '_honorific'} = (string) ($data['honorific'] ?? '');
        $this->{$prefix . '_holy_name'} = (string) ($data['holy_name'] ?? '');
        $birthday = $data['birthday'] ?? null;
        $this->{$prefix . '_birthday'} = $birthday instanceof Carbon
            ? $birthday->format('Y-m-d')
            : ($birthday ? Carbon::parse($birthday)->format('Y-m-d') : null);
        $this->{$prefix . '_birth_place'} = (string) ($data['birth_place'] ?? '');
        $this->{$prefix . '_father_name'} = (string) ($data['father_name'] ?? '');
        $this->{$prefix . '_mother_name'} = (string) ($data['mother_name'] ?? '');
        $this->{$prefix . '_address'} = (string) ($data['address'] ?? '');
        $this->{$prefix . '_parish_group'} = (string) ($data['parish_group'] ?? '');
        $this->{$prefix . '_parish'} = (string) ($data['parish'] ?? '');
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
