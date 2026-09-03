<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Category;
use App\Models\Task;
use App\Models\Note;
use App\Models\TaskInvitation;
use App\Models\TaskActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Primary User
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. Create Collaborators
        $collab1 = User::factory()->create([
            'name'  => 'Jane Doe',
            'email' => 'collab1@example.com',
        ]);
        $collab2 = User::factory()->create([
            'name'  => 'John Smith',
            'email' => 'collab2@example.com',
        ]);
        $collab3 = User::factory()->create([
            'name'  => 'Alice Johnson',
            'email' => 'collab3@example.com',
        ]);
        $collaborators = [$collab1, $collab2, $collab3];

        // 3. Create Subjects for Primary User
        $subjects = collect();
        $subjectDefs = [
            ['name' => 'Mathematics',      'color' => '#8e7dff'],
            ['name' => 'Science',          'color' => '#6ee7b7'],
            ['name' => 'English',          'color' => '#fbbf24'],
            ['name' => 'History',          'color' => '#f87171'],
            ['name' => 'Art',              'color' => '#f472b6'],
            ['name' => 'Computer Science', 'color' => '#818cf8'],
        ];
        foreach ($subjectDefs as $s) {
            $subjects->push(Subject::factory()->create([
                'user_id' => $user->id,
                'name'    => $s['name'],
                'color'   => $s['color'],
            ]));
        }

        // Create a subject for each collaborator to allow them to own tasks
        $collabSubjects = collect();
        foreach ($collaborators as $collab) {
            $collabSubjects->push(Subject::factory()->create([
                'user_id' => $collab->id,
                'name'    => 'Shared Workspace - ' . $collab->name,
                'color'   => '#34d399',
            ]));
        }

        // 4. Create Categories for Primary User
        $categories = collect();
        $categoryNames = [
            'Homework', 'Quiz', 'Project', 'Lab Report', 'Presentation',
            'Exam Prep', 'Reading', 'Online Lecture', 'Group Work', 'Discussion'
        ];
        foreach ($categoryNames as $name) {
            $categories->push(Category::factory()->create([
                'user_id' => $user->id,
                'name'    => $name,
            ]));
        }

        // Create categories for collaborators
        $collabCategories = collect();
        foreach ($collaborators as $collab) {
            $collabCategories->push(Category::factory()->create([
                'user_id' => $collab->id,
                'name'    => 'Collaboration Task',
            ]));
        }

        // 5. Seed 35 Tasks for Primary User (for ~4 pages of pagination)
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['pending', 'in_progress', 'completed'];

        for ($i = 1; $i <= 35; $i++) {
            $priority = $priorities[($i % 3)];
            $status = $statuses[($i % 3)];
            $completed = $status === 'completed';
            $dueDate = now()->addDays(($i % 15) - 5);

            Task::factory()->create([
                'subject_id'    => $subjects->random()->id,
                'category_id'   => $categories->random()->id,
                'title'         => "Task #{$i}: " . fake()->sentence(3),
                'description'   => "This is the description for Task #{$i}. " . fake()->paragraph(),
                'priority'      => $priority,
                'status'        => $status,
                'points_earned' => $completed ? Task::POINTS[$priority] : 0,
                'due_date'      => $dueDate,
                'completed_at'  => $completed ? now()->subHours($i) : null,
                'created_at'    => now()->subDays(40 - $i),
            ]);
        }

        // 6. Seed 18 Personal Notes for Primary User (for ~3 pages of pagination)
        for ($i = 1; $i <= 18; $i++) {
            Note::factory()->create([
                'user_id'    => $user->id,
                'title'      => "Lecture Note #{$i}: " . fake()->sentence(3),
                'content'    => "Details for lecture note #{$i}.\n\n" . fake()->paragraphs(2, true),
                'color'      => fake()->randomElement(['#fff9c4', '#ffcc80', '#b2dfdb', '#e1bee7', '#c8e6c9', '#f8bbd0']),
                'created_at' => now()->subDays(20 - $i),
                'updated_at' => now()->subHours(18 - $i),
            ]);
        }

        // 7. Seed 12 Collaborative Shared Tasks (6 owned by primary user, 6 owned by collaborators)
        $teamTasks = collect();

        // 6 tasks owned by Primary User with collaborators attached
        for ($i = 1; $i <= 6; $i++) {
            $priority = $priorities[$i % 3];
            $status = $statuses[$i % 3];
            $completed = $status === 'completed';

            $task = Task::factory()->create([
                'subject_id'    => $subjects->random()->id,
                'category_id'   => $categories->random()->id,
                'title'         => "Shared Task (Primary User) #{$i}",
                'description'   => "Collaborative project task managed by primary user. " . fake()->paragraph(),
                'priority'      => $priority,
                'status'        => $status,
                'points_earned' => $completed ? Task::POINTS[$priority] : 0,
                'due_date'      => now()->addDays($i + 2),
                'completed_at'  => $completed ? now()->subMinutes(30) : null,
            ]);

            // Attach 1-2 collaborators
            $task->collaborators()->attach(
                fake()->randomElements([$collab1->id, $collab2->id, $collab3->id], rand(1, 2)),
                ['role' => 'collaborator']
            );

            $teamTasks->push($task);
        }

        // 6 tasks owned by Collaborators with Primary User attached
        for ($i = 1; $i <= 6; $i++) {
            $owner = $collaborators[$i % 3];
            $subject = $collabSubjects->firstWhere('user_id', $owner->id);
            $category = $collabCategories->firstWhere('user_id', $owner->id);

            $priority = $priorities[($i + 1) % 3];
            $status = $statuses[($i + 1) % 3];
            $completed = $status === 'completed';

            $task = Task::factory()->create([
                'subject_id'    => $subject->id,
                'category_id'   => $category->id,
                'title'         => "Shared Task ({$owner->name}) #{$i}",
                'description'   => "Collaborative task initiated by {$owner->name}. " . fake()->paragraph(),
                'priority'      => $priority,
                'status'        => $status,
                'points_earned' => $completed ? Task::POINTS[$priority] : 0,
                'due_date'      => now()->addDays($i + 5),
                'completed_at'  => $completed ? now()->subMinutes(90) : null,
            ]);

            // Attach Primary User
            $task->collaborators()->attach($user->id, ['role' => 'collaborator']);

            // Attach another collaborator optionally
            $otherCollab = collect($collaborators)->reject(fn($c) => $c->id === $owner->id)->first();
            if ($otherCollab) {
                $task->collaborators()->attach($otherCollab->id, ['role' => 'collaborator']);
            }

            $teamTasks->push($task);
        }

        // 8. Seed 10 Shared Collaborative Notes (5 owned by primary user, 5 owned by collaborators)
        // 5 owned by Primary User with collaborators attached
        for ($i = 1; $i <= 5; $i++) {
            $note = Note::factory()->create([
                'user_id' => $user->id,
                'title'   => "Shared Meeting Note (Primary User) #{$i}",
                'content' => "Collaborative meeting contents. " . fake()->paragraph(),
                'color'   => '#e1bee7',
            ]);

            $note->collaborators()->attach(
                fake()->randomElements([$collab1->id, $collab2->id, $collab3->id], rand(1, 2)),
                ['role' => 'collaborator']
            );
        }

        // 5 owned by Collaborators with Primary User attached
        for ($i = 1; $i <= 5; $i++) {
            $owner = $collaborators[$i % 3];
            $note = Note::factory()->create([
                'user_id' => $owner->id,
                'title'   => "Shared Class Note ({$owner->name}) #{$i}",
                'content' => "Collaborative class notes by {$owner->name}. " . fake()->paragraph(),
                'color'   => '#b2dfdb',
            ]);

            $note->collaborators()->attach($user->id, ['role' => 'collaborator']);
        }

        // 9. Seed Task Invitations
        // 3 pending invitations sent to primary user's email
        for ($i = 1; $i <= 3; $i++) {
            $inviter = $collaborators[($i - 1) % 3];
            $subject = $collabSubjects->firstWhere('user_id', $inviter->id);
            $category = $collabCategories->firstWhere('user_id', $inviter->id);

            $task = Task::factory()->create([
                'subject_id'  => $subject->id,
                'category_id' => $category->id,
                'title'       => "Invitation Task #{$i} from {$inviter->name}",
                'status'      => 'pending',
            ]);

            TaskInvitation::create([
                'task_id'       => $task->id,
                'invited_by'    => $inviter->id,
                'invited_email' => $user->email,
                'token'         => Str::random(40),
                'status'        => 'pending',
            ]);
        }

        // 3 accepted invitations
        for ($i = 4; $i <= 6; $i++) {
            $inviter = $collaborators[($i - 1) % 3];
            $subject = $collabSubjects->firstWhere('user_id', $inviter->id);
            $category = $collabCategories->firstWhere('user_id', $inviter->id);

            $task = Task::factory()->create([
                'subject_id'  => $subject->id,
                'category_id' => $category->id,
                'title'       => "Accepted Task #{$i} from {$inviter->name}",
                'status'      => 'in_progress',
            ]);

            $task->collaborators()->attach($user->id, ['role' => 'collaborator']);

            TaskInvitation::create([
                'task_id'       => $task->id,
                'invited_by'    => $inviter->id,
                'invited_email' => $user->email,
                'token'         => Str::random(40),
                'status'        => 'accepted',
            ]);
        }

        // 10. Seed 25 Activity Logs for team tasks
        $actions = ['created', 'updated', 'status_change', 'priority_change', 'collaborator_added'];
        for ($i = 1; $i <= 25; $i++) {
            $task = $teamTasks->random();
            $actor = fake()->randomElement(array_merge([$user], $collaborators));
            $action = $actions[$i % count($actions)];

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'action'  => $action,
                'changes' => $action === 'status_change' ? ['from' => 'pending', 'to' => 'in_progress'] : null,
            ]);
        }
    }
}
