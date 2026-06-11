<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'role'  => 'parent',
        ]);

        $subjects = collect();
        foreach ([
            ['name' => 'Mathematics', 'color' => '#8e7dff'],
            ['name' => 'Science',     'color' => '#6ee7b7'],
            ['name' => 'English',     'color' => '#fbbf24'],
            ['name' => 'History',     'color' => '#f87171'],
        ] as $s) {
            $subjects->push(Subject::factory()->create([
                'user_id' => $user->id,
                'name'    => $s['name'],
                'color'   => $s['color'],
            ]));
        }

        $categories = collect();
        foreach (['Homework', 'Quiz', 'Project'] as $name) {
            $categories->push(Category::factory()->create([
                'user_id' => $user->id,
                'name'    => $name,
            ]));
        }

        $taskData = [
            ['subject' => 0, 'category' => 0, 'title' => 'Algebra Worksheet',              'priority' => 'medium', 'status' => 'completed', 'due' => '-2 days'],
            ['subject' => 0, 'category' => 1, 'title' => 'Geometry Quiz Prep',             'priority' => 'high',   'status' => 'pending',   'due' => '+3 days'],
            ['subject' => 0, 'category' => 2, 'title' => 'Statistics Project',             'priority' => 'high',   'status' => 'in_progress', 'due' => '+10 days'],
            ['subject' => 0, 'category' => 0, 'title' => 'Fraction Review',               'priority' => 'low',    'status' => 'pending',   'due' => null],

            ['subject' => 1, 'category' => 0, 'title' => 'Lab Report: Photosynthesis',     'priority' => 'medium', 'status' => 'in_progress', 'due' => '+5 days'],
            ['subject' => 1, 'category' => 1, 'title' => 'Periodic Table Quiz',           'priority' => 'high',   'status' => 'completed', 'due' => '-1 days'],
            ['subject' => 1, 'category' => 2, 'title' => 'Volcano Model Project',          'priority' => 'low',    'status' => 'pending',   'due' => '+14 days'],
            ['subject' => 1, 'category' => 0, 'title' => 'Chemical Reactions Worksheet',   'priority' => 'medium', 'status' => 'pending',   'due' => '+7 days'],

            ['subject' => 2, 'category' => 0, 'title' => 'Essay: Romeo and Juliet',        'priority' => 'high',   'status' => 'in_progress', 'due' => '+4 days'],
            ['subject' => 2, 'category' => 1, 'title' => 'Vocabulary Quiz',               'priority' => 'low',    'status' => 'completed', 'due' => '-5 days'],
            ['subject' => 2, 'category' => 0, 'title' => 'Grammar Exercises',             'priority' => 'medium', 'status' => 'pending',   'due' => '+2 days'],
            ['subject' => 2, 'category' => 2, 'title' => 'Book Report: The Giver',         'priority' => 'medium', 'status' => 'pending',   'due' => '+8 days'],

            ['subject' => 3, 'category' => 0, 'title' => 'Timeline of WW2',               'priority' => 'medium', 'status' => 'completed', 'due' => '-3 days'],
            ['subject' => 3, 'category' => 1, 'title' => 'Ancient Civilizations Quiz',     'priority' => 'high',   'status' => 'in_progress', 'due' => '+6 days'],
            ['subject' => 3, 'category' => 2, 'title' => 'Country Research Presentation',  'priority' => 'low',    'status' => 'pending',   'due' => '+12 days'],
        ];

        foreach ($taskData as $t) {
            $subject = $subjects[$t['subject']];
            $category = $categories[$t['category']];
            $priority = $t['priority'];
            $status = $t['status'];
            $completed = $status === 'completed';
            $now = now();

            Task::factory()->create([
                'subject_id'    => $subject->id,
                'category_id'   => $category->id,
                'title'         => $t['title'],
                'description'   => fake()->optional(0.6)->paragraph(),
                'priority'      => $priority,
                'status'        => $status,
                'points_earned' => $completed ? Task::POINTS[$priority] : 0,
                'due_date'      => $t['due'] ? $now->copy()->modify($t['due']) : null,
                'completed_at'  => $completed ? $now->copy()->modify('-'.rand(1, 5).' hours') : null,
            ]);
        }
    }
}
