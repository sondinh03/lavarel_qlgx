<?php

namespace App\Notifications;

use App\Models\CatechismClass;
use App\Models\Teacher;
use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeacherAssignedToClass extends Notification
{
    use Queueable;
    use SendsWebPush;

    public function __construct(
        public CatechismClass $class,
        public Teacher $teacher,
        public string $roleLabel
    ) {
    }

    public function via($notifiable): array
    {
        return $this->viaWebPushChannels(['database']);
    }

    public function toArray($notifiable): array
    {
        $className = $this->class->name ?? 'lớp học';

        return [
            'title' => 'Được phân công lớp giáo lý',
            'body'  => "Bạn được phân công {$this->roleLabel} lớp {$className}.",
            'url'   => null,
            'level' => 'info',
        ];
    }
}
