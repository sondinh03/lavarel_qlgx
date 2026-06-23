<?php

namespace App\Actions\MarriageAnnouncement;

use App\DTOs\MarriageProcessResult;
use App\Events\MarriageCreated;
use App\Models\Marriage;
use App\Models\MarriageAnnouncement;
use App\Services\MarriageService;
use Illuminate\Support\Facades\DB;

class CreateMarriageFromAnnouncementAction
{
    public function __construct(private MarriageService $marriageService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(MarriageAnnouncement $announcement, array $data): MarriageProcessResult
    {
        if ((int) $announcement->status !== 1) {
            throw new \InvalidArgumentException('Chỉ tạo hôn phối khi rao đã hoàn thành.');
        }

        $groom = $announcement->groomParticipant();
        $bride = $announcement->brideParticipant();

        if (! $groom?->idgiaodan || ! $bride?->idgiaodan) {
            throw new \InvalidArgumentException('Thiếu thông tin bên nam hoặc bên nữ.');
        }

        return DB::transaction(function () use ($announcement, $data, $groom, $bride) {
            $priestName = SaveMarriageAnnouncementAction::priestName($announcement->priest);

            $marriage = Marriage::create([
                'husband_id'         => $groom->idgiaodan,
                'wife_id'            => $bride->idgiaodan,
                'married_date'       => $data['married_date'] ?? null,
                'certificate_number' => $data['certificate_number'] ?? null,
                'parish_id'          => $data['parish_id'] ?? $announcement->pid,
                'parish_name'        => $data['parish_name'] ?? null,
                'place_ward_id'      => $data['place_ward_id'] ?? null,
                'place_province'     => $data['place_province'] ?? null,
                'priest_witness'     => $data['priest_witness'] ?? $priestName,
                'witness_1'          => $data['witness_1'] ?? null,
                'witness_2'          => $data['witness_2'] ?? null,
                'status'             => $data['status'] ?? Marriage::STATUS_VALID,
                'note'               => $data['note'] ?? null,
            ]);

            $result = $marriage->status === Marriage::STATUS_VALID
                ? $this->marriageService->processValidMarriage($marriage)
                : new MarriageProcessResult($marriage->load(['husband', 'wife']));

            event(new MarriageCreated($marriage, $result));

            return $result;
        });
    }
}
