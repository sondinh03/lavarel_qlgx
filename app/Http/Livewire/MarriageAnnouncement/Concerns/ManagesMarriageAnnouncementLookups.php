<?php

namespace App\Http\Livewire\MarriageAnnouncement\Concerns;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Services\MarriageAnnouncementLookupService;

trait ManagesMarriageAnnouncementLookups
{
    /** @var array<string, array<string, array<string, int|string|null>>> */
    public array $participantIds = [
        'groom' => [
            'old'     => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
            'current' => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
            'before'  => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
        ],
        'bride' => [
            'old'     => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
            'current' => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
            'before'  => ['diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null],
        ],
    ];

    /** @var array<string, array<string, array<string, array<int, array<string, string>>>>> */
    public array $participantDropdowns = [
        'groom' => [
            'old'     => ['deanery' => [], 'parish' => [], 'parish_group' => []],
            'current' => ['deanery' => [], 'parish' => [], 'parish_group' => []],
            'before'  => ['deanery' => [], 'parish' => [], 'parish_group' => []],
        ],
        'bride' => [
            'old'     => ['deanery' => [], 'parish' => [], 'parish_group' => []],
            'current' => ['deanery' => [], 'parish' => [], 'parish_group' => []],
            'before'  => ['deanery' => [], 'parish' => [], 'parish_group' => []],
        ],
    ];

    public array $maleParishionerOptions = [];
    public array $femaleParishionerOptions = [];

    protected function lookup(): MarriageAnnouncementLookupService
    {
        return app(MarriageAnnouncementLookupService::class);
    }

    public function searchPriests(?string $query = ''): array
    {
        return $this->lookup()->searchPriests($query);
    }

    public function searchMaleParishioners(?string $query = ''): array
    {
        return $this->lookup()->searchParishioners($this->parishId, 'male', $query);
    }

    public function searchFemaleParishioners(?string $query = ''): array
    {
        return $this->lookup()->searchParishioners($this->parishId, 'female', $query);
    }

    protected function loadParishionerPickerOptions(): void
    {
        $query = \App\Models\Parishioner::query()->with('saint')->orderBy('last_name')->orderBy('first_name');

        $parishFilter = $this->pid ?: $this->parishId;
        if ($parishFilter) {
            $query->where('parish_id', $parishFilter);
        }

        $map = fn ($p) => ['id' => (string) $p->id, 'name' => $p->full_name_with_saint];

        $this->maleParishionerOptions = $this->mergeSelectedParishionerOption(
            (clone $query)->where('gender', 'male')->limit(500)->get()->map($map)->values()->toArray(),
            $this->groom_parishioner_mode === 'pick' ? $this->groom_parishioner_id : null
        );
        $this->femaleParishionerOptions = $this->mergeSelectedParishionerOption(
            (clone $query)->where('gender', 'female')->limit(500)->get()->map($map)->values()->toArray(),
            $this->bride_parishioner_mode === 'pick' ? $this->bride_parishioner_id : null
        );
    }

    /** @param  array<int, array{id: string, name: string}>  $options */
    protected function mergeSelectedParishionerOption(array $options, $selectedId): array
    {
        if (! $selectedId) {
            return $options;
        }

        $selectedId = (string) $selectedId;
        if (collect($options)->contains(fn ($o) => $o['id'] === $selectedId)) {
            return $options;
        }

        $selected = \App\Models\Parishioner::with('saint')->find($selectedId);
        if (! $selected) {
            return $options;
        }

        array_unshift($options, ['id' => $selectedId, 'name' => $selected->full_name_with_saint]);

        return $options;
    }

    protected function mapOptions($rows): array
    {
        return collect($rows)
            ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
            ->values()
            ->toArray();
    }

    protected function syncParticipantDropdownOptions(string $role, string $prefix): void
    {
        $ids = $this->participantIds[$role][$prefix] ?? [];
        $dioceseId = $ids['diocese'] ?? null;
        $deaneryId = $ids['deanery'] ?? null;
        $parishId  = $ids['parish'] ?? null;

        $this->participantDropdowns[$role][$prefix]['deanery'] = $dioceseId
            ? $this->mapOptions(Deanery::where('did', $dioceseId)->where('status', 1)->orderBy('name')->get(['id', 'name']))
            : [];

        $this->participantDropdowns[$role][$prefix]['parish'] = $deaneryId
            ? $this->mapOptions(ParishNew::where('deanery_id', $deaneryId)->where('status', 1)->orderBy('name')->get(['id', 'name']))
            : [];

        $this->participantDropdowns[$role][$prefix]['parish_group'] = $parishId
            ? $this->mapOptions(ParishGroup::where('parish_id', $parishId)->where('status', 1)->orderBy('name')->get(['id', 'name']))
            : [];
    }

    protected function syncAllParticipantDropdownOptions(): void
    {
        foreach (['groom', 'bride'] as $role) {
            foreach (['old', 'current', 'before'] as $prefix) {
                $this->syncParticipantDropdownOptions($role, $prefix);
            }
        }
    }

    protected function handleParticipantCascadeChange(string $role, string $prefix, string $level): void
    {
        $ids        = $this->participantIds[$role][$prefix] ?? [];
        $namePrefix = $this->participantNamePrefix($role, $prefix);

        $levels = ['diocese', 'deanery', 'parish', 'parish_management'];
        $idx    = array_search($level, $levels, true);

        if ($idx !== false) {
            for ($i = $idx + 1; $i < count($levels); $i++) {
                $this->participantIds[$role][$prefix][$levels[$i]] = null;
                $this->{$namePrefix . $levels[$i]} = '';
            }
        }

        $id = $ids[$level] ?? null;
        $name = match ($level) {
            'diocese'           => $id ? Diocese::find($id)?->name : null,
            'deanery'           => $id ? Deanery::find($id)?->name : null,
            'parish'            => $id ? ParishNew::find($id)?->name : null,
            'parish_management' => $id ? ParishGroup::find($id)?->name : null,
            default             => null,
        };

        $this->{$namePrefix . $level} = $name ?? '';

        $this->syncParticipantDropdownOptions($role, $prefix);
    }

    protected function syncParticipantNamesFromIds(string $role, string $prefix): void
    {
        $namePrefix = $this->participantNamePrefix($role, $prefix);
        $ids        = $this->participantIds[$role][$prefix] ?? [];

        foreach (['diocese', 'deanery', 'parish', 'parish_management'] as $level) {
            $id = $ids[$level] ?? null;
            $this->{$namePrefix . $level} = match ($level) {
                'diocese'           => $id ? (Diocese::find($id)?->name ?? '') : '',
                'deanery'           => $id ? (Deanery::find($id)?->name ?? '') : '',
                'parish'            => $id ? (ParishNew::find($id)?->name ?? '') : '',
                'parish_management' => $id ? (ParishGroup::find($id)?->name ?? '') : '',
                default             => '',
            };
        }
    }

    protected function participantNamePrefix(string $role, string $prefix): string
    {
        if ($prefix === 'current') {
            return $role . '_';
        }

        return $role . '_' . $prefix . '_';
    }

    protected function resolveParticipantIdsFromNames(string $role, string $prefix): void
    {
        $namePrefix = $this->participantNamePrefix($role, $prefix);

        $dioceseName = $this->{$namePrefix . 'diocese'} ?: null;
        $deaneryName = $this->{$namePrefix . 'deanery'} ?: null;
        $pmName      = $this->{$namePrefix . 'parish_management'} ?: null;
        $parishName  = $this->{$namePrefix . 'parish'} ?: null;

        if (! $dioceseName && ! $deaneryName && ! $pmName && ! $parishName) {
            $this->participantIds[$role][$prefix] = [
                'diocese' => null, 'deanery' => null, 'parish' => null, 'parish_management' => null,
            ];

            return;
        }

        $dioceseId = $dioceseName
            ? Diocese::where('name', $dioceseName)->value('id')
            : null;
        $deaneryId = ($deaneryName && $dioceseId)
            ? Deanery::where('name', $deaneryName)->where('did', $dioceseId)->value('id')
            : null;
        $parishId = ($parishName && $deaneryId)
            ? ParishNew::where('name', $parishName)->where('deanery_id', $deaneryId)->value('id')
            : null;
        $pmId = ($pmName && $parishId)
            ? ParishGroup::where('name', $pmName)->where('parish_id', $parishId)->value('id')
            : null;

        $this->participantIds[$role][$prefix] = [
            'diocese'           => $dioceseId ? (string) $dioceseId : null,
            'deanery'           => $deaneryId ? (string) $deaneryId : null,
            'parish'            => $parishId ? (string) $parishId : null,
            'parish_management' => $pmId ? (string) $pmId : null,
        ];
    }

    protected function resolveAllParticipantIdsFromNames(): void
    {
        foreach (['groom', 'bride'] as $role) {
            foreach (['old', 'current', 'before'] as $prefix) {
                $this->resolveParticipantIdsFromNames($role, $prefix);
            }
        }
    }

    public function updatedParticipantIds($value, $key = null): void
    {
        if (! is_string($key)) {
            return;
        }

        $parts = explode('.', $key);
        if (count($parts) === 3) {
            $this->handleParticipantCascadeChange($parts[0], $parts[1], $parts[2]);
        }
    }

    protected function setParticipantIdsFromParishioner(string $role, string $prefix, \App\Models\Parishioner $p): void
    {
        $this->participantIds[$role][$prefix] = [
            'diocese'           => $p->diocese_id ? (string) $p->diocese_id : null,
            'deanery'           => $p->deanery_id ? (string) $p->deanery_id : null,
            'parish'            => $p->parish_id ? (string) $p->parish_id : null,
            'parish_management' => $p->parish_area_id ? (string) $p->parish_area_id : null,
        ];

        $this->syncParticipantNamesFromIds($role, $prefix);
        $this->syncParticipantDropdownOptions($role, $prefix);
    }
}
