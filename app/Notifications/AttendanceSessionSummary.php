<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceSessionSummary extends Notification
{
    use Queueable;

    /**
     * @param  array{class_name: string, present: int, absent_excused: int, absent_unexcused: int, total: int}  $summary
     */
    public function __construct(
        public array $summary,
        public int $classId
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $s = $this->summary;
        $className = $s['class_name'] ?? 'Lớp';
        $present = (int) ($s['present'] ?? 0);
        $excused = (int) ($s['absent_excused'] ?? 0);
        $unexcused = (int) ($s['absent_unexcused'] ?? 0);
        $total = (int) ($s['total'] ?? 0);

        return [
            'title' => "Điểm danh: {$className}",
            'body'  => "Có mặt {$present}/{$total} · Vắng phép {$excused} · Vắng không phép {$unexcused}.",
            'url'   => route('attendance.show', ['classId' => $this->classId]),
            'level' => $unexcused > 0 ? 'warning' : 'info',
        ];
    }
}
