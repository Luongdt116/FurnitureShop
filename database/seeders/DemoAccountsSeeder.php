<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $legacyCustomer = User::query()->where('email', 'customer@auraliving.vn')->first();
        $newCustomerExists = User::query()->where('email', 'customer@gmail.com')->exists();
        if ($legacyCustomer && !$newCustomerExists) {
            $legacyCustomer->email = 'customer@gmail.com';
            $legacyCustomer->save();
        }

        User::updateOrCreate(
            ['email' => 'admin@auraliving.vn'],
            [
                'name' => 'Trần Minh Hoàng',
                'password' => Hash::make('admin123'),
                'phone' => '0908123456',
                'role' => 'admin',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Nguyễn Thanh Tùng',
                'password' => Hash::make('password123'),
                'phone' => '0912345678',
                'role' => 'customer',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
                'is_active' => true,
            ]
        );
    }
}
