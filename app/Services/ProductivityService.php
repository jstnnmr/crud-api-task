<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProductivityService
{
    public function getMonthlyStats(int $userId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $dailyStats = Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as tasks_count, COALESCE(SUM(points_earned), 0) as points')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $daysInMonth = $start->daysInMonth;
        $firstDayOfWeek = $start->dayOfWeek;

        $calendar = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateStr = $date->toDateString();
            $dayStats = $dailyStats->get($dateStr);

            $calendar[] = [
                'day'             => $day,
                'date'            => $dateStr,
                'tasks_completed' => (int) ($dayStats->tasks_count ?? 0),
                'points'          => (int) ($dayStats->points ?? 0),
                'is_today'        => $date->isToday(),
                'is_past'         => $date->isPast() || $date->isToday(),
                'level'           => $this->productivityLevel((int) ($dayStats->points ?? 0)),
            ];
        }

        return [
            'year'             => $year,
            'month'            => $month,
            'month_name'       => $start->format('F'),
            'calendar'         => $calendar,
            'days_in_month'    => $daysInMonth,
            'first_day_of_week' => $firstDayOfWeek,
            'total_tasks'      => $dailyStats->sum('tasks_count'),
            'total_points'     => $dailyStats->sum('points'),
        ];
    }

    public function getWeeklyStats(int $userId): array
    {
        $weeks = [];
        for ($i = 0; $i < 8; $i++) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

            $stats = Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$start, $end])
                ->selectRaw('COUNT(*) as tasks_count, COALESCE(SUM(points_earned), 0) as points')
                ->first();

            $weeks[] = [
                'label'           => $i === 0 ? 'This Week' : ($i === 1 ? 'Last Week' : $start->format('M j')),
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'tasks_completed' => (int) ($stats->tasks_count ?? 0),
                'points'          => (int) ($stats->points ?? 0),
            ];
        }

        return $weeks;
    }

    public function getStreak(int $userId): array
    {
        $completedDates = Task::whereHas('subject', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->selectRaw('DATE(completed_at) as date')
            ->distinct()
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d))
            ->values();

        if ($completedDates->isEmpty()) {
            return ['current' => 0, 'longest' => 0];
        }

        $current = 0;
        $check = Carbon::today();
        foreach ($completedDates as $date) {
            if ($date->eq($check)) {
                $current++;
                $check->subDay();
            } elseif ($date->lt($check)) {
                break;
            }
        }

        $longest = 0;
        $streak = 1;
        for ($i = 1; $i < $completedDates->count(); $i++) {
            if ($completedDates[$i]->diffInDays($completedDates[$i - 1]) === 1) {
                $streak++;
            } else {
                $longest = max($longest, $streak);
                $streak = 1;
            }
        }
        $longest = max($longest, $streak);

        return ['current' => $current, 'longest' => $longest];
    }

    public function getHonorNote(int $weeklyPoints, int $weeklyTasks): array
    {
        if ($weeklyPoints >= 40 || $weeklyTasks >= 10) {
            return [
                'icon'  => '👑',
                'title' => 'Legendary Week!',
                'message' => "You're absolutely unstoppable! This level of dedication is inspiring. Keep shining bright!",
            ];
        }

        if ($weeklyPoints >= 25 || $weeklyTasks >= 6) {
            return [
                'icon'  => '🌟',
                'title' => 'Outstanding!',
                'message' => "What an incredible week! Your hard work is truly paying off. You should be so proud of yourself!",
            ];
        }

        if ($weeklyPoints >= 15 || $weeklyTasks >= 4) {
            return [
                'icon'  => '💪',
                'title' => 'Great Effort!',
                'message' => "You're making wonderful progress! Every task you complete brings you closer to your goals. Keep going!",
            ];
        }

        if ($weeklyPoints >= 5 || $weeklyTasks >= 2) {
            return [
                'icon'  => '🌱',
                'title' => 'Steady Progress',
                'message' => "Small steps lead to big achievements. You're building momentum, and that's what matters most!",
            ];
        }

        if ($weeklyPoints > 0 || $weeklyTasks > 0) {
            return [
                'icon'  => '✨',
                'title' => 'You Started!',
                'message' => "Getting started is the hardest part, and you did it! Tomorrow is a chance to do even more.",
            ];
        }

        return [
            'icon'  => '🌙',
            'title' => 'Fresh Start',
            'message' => "Every great journey begins with a single step. This week is full of possibility — set your intentions and go for it!",
        ];
    }

    private function productivityLevel(int $points): string
    {
        if ($points >= 20) return 'high';
        if ($points >= 10) return 'medium';
        if ($points > 0)  return 'low';
        return 'none';
    }
}
