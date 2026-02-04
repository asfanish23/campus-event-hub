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
        User::firstOrCreate(
            ['email' => 'azlinshuha@uitm.edu.my'],
            [
                'name' => 'Azlin Shuha',
                'email' => 'azlinshuha@uitm.edu.my',
                'password' => Hash::make('admin'),
                'role' => 'super_admin',
                'admin_status' => 'approved',
            ]
        );

        User::firstOrCreate(
            ['email' => 'ADHezri@gmail.com'],
            [
                'name' => 'AD Hezri',
                'email' => 'ADHezri@gmail.com',
                'password' => Hash::make('admin'),
                'role' => 'super_admin',
                'admin_status' => 'approved',
            ]
        );
    }
}
