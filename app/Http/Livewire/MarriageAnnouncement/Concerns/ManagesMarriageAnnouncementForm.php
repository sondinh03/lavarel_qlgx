<?php

namespace App\Http\Livewire\MarriageAnnouncement\Concerns;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\MarriageAnnouncement;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\Priest;
use Carbon\Carbon;

trait ManagesMarriageAnnouncementForm
{
    use ManagesMarriageAnnouncementLookups;

    public string $activeTab = 'general';

    public string  $name = '';
    public         $priest_id = null;
    public         $did = null;
    public         $deid = null;
    public         $pid = null;
    public int     $status = 0;

    public ?string $announcements_one   = null;
    public ?string $announcements_two   = null;
    public ?string $announcements_three = null;
    public bool    $announcements_one_done   = false;
    public bool    $announcements_two_done   = false;
    public bool    $announcements_three_done = false;

    public         $groom_parishioner_id = null;
    public string  $groom_parishioner_mode = 'pick';
    public string  $groom_manual_name = '';
    public bool    $groom_has_impediment = false;
    public ?string $groom_old_diocese = '';
    public ?string $groom_old_deanery = '';
    public ?string $groom_old_parish_management = '';
    public ?string $groom_old_parish = '';
    public ?string $groom_diocese = '';
    public ?string $groom_deanery = '';
    public ?string $groom_parish_management = '';
    public ?string $groom_parish = '';
    public ?string $groom_before_diocese = '';
    public ?string $groom_before_deanery = '';
    public ?string $groom_before_parish_management = '';
    public ?string $groom_before_parish = '';

    public         $bride_parishioner_id = null;
    public string  $bride_parishioner_mode = 'pick';
    public string  $bride_manual_name = '';
    public bool    $bride_has_impediment = false;
    public ?string $bride_old_diocese = '';
    public ?string $bride_old_deanery = '';
    public ?string $bride_old_parish_management = '';
    public ?string $bride_old_parish = '';
    public ?string $bride_diocese = '';
    public ?string $bride_deanery = '';
    public ?string $bride_parish_management = '';
    public ?string $bride_parish = '';
    public ?string $bride_before_diocese = '';
    public ?string $bride_before_deanery = '';
    public ?string $bride_before_parish_management = '';
    public ?string $bride_before_parish = '';

    public array $priests = [];
    public array $dioceses = [];
    public array $deaneryOptions = [];
    public array $parishOptions = [];

    protected function announcementFormRules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'priest_id'           => 'nullable|integer|exists:sacrament_givers,id',
            'did'                 => 'nullable|integer|exists:dioceses,id',
            'deid'                => 'nullable|integer|exists:deanerys,id',
            'pid'                 => 'required|integer|exists:parishes,id',
            'status'              => 'required|integer|in:0,1,2,3',
            'announcements_one'   => 'required|date',
            'announcements_two'   => 'nullable|date|after:announcements_one',
            'announcements_three' => 'nullable|date|after:announcements_two',
            'groom_parishioner_mode' => 'required|in:pick,manual',
            'groom_parishioner_id'   => 'required_if:groom_parishioner_mode,pick|nullable|integer|exists:parishioners_new,id',
            'groom_manual_name'      => 'required_if:groom_parishioner_mode,manual|nullable|string|max:255',
            'bride_parishioner_mode' => 'required|in:pick,manual',
            'bride_parishioner_id'   => 'required_if:bride_parishioner_mode,pick|nullable|integer|exists:parishioners_new,id',
            'bride_manual_name'      => 'required_if:bride_parishioner_mode,manual|nullable|string|max:255',
        ];
    }

    protected function announcementFormMessages(): array
    {
        return [
            'name.required'                 => 'Vui lòng nhập tên đôi hôn phối.',
            'pid.required'                  => 'Vui lòng chọn giáo xứ.',
            'announcements_one.required'    => 'Vui lòng nhập ngày rao lần 1.',
            'groom_parishioner_id.required_if' => 'Vui lòng chọn bên nam.',
            'groom_manual_name.required_if'    => 'Vui lòng nhập tên bên nam.',
            'bride_parishioner_id.required_if' => 'Vui lòng chọn bên nữ.',
            'bride_manual_name.required_if'    => 'Vui lòng nhập tên bên nữ.',
        ];
    }

    protected function validateAnnouncementDates(): void
    {
        $minGap = (int) config('marriage-announcement.min_days_between_announcements', 7);

        if ($this->announcements_one && $this->announcements_two) {
            $one = Carbon::parse($this->announcements_one);
            $two = Carbon::parse($this->announcements_two);
            if ($two->lt($one->copy()->addDays($minGap))) {
                $this->addError('announcements_two', "Lần 2 phải cách lần 1 ít nhất {$minGap} ngày.");
            }
        }

        if ($this->announcements_two && $this->announcements_three) {
            $two = Carbon::parse($this->announcements_two);
            $three = Carbon::parse($this->announcements_three);
            if ($three->lt($two->copy()->addDays($minGap))) {
                $this->addError('announcements_three', "Lần 3 phải cách lần 2 ít nhất {$minGap} ngày.");
            }
        }

        if ($this->announcements_one_done && ! $this->announcements_one) {
            $this->addError('announcements_one_done', 'Cần nhập ngày rao lần 1 trước khi đánh dấu đã rao.');
        }

        if ($this->announcements_two_done) {
            if (! $this->announcements_two) {
                $this->addError('announcements_two_done', 'Cần nhập ngày rao lần 2 trước khi đánh dấu đã rao.');
            } elseif (! $this->announcements_one_done) {
                $this->addError('announcements_two_done', 'Cần đánh dấu đã rao lần 1 trước.');
            }
        }

        if ($this->announcements_three_done) {
            if (! $this->announcements_three) {
                $this->addError('announcements_three_done', 'Cần nhập ngày rao lần 3 trước khi đánh dấu đã rao.');
            } elseif (! $this->announcements_two_done) {
                $this->addError('announcements_three_done', 'Cần đánh dấu đã rao lần 2 trước.');
            }
        }
    }

    public function updatedAnnouncementsOneDone(bool $value): void
    {
        if (! $value) {
            $this->announcements_two_done   = false;
            $this->announcements_three_done = false;
        }
    }

    public function updatedAnnouncementsTwoDone(bool $value): void
    {
        if (! $value) {
            $this->announcements_three_done = false;
        }
    }

    public function updatedAnnouncementsOne(): void
    {
        if (! $this->announcements_one) {
            $this->announcements_one_done = false;
        }
    }

    public function updatedAnnouncementsTwo(): void
    {
        if (! $this->announcements_two) {
            $this->announcements_two_done = false;
        }
    }

    public function updatedAnnouncementsThree(): void
    {
        if (! $this->announcements_three) {
            $this->announcements_three_done = false;
        }
    }

    public function getSuggestedDateTwoProperty(): ?string
    {
        if (! $this->announcements_one) {
            return null;
        }

        return Carbon::parse($this->announcements_one)
            ->addDays((int) config('marriage-announcement.min_days_between_announcements', 7))
            ->format('Y-m-d');
    }

    public function getSuggestedDateThreeProperty(): ?string
    {
        $base = $this->announcements_two ?: $this->announcements_one;
        if (! $base) {
            return null;
        }

        $days = $this->announcements_two
            ? (int) config('marriage-announcement.min_days_between_announcements', 7)
            : (int) config('marriage-announcement.min_days_between_announcements', 7) * 2;

        return Carbon::parse($base)->addDays($days)->format('Y-m-d');
    }

    protected function loadAnnouncementDropdowns(): void
    {
        $this->dioceses = Diocese::where('status', 1)->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($d) => ['id' => (string) $d->id, 'name' => $d->name])
            ->values()->toArray();

        $this->priests = Priest::orderBy('name')->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->values()->toArray();

        $this->syncDeaneryOptions();
        $this->syncParishOptions();
        $this->loadParishionerPickerOptions();
        $this->syncAllParticipantDropdownOptions();
    }

    protected function syncDeaneryOptions(): void
    {
        if (! $this->did) {
            $this->deaneryOptions = [];
            return;
        }

        $this->deaneryOptions = Deanery::where('did', $this->did)->where('status', 1)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn ($d) => ['id' => (string) $d->id, 'name' => $d->name])
            ->values()->toArray();
    }

    protected function syncParishOptions(): void
    {
        if (! $this->deid) {
            $this->parishOptions = [];
            return;
        }

        $this->parishOptions = ParishNew::where('deanery_id', $this->deid)->where('status', 1)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->values()->toArray();
    }

    public function updatedDid($value): void
    {
        $this->deid = null;
        $this->pid  = null;
        $this->syncDeaneryOptions();
        $this->parishOptions = [];
    }

    public function updatedDeid($value): void
    {
        $this->pid = null;
        $this->syncParishOptions();
    }

    public function updatedPid(): void
    {
        $this->loadParishionerPickerOptions();
    }

    public function updatedGroomParishionerMode(string $value): void
    {
        if ($value === 'manual') {
            $this->groom_parishioner_id = null;
        } else {
            $this->groom_manual_name = '';
        }
    }

    public function updatedBrideParishionerMode(string $value): void
    {
        if ($value === 'manual') {
            $this->bride_parishioner_id = null;
        } else {
            $this->bride_manual_name = '';
        }
    }

    public function updatedGroomParishionerId($value): void
    {
        if ($this->groom_parishioner_mode !== 'pick') {
            return;
        }

        $this->groom_parishioner_id = $value ? (int) $value : null;
        $this->prefillParticipantFromParishioner('groom', $this->groom_parishioner_id);
        $this->syncCoupleName();
    }

    public function updatedBrideParishionerId($value): void
    {
        if ($this->bride_parishioner_mode !== 'pick') {
            return;
        }

        $this->bride_parishioner_id = $value ? (int) $value : null;
        $this->prefillParticipantFromParishioner('bride', $this->bride_parishioner_id);
        $this->syncCoupleName();
    }

    public function updatedGroomManualName(): void
    {
        $this->syncCoupleName();
    }

    public function updatedBrideManualName(): void
    {
        $this->syncCoupleName();
    }

    protected function syncCoupleName(): void
    {
        if (trim($this->name) !== '') {
            return;
        }

        $groomName = $this->participantDisplayName('groom');
        $brideName = $this->participantDisplayName('bride');

        if ($groomName && $brideName) {
            $this->name = $groomName . ' & ' . $brideName;
        }
    }

    protected function participantDisplayName(string $role): ?string
    {
        if ($this->{$role . '_parishioner_mode'} === 'manual') {
            $name = trim($this->{$role . '_manual_name'});

            return $name !== '' ? $name : null;
        }

        $id = $this->{$role . '_parishioner_id'};
        if (! $id) {
            return null;
        }

        return Parishioner::find($id)?->full_name_with_saint;
    }

    protected function prefillParticipantFromParishioner(string $role, $parishionerId): void
    {
        if (! $parishionerId) {
            return;
        }

        $p = Parishioner::with(['diocese', 'deanery', 'parish', 'parishGroup'])->find($parishionerId);
        if (! $p) {
            return;
        }

        $this->setParticipantIdsFromParishioner($role, 'current', $p);
    }

    protected function mapAnnouncementToForm(MarriageAnnouncement $announcement): void
    {
        $announcement->load(['parishioners.parishioner.saint']);

        $this->name                = $announcement->name;
        $this->priest_id           = $announcement->priest;
        $this->did                 = $announcement->did;
        $this->deid                = $announcement->deid;
        $this->pid                 = $announcement->pid;
        $this->status              = (int) $announcement->status;
        $this->announcements_one   = $this->normalizeDateForInput($announcement->announcements_one);
        $this->announcements_two   = $this->normalizeDateForInput($announcement->announcements_two);
        $this->announcements_three = $this->normalizeDateForInput($announcement->announcements_three);
        $this->announcements_one_done   = (bool) $announcement->announcements_one_done;
        $this->announcements_two_done   = (bool) $announcement->announcements_two_done;
        $this->announcements_three_done = (bool) $announcement->announcements_three_done;

        $groom = $announcement->groomParticipant();
        $bride = $announcement->brideParticipant();

        if ($groom) {
            $this->mapParticipantToForm('groom', $groom);
        }
        if ($bride) {
            $this->mapParticipantToForm('bride', $bride);
        }

        $this->resolveAllParticipantIdsFromNames();
        $this->loadAnnouncementDropdowns();
        $this->syncAllParticipantDropdownOptions();
    }

    protected function mapParticipantToForm(string $role, MarriageAnnouncementParishioners $row): void
    {
        if ($row->isManualEntry()) {
            $this->{$role . '_parishioner_mode'} = 'manual';
            $this->{$role . '_parishioner_id'}   = null;
            $this->{$role . '_manual_name'}      = $row->manual_name ?? '';
        } else {
            $this->{$role . '_parishioner_mode'} = 'pick';
            $this->{$role . '_parishioner_id'}   = $row->idgiaodan ?: null;
            $this->{$role . '_manual_name'}      = '';
        }

        $this->{$role . '_has_impediment'}   = $row->hasImpediment();
        $this->{$role . '_old_diocese'}      = $row->diocesesold;
        $this->{$role . '_old_deanery'}      = $row->deanerysold;
        $this->{$role . '_old_parish_management'} = $row->parishmanagementsold;
        $this->{$role . '_old_parish'}       = $row->parishsold;
        $this->{$role . '_diocese'}          = $row->dioceses;
        $this->{$role . '_deanery'}          = $row->deanerys;
        $this->{$role . '_parish_management'} = $row->parishmanagements;
        $this->{$role . '_parish'}           = $row->parishs;
        $this->{$role . '_before_diocese'}   = $row->diocesesbefore;
        $this->{$role . '_before_deanery'}   = $row->deanerysbefore;
        $this->{$role . '_before_parish_management'} = $row->parishmanagementsbefore;
        $this->{$role . '_before_parish'}    = $row->parishsbefore;

        $this->resolveParticipantIdsFromNames($role, 'old');
        $this->resolveParticipantIdsFromNames($role, 'current');
        $this->resolveParticipantIdsFromNames($role, 'before');
        $this->syncParticipantDropdownOptions($role, 'old');
        $this->syncParticipantDropdownOptions($role, 'current');
        $this->syncParticipantDropdownOptions($role, 'before');
    }

    protected function normalizeDateForInput(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function buildAnnouncementPayload(): array
    {
        return [
            'header' => [
                'name'                => $this->name,
                'priest'              => $this->priest_id ? (int) $this->priest_id : null,
                'did'                 => $this->did ? (int) $this->did : null,
                'deid'                => $this->deid ? (int) $this->deid : null,
                'pid'                 => (int) $this->pid,
                'status'              => $this->status,
                'announcements_one'   => $this->announcements_one,
                'announcements_two'   => $this->announcements_two,
                'announcements_three' => $this->announcements_three,
                'announcements_one_done'   => $this->announcements_one_done,
                'announcements_two_done'   => $this->announcements_two_done,
                'announcements_three_done' => $this->announcements_three_done,
            ],
            'groom' => $this->participantPayload('groom'),
            'bride' => $this->participantPayload('bride'),
        ];
    }

    protected function participantPayload(string $role): array
    {
        $isManual = $this->{$role . '_parishioner_mode'} === 'manual';

        return [
            'parishioner_id'          => $isManual ? 0 : (int) $this->{$role . '_parishioner_id'},
            'manual_name'             => $isManual ? trim($this->{$role . '_manual_name'}) : null,
            'has_impediment'          => (bool) $this->{$role . '_has_impediment'},
            'old_diocese'             => $this->{$role . '_old_diocese'},
            'old_deanery'             => $this->{$role . '_old_deanery'},
            'old_parish_management'   => $this->{$role . '_old_parish_management'},
            'old_parish'              => $this->{$role . '_old_parish'},
            'diocese'                 => $this->{$role . '_diocese'},
            'deanery'                 => $this->{$role . '_deanery'},
            'parish_management'       => $this->{$role . '_parish_management'},
            'parish'                  => $this->{$role . '_parish'},
            'before_diocese'          => $this->{$role . '_before_diocese'},
            'before_deanery'          => $this->{$role . '_before_deanery'},
            'before_parish_management' => $this->{$role . '_before_parish_management'},
            'before_parish'           => $this->{$role . '_before_parish'},
        ];
    }

    public function switchFormTab(string $tab): void
    {
        if (in_array($tab, ['general', 'schedule', 'groom', 'bride'], true)) {
            $this->activeTab = $tab;
        }
    }
}
