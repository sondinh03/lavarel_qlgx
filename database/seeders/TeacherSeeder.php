<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'parish_id'      => 31,
                'last_name'      => 'Nguyễn Văn',
                'first_name'     => 'An',
                'gender'         => 'male',
                'birthday'       => '1990-05-15',
                'phone_number'   => '0901234567',
                'email'          => 'an.nguyen@gmail.com',
            ],
            [
                'parish_id'      => 31,
                'last_name'      => 'Trần Thị',
                'first_name'     => 'Bình',
                'gender'         => 'female',
                'birthday'       => '1995-08-20',
                'phone_number'   => '0912345678',
                'email'          => 'binh.tran@gmail.com',
            ],
            [
                'parish_id'      => 31,
                'last_name'      => 'Lê Thị',
                'first_name'     => 'Chi',
                'gender'         => 'female',
                'birthday'       => '1988-03-10',
                'phone_number'   => '0923456789',
                'email'          => null, // không có email → dùng phone
            ],
        ];

        foreach ($teachers as $data) {
            DB::transaction(function () use ($data) {
                // Tạo email cho account
                $email = $data['email']
                    ?? $data['phone_number'] . '@giaoly.local';

                // Tạo User account
                $user = User::create([
                    'name'       => $data['last_name'] . ' ' . $data['first_name'],
                    'email'      => $email,
                    'parish_id'  => $data['parish_id'],
                    'password'   => Hash::make('12345678'), // mật khẩu mặc định
                ]);

                // Gán role catechist
                $user->assignRole('catechist');

                // Tạo Teacher
                Teacher::create([
                    ...$data,
                    'user_id'   => $user->id,
                    'is_active' => true,
                ]);
            });
        }
    }
}
