<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@auraliving.vn'],
            [
                'name' => 'Trần Minh Hoàng',
                'password' => Hash::make('password'),
                'phone' => '0908123456',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@auraliving.vn'],
            [
                'name' => 'Nguyễn Thanh Tùng',
                'password' => Hash::make('password'),
                'phone' => '0912345678',
                'role' => 'customer',
                'is_active' => true,
            ]
        );
    }
}
