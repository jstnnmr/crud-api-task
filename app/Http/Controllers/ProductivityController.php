<?php

namespace App\Http\Controllers;

use App\Services\ProductivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductivityController extends Controller
{
    public function __construct(
        protected ProductivityService $productivityService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $year = (int) ($request->query('year', now()->year));
        $month = (int) ($request->query('month', now()->month));

        $monthlyStats = $this->productivityService->getMonthlyStats($user->id, $year, $month);
        $weeklyStats = $this->productivityService->getWeeklyStats($user->id);
        $streak = $this->productivityService->getStreak($user->id);

        $currentWeek = $weeklyStats[0];
        $honorNote = $this->productivityService->getHonorNote(
            $currentWeek['points'],
            $currentWeek['tasks_completed']
        );

        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        return view('productivity.index', compact(
            'monthlyStats',
            'weeklyStats',
            'streak',
            'honorNote',
            'year',
            'month',
            'prevMonth',
            'nextMonth'
        ));
    }
}
