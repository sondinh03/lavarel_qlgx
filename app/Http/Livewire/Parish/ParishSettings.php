<?php

namespace App\Http\Livewire\Parish;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\ParishNew;
use App\Support\VietnamAddressResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ParishSettings extends Component
{
    public string $name = '';

    public string $code = '';

    public string $parish_priest_name = '';

    public string $phone = '';

    public $dioceseId = null;

    public $deaneryId = null;

    public ?string $province = null;

    public ?string $ward = null;

    public array $dioceseOptions = [];

    public array $deaneryOptions = [];

    public array $provinceOptions = [];

    public array $wardOptions = [];

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless($user && $user->isParishAdmin(), 403);
        abort_unless($user->parish_id, 403, 'Tài khoản chưa gắn giáo xứ.');

        $parish = ParishNew::query()->findOrFail($user->parish_id);

        $this->name = (string) ($parish->name ?? '');
        $this->code = (string) ($parish->code ?? '');
        $this->parish_priest_name = (string) ($parish->parish_priest_name ?? '');
        $this->phone = (string) ($parish->phone ?? '');
        $this->dioceseId = $parish->diocese_id ? (int) $parish->diocese_id : null;
        $this->deaneryId = $parish->deanery_id ? (int) $parish->deanery_id : null;
        $this->province = $parish->province ? (string) $parish->province : null;
        $this->ward = $parish->ward !== null && $parish->ward !== ''
            ? (string) $parish->ward
            : null;

        $this->dioceseOptions = Diocese::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $row->name,
            ])
            ->values()
            ->toArray();

        $this->provinceOptions = VietnamAddressResolver::provincesForSelect();
        $this->loadDeaneryOptions();
        $this->syncWardOptions();
    }

    public function updatedDioceseId(): void
    {
        $this->deaneryId = null;
        $this->loadDeaneryOptions();
    }

    public function updatedProvince(): void
    {
        $this->ward = null;
        $this->syncWardOptions();
    }

    protected function loadDeaneryOptions(): void
    {
        if (! $this->dioceseId) {
            $this->deaneryOptions = [];

            return;
        }

        $this->deaneryOptions = Deanery::query()
            ->where('did', $this->dioceseId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $row->name,
            ])
            ->values()
            ->toArray();
    }

    protected function syncWardOptions(): void
    {
        $this->wardOptions = VietnamAddressResolver::wardsForSelect(
            $this->province ? (string) $this->province : null
        );
    }

    public function save(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isParishAdmin() && $user->parish_id, 403);

        $parish = ParishNew::query()->findOrFail($user->parish_id);

        $validated = $this->validate([
            'name'               => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('parishes', 'name')->ignore($parish->id),
            ],
            'parish_priest_name' => 'nullable|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'dioceseId'          => 'required|integer|exists:dioceses,id',
            'deaneryId'          => 'required|integer|exists:deanerys,id',
            'province'           => 'nullable|string|max:20',
            'ward'               => 'nullable|string|max:20',
        ], [
            'name.required'      => 'Tên giáo xứ là bắt buộc.',
            'name.unique'        => 'Tên giáo xứ đã tồn tại.',
            'dioceseId.required' => 'Vui lòng chọn giáo phận.',
            'deaneryId.required' => 'Vui lòng chọn giáo hạt.',
        ]);

        $deanery = Deanery::query()->find($validated['deaneryId']);

        if (! $deanery || (int) $deanery->did !== (int) $validated['dioceseId']) {
            $this->addError('deaneryId', 'Giáo hạt không thuộc giáo phận đã chọn.');

            return;
        }

        $parish->update([
            'name'               => trim($validated['name']),
            'parish_priest_name' => trim((string) ($validated['parish_priest_name'] ?? '')) ?: null,
            'phone'              => trim((string) ($validated['phone'] ?? '')) ?: null,
            'diocese_id'         => (int) $validated['dioceseId'],
            'deanery_id'         => (int) $validated['deaneryId'],
            'province'           => $validated['province'] ?: null,
            'ward'               => $validated['ward'] ?: null,
        ]);

        $this->emit('toast', 'message', 'Đã cập nhật thông tin giáo xứ.');
    }

    public function render()
    {
        $user = Auth::user();

        $layout = match (true) {
            $user === null => 'frontend.layout.landing',
            $user->canManageCatechism() => 'frontend.layout.main',
            $user->canManageParishioners() => 'frontend.layout.parishioner',
            default => 'frontend.layout.main',
        };

        return view('livewire.parish.parish-settings')
            ->extends($layout)
            ->section('content');
    }
}
