<?php

use App\Mail\OverdueTaskMail;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:send-overdue-reminders {--days-ahead=1 : Number of days ahead to check} {--include-overdue : Include already-overdue tasks}', function () {
    $daysAhead = (int) $this->option('days-ahead');
    $includeOverdue = (bool) $this->option('include-overdue');

    $dueDates = [Carbon::today(), Carbon::today()->addDays($daysAhead)];

    if ($includeOverdue) {
        $dueDates[] = Carbon::today()->subDay();
    }

    $tasks = Task::with(['subject.user', 'category'])
        ->whereNot('status', 'completed')
        ->where(function ($q) use ($dueDates) {
            foreach ($dueDates as $i => $date) {
                $method = $i === 0 ? 'whereDate' : 'orWhereDate';
                $q->$method('due_date', $date);
            }
        })
        ->get();

    if ($tasks->isEmpty()) {
        $this->info('No tasks due soon. No reminders sent.');
        return 0;
    }

    $grouped = $tasks->groupBy(fn($task) => $task->subject?->user_id);
    $sent = 0;

    foreach ($grouped as $userId => $userTasks) {
        $user = $userTasks->first()->subject?->user;
        if (!$user || !$user->email) {
            continue;
        }
        Mail::to($user->email, $user->name)
            ->send(new OverdueTaskMail($userTasks, $user->name));
        $sent++;
    }

    $this->info("Sent {$sent} reminder email(s) for {$tasks->count()} task(s).");
})->purpose('Send email reminders for tasks nearing their due date');

Artisan::command('ai:prune-sessions', function () {
    $count = \App\Models\AiChatSession::where('updated_at', '<', now()->subDays(30))->delete();
    $this->info("Pruned {$count} AI chat sessions older than 30 days.");
})->purpose('Prune AI chat sessions older than 30 days');

Schedule::command('tasks:send-overdue-reminders --days-ahead=1')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->description('Morning: remind about tasks due tomorrow');

Schedule::command('tasks:send-overdue-reminders --days-ahead=0 --include-overdue')
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->description('Evening: remind about tasks due today and overdue tasks');

Schedule::command('ai:prune-sessions')
    ->daily()
    ->withoutOverlapping()
    ->description('Daily: prune AI chat sessions older than 30 days');
