<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'program']);

        $admin = User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin Demo',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');

        $user = User::updateOrCreate([
            'email' => 'st@techupi.id',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('Ddw9889##'),
        ]);

        $user->assignRole('admin');

        $this->call(SccDemoDataSeeder::class);
    }
}
