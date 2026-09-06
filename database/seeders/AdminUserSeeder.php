<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default CommercePilot admin account.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@commercepilot.test'],
            [
                'name' => 'CommercePilot Admin',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
