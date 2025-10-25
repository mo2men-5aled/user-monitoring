<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user if one doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
        
        // Create a regular user if one doesn't exist
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );
        
        // Create default settings
        Setting::firstOrCreate(
            ['key' => 'idle_timeout'],
            [
                'value' => '300', // 5 minutes in seconds
                'description' => 'Inactivity timeout in seconds'
            ]
        );
        
        Setting::firstOrCreate(
            ['key' => 'monitoring_enabled'],
            [
                'value' => 'true',
                'description' => 'Whether activity monitoring is enabled'
            ]
        );
    }
}
