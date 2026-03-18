<?php

namespace Database\Factories;

use App\Models\StudentNew;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentNewFactory extends Factory
{
    protected $model = StudentNew::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            // thêm các field bắt buộc khác của bảng students vào đây
        ];
    }
}
