<?php

namespace App\Actions\MarriageAnnouncement;

use App\Http\Controllers\MarriageAnnouncementController;
use App\Models\MarriageAnnouncement;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Priest;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use Illuminate\Support\Facades\DB;

class SaveMarriageAnnouncementAction
{
    public function __construct(private Slugify $slugify) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $groom
     * @param  array<string, mixed>  $bride
     */
    public function handle(?int $id, array $header, array $groom, array $bride): MarriageAnnouncement
    {
        return DB::transaction(function () use ($id, $header, $groom, $bride) {
            $groomImpediment = ! empty($groom['has_impediment']);
            $brideImpediment = ! empty($bride['has_impediment']);

            $userStatus = (int) ($header['status'] ?? 0);

            if ($groomImpediment || $brideImpediment) {
                $status = 2;
            } elseif ($userStatus === 3) {
                $status = 3;
            } elseif ($this->hasAllAnnouncementsCompleted($header)) {
                $status = 1;
            } else {
                $status = 0;
            }

            $data = [
                'name'                 => trim($header['name']),
                'priest'               => $header['priest'] ?: null,
                'announcements_one'    => $header['announcements_one'],
                'announcements_two'    => $header['announcements_two'] ?: null,
                'announcements_three'  => $header['announcements_three'] ?: null,
                'announcements_one_done'   => ! empty($header['announcements_one_done']),
                'announcements_two_done'   => ! empty($header['announcements_two_done']),
                'announcements_three_done' => ! empty($header['announcements_three_done']),
                'did'                  => $header['did'] ?: null,
                'deid'                 => $header['deid'] ?: null,
                'pid'                  => $header['pid'] ?: null,
                'status'               => $status,
            ];

            if ($id) {
                $announcement = MarriageAnnouncement::findOrFail($id);
                $announcement->update($data);
            } else {
                $announcement = MarriageAnnouncement::create($data);
            }

            $this->syncParticipant($announcement, config('marriage-announcement.sex_groom'), $groom);
            $this->syncParticipant($announcement, config('marriage-announcement.sex_bride'), $bride);
            $this->syncSlug($announcement, $header['slug'] ?? null);

            return $announcement->fresh(['parishioners']);
        });
    }

    protected function hasAllAnnouncementsCompleted(array $header): bool
    {
        return ! empty($header['announcements_one_done'])
            && ! empty($header['announcements_two_done'])
            && ! empty($header['announcements_three_done']);
    }

    protected function syncParticipant(MarriageAnnouncement $announcement, int $sex, array $participant): void
    {
        $parishionerId = (int) ($participant['parishioner_id'] ?? 0);
        $manualName    = trim((string) ($participant['manual_name'] ?? ''));

        if ($parishionerId <= 0 && $manualName === '') {
            MarriageAnnouncementParishioners::where('idannouncement', $announcement->id)
                ->where('sex', $sex)
                ->delete();

            return;
        }

        MarriageAnnouncementParishioners::updateOrCreate(
            [
                'idannouncement' => $announcement->id,
                'sex'            => $sex,
            ],
            [
                'idgiaodan'               => $parishionerId > 0 ? $parishionerId : 0,
                'manual_name'             => $parishionerId > 0 ? null : $manualName,
                'status'                  => ! empty($participant['has_impediment']) ? 1 : 0,
                'diocesesold'             => $participant['old_diocese'] ?? '',
                'deanerysold'             => $participant['old_deanery'] ?? '',
                'parishmanagementsold'    => $participant['old_parish_management'] ?? '',
                'parishsold'              => $participant['old_parish'] ?? '',
                'dioceses'                => $participant['diocese'] ?? '',
                'deanerys'                => $participant['deanery'] ?? '',
                'parishmanagements'       => $participant['parish_management'] ?? '',
                'parishs'                 => $participant['parish'] ?? '',
                'diocesesbefore'          => $participant['before_diocese'] ?? null,
                'deanerysbefore'          => $participant['before_deanery'] ?? null,
                'parishmanagementsbefore' => $participant['before_parish_management'] ?? null,
                'parishsbefore'           => $participant['before_parish'] ?? null,
            ]
        );
    }

    protected function syncSlug(MarriageAnnouncement $announcement, ?string $customSlug): void
    {
        $keyword = $customSlug
            ? $this->slugify->slugify($customSlug)
            : $this->slugify->slugify($announcement->name);

        $existing = Slug::where('keyword', $keyword)
            ->where('sluggable_id', '!=', $announcement->id)
            ->exists();

        if ($existing) {
            $keyword = $keyword . '-' . $announcement->id;
        }

        Slug::updateOrCreate(
            [
                'controller'     => MarriageAnnouncementController::class,
                'model'          => MarriageAnnouncement::class,
                'sluggable_id'   => $announcement->id,
            ],
            ['keyword' => $keyword]
        );
    }

    public static function priestName(?int $priestId): ?string
    {
        if (! $priestId) {
            return null;
        }

        return Priest::find($priestId)?->name;
    }
}
