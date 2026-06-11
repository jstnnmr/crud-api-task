<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $user = auth()->user();

        $subjects = $user->subjects()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed')])
            ->get();

        $todayTasks = $user->tasks()
            ->with(['subject', 'category'])
            ->whereDate('due_date', Carbon::today())
            ->where('status', '!=', 'completed')
            ->get();

        return view('dashboard', compact('user', 'subjects', 'todayTasks'));
    }
}