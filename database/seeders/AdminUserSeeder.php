<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => config('rh.admin.email'),
            ],
            [
                'name' => config('rh.admin.name'),
                'password' => config('rh.admin.password'),
                'is_admin' => true,
            ]
        );
    }
}
