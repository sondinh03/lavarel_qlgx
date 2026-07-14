<?php

namespace App\Presenters;

use App\Models\Parishioner;
use App\Models\Sacrament;
use Carbon\Carbon;

/**
 * Chứng chỉ bí tích Rửa tội – Thêm sức: xuất từ hồ sơ giáo dân.
 * Các trường kính gửi / mục đích nhập trên modal.
 */
class ParishionerChungChiBiTichPresenter
{
    public function __construct(
        private Parishioner $parishioner,
        private string $recipientPriest = '',
        private string $recipientDiocese = '',
        private string $purpose = '',
    ) {}

    public static function for(
        Parishioner $parishioner,
        string $recipientPriest = '',
        string $recipientDiocese = '',
        string $purpose = '',
    ): self {
        $parishioner->loadMissing([
            'saint',
            'diocese',
            'deanery',
            'parish',
            'parishGroup',
            'father.saint',
            'mother.saint',
            'baptism.parish',
            'baptism.deanery',
            'baptism.diocese',
            'confirmation.parish',
            'confirmation.deanery',
            'confirmation.diocese',
        ]);

        return new self(
            $parishioner,
            trim($recipientPriest),
            trim($recipientDiocese),
            trim($purpose),
        );
    }

    public function toPlaceholders(): array
    {
        $p = $this->parishioner;
        $baptism = $p->baptism;
        $confirmation = $p->confirmation;

        $diocese = $this->labeledName($p->diocese?->name, 'Giáo phận');
        $deanery = $this->labeledName($p->deanery?->name, 'Giáo hạt');
        $parish  = $this->labeledName($p->parish?->name, 'Giáo xứ');
        $priestName = trim((string) ($p->parish?->parish_priest_name ?? ''));

        $origin = trim((string) ($p->origin ?: $p->birth_place ?: $p->full_address_permanent ?: ''));

        return [
            'archdiocese' => $diocese !== ''
                ? mb_strtoupper($diocese, 'UTF-8')
                : '………………',
            'deanery'     => $deanery !== '' ? $deanery : '………………',
            'parish'      => $parish !== '' ? $parish : '………………',

            'recipient_priest'  => $this->blank($this->recipientPriest),
            'recipient_diocese' => $this->blank(
                $this->recipientDiocese !== ''
                    ? $this->recipientDiocese
                    : ($p->diocese?->name ?? '')
            ),
            'priest_name'   => $this->blank($priestName),
            'priest_parish' => $parish !== '' ? $parish : '………………',

            'holy_name'   => $this->blank(trim((string) ($p->full_name_with_saint ?: $p->full_name))),
            'birthday'    => $p->birthday ? $p->birthday->format('d/m/Y') : '………………',
            'origin'      => $this->blank($origin),
            'father_name' => $this->blank($this->parentName('father')),
            'mother_name' => $this->blank($this->parentName('mother')),

            'baptism_date'    => $this->sacramentDate($baptism),
            'baptism_place'   => $this->sacramentPlace($baptism),
            'baptism_giver'   => $this->blank(trim((string) ($baptism?->giver ?? ''))),
            'baptism_sponsor' => $this->blank(trim((string) ($baptism?->sponsor ?? ''))),
            'baptism_number'  => $this->blank($this->sacramentNumber($baptism)),

            'confirmation_date'    => $this->sacramentDate($confirmation),
            'confirmation_place'   => $this->sacramentPlace($confirmation),
            'confirmation_giver'   => $this->blank(trim((string) ($confirmation?->giver ?? ''))),
            'confirmation_sponsor' => $this->blank(trim((string) ($confirmation?->sponsor ?? ''))),
            'confirmation_number'  => $this->blank($this->sacramentNumber($confirmation)),

            'purpose'     => $this->blank($this->purpose),
            'sign_place'  => $parish !== '' ? $parish : '………………',
            'day'         => date('d'),
            'month'       => date('m'),
            'year'        => date('Y'),
            'signer_name' => $this->blank($priestName),
        ];
    }

    public function downloadFilename(): string
    {
        $name = \Illuminate\Support\Str::slug($this->parishioner->full_name ?: 'giaodan', '_');
        if ($name === '') {
            $name = 'giaodan_' . (int) $this->parishioner->id;
        }

        return 'ChungChiBiTich_' . $name . '.docx';
    }

    private function parentName(string $role): string
    {
        $p = $this->parishioner;
        $related = $p->{$role} ?? null;

        if ($related) {
            $related->loadMissing('saint');
            $name = trim((string) ($related->full_name_with_saint ?: $related->full_name));
            if ($name !== '') {
                return $name;
            }
        }

        return trim((string) ($p->{$role . '_name'} ?? ''));
    }

    private function sacramentDate(?Sacrament $s): string
    {
        if (! $s?->received_date) {
            return '………………';
        }

        return $s->received_date instanceof Carbon
            ? $s->received_date->format('d/m/Y')
            : Carbon::parse($s->received_date)->format('d/m/Y');
    }

    private function sacramentPlace(?Sacrament $s): string
    {
        if (! $s) {
            return '………………';
        }

        $parts = array_filter([
            $s->church_name ?: null,
            $s->parish?->name ?: $s->parish_name,
            $s->deanery?->name,
            $s->diocese?->name,
        ]);

        return $parts !== [] ? implode(', ', $parts) : '………………';
    }

    private function sacramentNumber(?Sacrament $s): string
    {
        if (! $s) {
            return '';
        }

        return trim((string) ($s->certificate_number ?: $s->book_number ?: ''));
    }

    private function labeledName(?string $name, string $prefix): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        if (mb_stripos($name, $prefix) === 0) {
            return $name;
        }

        return $prefix . ' ' . $name;
    }

    private function blank(string $value): string
    {
        return $value !== '' ? $value : '………………';
    }
}
