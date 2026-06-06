<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Subject;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $priority = fake()->randomElement(['low', 'medium', 'high']);
        $status = fake()->randomElement(['pending', 'in_progress', 'completed']);

        return [
            'subject_id'    => Subject::factory(),
            'category_id'   => Category::factory(),
            'title'         => fake()->sentence(4),
            'description'   => fake()->optional(0.7)->paragraph(),
            'priority'      => $priority,
            'status'        => $status,
            'points_earned' => $status === 'completed' ? Task::POINTS[$priority] : 0,
            'due_date'      => fake()->optional(0.8)->dateTimeBetween('-1 week', '+2 weeks'),
            'completed_at'  => $status === 'completed' ? fake()->dateTimeBetween('-2 weeks', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status'        => 'pending',
            'points_earned' => 0,
            'completed_at'  => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn() => [
            'status'        => 'in_progress',
            'points_earned' => 0,
            'completed_at'  => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status'        => 'completed',
            'points_earned' => Task::POINTS[$attrs['priority'] ?? 'medium'],
            'completed_at'  => fake()->dateTimeBetween('-2 weeks', 'now'),
        ]);
    }
}
