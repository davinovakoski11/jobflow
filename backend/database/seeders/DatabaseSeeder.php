<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Os dados de demonstração são exclusivos para local/testing.');
        }
        $user = User::firstOrCreate(['email' => 'demo@jobflow.local'], ['name' => 'Visitante Demo', 'password' => 'demo-password']);
        if ($user->applications()->doesntExist()) {
            JobApplication::factory()->for($user)->create(['company' => 'Orbit Studio', 'role' => 'Desenvolvedor Vue.js', 'status' => 'interview']);
            JobApplication::factory()->for($user)->create(['company' => 'Nuvem Digital', 'status' => 'applied']);
        }
    }
}
