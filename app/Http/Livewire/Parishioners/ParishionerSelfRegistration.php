<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Parishioners\Concerns\ManagesFamilyRegisterSubmission;
use App\Models\Marriage;
use App\Models\ParishionerRegistrationRequest;
use App\Models\ParishNew;
use App\Models\Sacrament;
use App\Models\User;
use App\Notifications\ParishionerRegistrationSubmitted;
use App\Support\FamilyCodeGenerator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ParishionerSelfRegistration extends Component
{
    use ManagesFamilyRegisterSubmission;

    public ?int $targetParishId = null;

    public string $parishName = '';

    public string $parishDisplayLabel = '';

    public bool $submitted = false;

    public ?string $referenceCode = null;

    public string $activeStep = 'household';

    public array $parishOptions = [];

    public function mount(?int $parish = null): void
    {
        $activeParishes = ParishNew::query()
            ->where('status', 1)
            ->with('diocese:id,name')
            ->orderBy('name')
            ->get();

        $this->parishOptions = $activeParishes
            ->map(fn ($row) => [
                'id' => (string) $row->id,
                'name' => $this->formatParishOptionLabel($row),
            ])
            ->values()
            ->toArray();

        if ($parish && $activeParishes->contains('id', $parish)) {
            $this->targetParishId = $parish;
        } elseif ($activeParishes->count() === 1) {
            $this->targetParishId = $activeParishes->first()->id;
        }

        $this->syncParishContext();
        $this->family_code = FamilyCodeGenerator::generate();
        $this->seedDefaultMember();
    }

    public function updatedTargetParishId(): void
    {
        $this->family_parish_area_id = null;
        $this->syncParishContext();
    }

    protected function syncParishContext(): void
    {
        if (! $this->targetParishId) {
            $this->parishName = '';
            $this->parishDisplayLabel = '';
            $this->parishGroups = [];
            $this->associationOptions = [];
            $this->saints = \App\Models\Holymanagement::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();

            return;
        }

        $parish = ParishNew::with('diocese:id,name')->find($this->targetParishId);
        $this->parishName = $parish?->name ?? '';
        $this->parishDisplayLabel = $parish ? $this->formatParishOptionLabel($parish) : '';
        $this->loadFamilyRegisterDropdowns($this->targetParishId);
    }

    protected function formatParishOptionLabel(ParishNew $parish): string
    {
        $dioceseName = $parish->diocese?->name;

        return $dioceseName
            ? $parish->name . ' — ' . $dioceseName
            : $parish->name;
    }

    public function goToStep(string $step): void
    {
        $valid = ['household', 'members', 'marriages', 'contact'];
        if (in_array($step, $valid, true)) {
            $this->activeStep = $step;
            $this->ensureFamilyRegisterDropdowns();
        }
    }

    protected function ensureFamilyRegisterDropdowns(): void
    {
        if ($this->targetParishId) {
            $this->loadFamilyRegisterDropdowns($this->targetParishId);
        }
    }

    protected function stepOrder(): array
    {
        return ['household', 'members', 'marriages', 'contact'];
    }

    public function nextStep(): void
    {
        $order = $this->stepOrder();
        $index = array_search($this->activeStep, $order, true);
        if ($index !== false && isset($order[$index + 1])) {
            $this->activeStep = $order[$index + 1];
            $this->ensureFamilyRegisterDropdowns();
        }
    }

    public function prevStep(): void
    {
        $order = $this->stepOrder();
        $index = array_search($this->activeStep, $order, true);
        if ($index !== false && $index > 0) {
            $this->activeStep = $order[$index - 1];
        }
    }

    public function submit(): void
    {
        $key = 'parishioner-registration:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $message = 'Bạn đã gửi quá nhiều lần. Vui lòng thử lại sau ' . $seconds . ' giây.';
            $this->addError('submit', $message);
            $this->emit('toast', 'error', $message);

            return;
        }

        try {
            $this->validate($this->familyRegisterSubmitRules(), $this->familyRegisterSubmitMessages());
        } catch (ValidationException $e) {
            $this->emit('toast', 'error', 'Không thể gửi đăng ký: ' . $e->validator->errors()->first());
            throw $e;
        }

        $submitter = collect($this->members)->firstWhere('ref', $this->submitter_ref);
        if (! $submitter) {
            $message = 'Người đăng ký không hợp lệ';
            $this->addError('submitter_ref', $message);
            $this->emit('toast', 'error', $message);

            return;
        }

        $payload = $this->buildFamilyRegisterPayload();

        try {
            $request = ParishionerRegistrationRequest::create([
                'reference_code'  => $this->family_code,
                'parish_id'       => $this->targetParishId,
                'status'          => ParishionerRegistrationRequest::STATUS_PENDING,
                'submitted_name'  => trim(($submitter['last_name'] ?? '') . ' ' . ($submitter['first_name'] ?? '')),
                'submitted_phone' => $this->contact_phone,
                'payload'         => $payload,
                'sacraments'      => $this->familySacraments ?: null,
                'marriages'       => $this->familyMarriages ?: null,
                'ip_address'      => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $message = 'Có lỗi khi gửi đăng ký. Vui lòng thử lại sau.';
            $this->addError('submit', $message);
            $this->emit('toast', 'error', $message);

            return;
        }

        RateLimiter::hit($key, 3600);

        $recipients = User::query()
            ->where('parish_id', $request->parish_id)
            ->role(['parish_admin', 'parishioner_admin'])
            ->get();
        notify_users($recipients, new ParishionerRegistrationSubmitted($request));

        $this->submitted = true;
        $this->referenceCode = $request->reference_code;

        $this->emit(
            'toast',
            'message',
            'Đã gửi yêu cầu đăng ký thành công. Mã theo dõi: <strong>' . e($request->reference_code) . '</strong>'
        );
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-self-registration', [
            'familyRoles'    => config('parishioner-registration.family_roles', []),
            'marriageStatuses' => Marriage::statusOptions(),
            'sacramentTypes' => Sacrament::typeOptions(),
            'stepLabels'     => [
                'household'  => 'Hộ GĐ',
                'members'    => 'Thành viên',
                'marriages'  => 'Hôn phối',
                'contact'    => 'Gửi',
            ],
        ])->extends('frontend.layout.landing')->section('content');
    }
}
