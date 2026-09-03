<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title'   => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'color'   => '#fff9c4',
        ];
    }
}
