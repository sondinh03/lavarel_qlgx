<?php

namespace App\Http\Livewire\Parishioners\Concerns;

use App\Models\Sacrament;

trait ManagesPendingSacraments
{
    public array $pendingSacraments = [];

    public bool $showSacramentForm = false;

    public ?int $editingSacramentIndex = null;

    public string $type = '';

    public ?string $received_date = null;

    public ?string $certificate_number = null;

    public ?int $book_number = null;

    public ?string $giver = null;

    public ?string $sponsor = null;

    public ?string $church_name = null;

    public ?string $anointing_condition = null;

    public ?string $parish_name = null;

    public ?string $note = null;

    protected function sacramentFormRules(): array
    {
        return [
            'type'                => 'required|in:baptism,communion,confirmation,anointing,holy_orders',
            'received_date'       => 'nullable|date',
            'certificate_number'  => 'nullable|string|max:50',
            'book_number'         => 'nullable|integer|min:1',
            'giver'               => 'nullable|string|max:100',
            'sponsor'             => 'nullable|string|max:100',
            'church_name'         => 'nullable|string|max:100',
            'anointing_condition' => 'nullable|string|max:100',
            'parish_name'         => 'nullable|string|max:100',
            'note'                => 'nullable|string|max:500',
        ];
    }

    protected function sacramentFormMessages(): array
    {
        return [
            'type.required'      => 'Vui lòng chọn loại bí tích',
            'type.in'            => 'Loại bí tích không hợp lệ',
            'received_date.date' => 'Ngày không hợp lệ',
        ];
    }

    public function openSacramentForm(string $type = ''): void
    {
        $this->resetSacramentForm();
        $this->type = $type;
        $this->showSacramentForm = true;
    }

    public function closeSacramentForm(): void
    {
        $this->showSacramentForm = false;
        $this->resetSacramentForm();
    }

    public function addPendingSacrament(): void
    {
        $this->validate($this->sacramentFormRules(), $this->sacramentFormMessages());

        if ($this->type !== Sacrament::TYPE_ANOINTING && $this->hasDuplicatePendingSacramentType()) {
            $this->addError('type', 'Đã có bí tích ' . $this->getSacramentTypeName() . ' trong danh sách');

            return;
        }

        $record = $this->buildSacramentRecordFromForm();

        if ($this->editingSacramentIndex !== null) {
            $this->pendingSacraments[$this->editingSacramentIndex] = $record;
        } else {
            $this->pendingSacraments[] = $record;
        }

        $this->closeSacramentForm();
    }

    public function editPendingSacrament(int $index): void
    {
        if (! isset($this->pendingSacraments[$index])) {
            return;
        }

        $item = $this->pendingSacraments[$index];
        $this->editingSacramentIndex = $index;
        $this->type                = $item['type'] ?? '';
        $this->received_date       = $item['received_date'] ?? null;
        $this->certificate_number  = $item['certificate_number'] ?? null;
        $this->book_number         = $item['book_number'] ?? null;
        $this->giver               = $item['giver'] ?? null;
        $this->sponsor             = $item['sponsor'] ?? null;
        $this->church_name         = $item['church_name'] ?? null;
        $this->anointing_condition = $item['anointing_condition'] ?? null;
        $this->parish_name         = $item['parish_name'] ?? null;
        $this->note                = $item['note'] ?? null;
        $this->showSacramentForm     = true;
    }

    public function removePendingSacrament(int $index): void
    {
        if (! isset($this->pendingSacraments[$index])) {
            return;
        }

        unset($this->pendingSacraments[$index]);
        $this->pendingSacraments = array_values($this->pendingSacraments);

        if ($this->editingSacramentIndex === $index) {
            $this->closeSacramentForm();
        }
    }

    protected function persistPendingSacraments(int $parishionerId): void
    {
        foreach ($this->pendingSacraments as $item) {
            Sacrament::create(array_merge($item, ['parishioner_id' => $parishionerId]));
        }
    }

    public function getGroupedPendingSacraments(): array
    {
        $grouped = [];

        foreach (Sacrament::typeOptions() as $type => $label) {
            $records = [];
            foreach ($this->pendingSacraments as $index => $item) {
                if (($item['type'] ?? '') === $type) {
                    $records[] = array_merge($item, ['_index' => $index]);
                }
            }

            $grouped[$type] = [
                'label'    => $label,
                'records'  => $records,
                'multiple' => $type === Sacrament::TYPE_ANOINTING,
            ];
        }

        return $grouped;
    }

    protected function buildSacramentRecordFromForm(): array
    {
        return [
            'type'                => $this->type,
            'received_date'       => $this->received_date ?: null,
            'certificate_number'  => $this->certificate_number,
            'book_number'         => $this->book_number,
            'giver'               => $this->giver,
            'sponsor'             => $this->sponsor,
            'church_name'         => $this->church_name,
            'anointing_condition' => $this->type === Sacrament::TYPE_ANOINTING
                ? $this->anointing_condition
                : null,
            'parish_id'           => null,
            'parish_name'         => $this->parish_name,
            'deanery_id'          => null,
            'diocese_id'          => null,
            'note'                => $this->note,
        ];
    }

    protected function hasDuplicatePendingSacramentType(): bool
    {
        foreach ($this->pendingSacraments as $index => $item) {
            if (($item['type'] ?? '') === $this->type && $index !== $this->editingSacramentIndex) {
                return true;
            }
        }

        return false;
    }

    protected function getSacramentTypeName(): string
    {
        return Sacrament::typeOptions()[$this->type] ?? $this->type;
    }

    protected function resetSacramentForm(): void
    {
        $this->reset([
            'editingSacramentIndex', 'type',
            'received_date', 'certificate_number', 'book_number',
            'giver', 'sponsor', 'church_name', 'anointing_condition',
            'parish_name', 'note',
        ]);
        $this->resetValidation([
            'type', 'received_date', 'certificate_number', 'book_number',
            'giver', 'sponsor', 'church_name', 'anointing_condition',
            'parish_name', 'note',
        ]);
    }
}
