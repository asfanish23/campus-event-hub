<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = collect(config('admin.seed_accounts'))
            ->filter(fn (array $account) => collect($account)->every(
                fn ($value) => is_string($value) && $value !== ''
            ));

        if ($accounts->isEmpty()) {
            throw new \RuntimeException(
                'Configure SUPER_ADMIN_1_* environment variables before running AdminUserSeeder.'
            );
        }

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => 'super_admin',
                    'admin_status' => 'approved',
                ]
            );
        }
    }
}
