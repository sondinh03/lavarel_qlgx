<?php

namespace App\Actions\Catechism;

use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\CatechismBoardAnnouncement;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SendCatechistAnnouncementAction
{
    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_GRADE = 'grade';

    /**
     * @return array{count: int, audience_label: string}
     */
    public function handle(
        User $sender,
        int $parishId,
        string $title,
        string $body,
        string $audience,
        ?int $gradeLevelId = null,
        ?string $linkUrl = null,
    ): array {
        $title = trim($title);
        $body = trim($body);

        if ($title === '' || $body === '') {
            throw new InvalidArgumentException('Thiếu tiêu đề hoặc nội dung thông báo.');
        }

        if (! in_array($audience, [self::AUDIENCE_ALL, self::AUDIENCE_GRADE], true)) {
            throw new InvalidArgumentException('Đối tượng nhận không hợp lệ.');
        }

        $recipients = $this->resolveRecipients($parishId, $audience, $gradeLevelId, $sender->id);
        $audienceLabel = $this->audienceLabel($audience, $gradeLevelId);

        if ($recipients->isEmpty()) {
            throw new InvalidArgumentException(
                $audience === self::AUDIENCE_GRADE
                    ? 'Không tìm thấy giáo lý viên (có tài khoản) của khối đã chọn.'
                    : 'Không tìm thấy giáo lý viên nào trong giáo xứ.'
            );
        }

        $notification = new CatechismBoardAnnouncement(
            $title,
            $body,
            $audienceLabel,
            $linkUrl ?: route('notifications.index'),
            (string) ($sender->name ?? ''),
        );

        notify_users($recipients, $notification);

        return [
            'count'          => $recipients->count(),
            'audience_label' => $audienceLabel,
        ];
    }

    /**
     * Đếm trước khi gửi (hiển thị trên form).
     */
    public function countRecipients(
        int $parishId,
        string $audience,
        ?int $gradeLevelId = null,
        ?int $exceptUserId = null
    ): int {
        return $this->resolveRecipients($parishId, $audience, $gradeLevelId, $exceptUserId)->count();
    }

    /**
     * @return Collection<int, User>
     */
    public function resolveRecipients(
        int $parishId,
        string $audience,
        ?int $gradeLevelId = null,
        ?int $exceptUserId = null
    ): Collection {
        if ($audience === self::AUDIENCE_ALL) {
            $query = User::query()
                ->where('parish_id', $parishId)
                ->role('catechist');

            if ($exceptUserId) {
                $query->where('id', '!=', $exceptUserId);
            }

            return $query->get();
        }

        if (! $gradeLevelId) {
            return collect();
        }

        $yearId = NamHoc::query()
            ->ofParish($parishId)
            ->active()
            ->current()
            ->value('id');

        if (! $yearId) {
            $yearId = NamHoc::query()
                ->ofParish($parishId)
                ->active()
                ->orderByDesc('name')
                ->value('id');
        }

        $userIds = Teacher::query()
            ->where('parish_id', $parishId)
            ->whereNotNull('user_id')
            ->whereHas('classes', function ($q) use ($parishId, $gradeLevelId, $yearId) {
                $q->where('classes.parish_id', $parishId)
                    ->where('classes.grade_level_id', $gradeLevelId)
                    ->where('classes.is_active', true)
                    ->where('class_teachers.status', true)
                    ->when($yearId, fn ($c) => $c->where('classes.school_year_id', $yearId));
            })
            ->pluck('user_id')
            ->unique()
            ->filter()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        $query = User::query()
            ->where('parish_id', $parishId)
            ->whereIn('id', $userIds);

        if ($exceptUserId) {
            $query->where('id', '!=', $exceptUserId);
        }

        return $query->get();
    }

    private function audienceLabel(string $audience, ?int $gradeLevelId): string
    {
        if ($audience === self::AUDIENCE_ALL) {
            return 'Toàn trường (GLV)';
        }

        $gradeName = GradeLevel::query()->where('id', $gradeLevelId)->value('name');

        return $gradeName
            ? 'Khối ' . $gradeName
            : 'Theo khối';
    }
}
