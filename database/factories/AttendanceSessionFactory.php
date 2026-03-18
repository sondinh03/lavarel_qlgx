<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    public function definition(): array
    {
        $classId = \App\Models\CatechismClass::first()->id;

        return [
            'class_id'  => $classId,
            'date'      => now(),
            'semester'  => 1,
            'type'      => AttendanceSession::TYPE_CLASS,
            'status'    => AttendanceSession::STATUS_OPENING,
        ];
    }

    public function open(): static
    {
        return $this->state([
            'status' => AttendanceSession::STATUS_OPENING,
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => AttendanceSession::STATUS_CLOSED,
        ]);
    }
}