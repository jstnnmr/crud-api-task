<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->unique()->randomElement([
                'Mathematics', 'Science', 'English', 'History',
                'Filipino', 'Computer Science', 'Physics', 'Chemistry',
                'Biology', 'Algebra', 'Geometry', 'Literature',
            ]),
            'color'   => fake()->randomElement([
                '#8e7dff', '#6ee7b7', '#fbbf24', '#f87171',
                '#818cf8', '#34d399', '#f472b6', '#fb923c',
            ]),
        ];
    }
}
