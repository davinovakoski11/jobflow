<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 'company' => fake()->company(),
            'role' => 'Desenvolvedor Full Stack', 'location' => 'Brasil',
            'url' => null, 'salary_min' => 4000, 'status' => 'saved',
            'notes' => 'Preparar apresentação do projeto.',
            'applied_on' => '2026-10-20', 'remote' => true,
        ];
    }
}
