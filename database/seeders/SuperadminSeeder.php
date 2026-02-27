<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'superadmin@maxumax.my'],
            [
                'name' => 'Superadmin Maxumax',
                'password' => 'Superadmin#1234',
                'role' => 'superadmin',
            ]
        );
    }
}
