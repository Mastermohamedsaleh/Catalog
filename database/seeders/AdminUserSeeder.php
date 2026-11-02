<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sec_master@catalogpro.com'],
            [
                'name' => 'System Master',
                'password' => Hash::make('CatalogPro2026'),
                'role' => 'catalog_master_root',
                'is_super'=> true,
                'email_verified_at' => now(),
            ]
        );
    }
}