<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarriageAnnouncementParishioners extends Model
{
    use HasFactory;
    
    protected $table = 'marriage_announcements_parishioners';
    protected $guarded = ['id'];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(MarriageAnnouncement::class, 'idannouncement');
    }

    public function parishioner(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'idgiaodan');
    }

    public function isGroom(): bool
    {
        return (int) $this->sex === (int) config('marriage-announcement.sex_groom');
    }

    public function isBride(): bool
    {
        return (int) $this->sex === (int) config('marriage-announcement.sex_bride');
    }

    public function hasImpediment(): bool
    {
        return (int) $this->status === 1;
    }

    public function isManualEntry(): bool
    {
        return empty($this->idgiaodan) && filled($this->manual_name);
    }

    public function displayName(): ?string
    {
        if ($this->parishioner) {
            return $this->parishioner->full_name_with_saint;
        }

        return $this->manual_name ?: null;
    }

    /** @return array<string, string|null> */
    public function parishGroupLabels(string $prefix): array
    {
        return match ($prefix) {
            'old'    => [
                'diocese' => $this->diocesesold,
                'deanery' => $this->deanerysold,
                'management' => $this->parishmanagementsold,
                'parish' => $this->parishsold,
            ],
            'before' => [
                'diocese' => $this->diocesesbefore,
                'deanery' => $this->deanerysbefore,
                'management' => $this->parishmanagementsbefore,
                'parish' => $this->parishsbefore,
            ],
            'current', '' => [
                'diocese' => $this->dioceses,
                'deanery' => $this->deanerys,
                'management' => $this->parishmanagements,
                'parish' => $this->parishs,
            ],
            default => [
                'diocese' => $this->dioceses,
                'deanery' => $this->deanerys,
                'management' => $this->parishmanagements,
                'parish' => $this->parishs,
            ],
        };
    }
}
