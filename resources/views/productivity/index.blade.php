@extends('layouts.app')
@section('title', 'Productivity | EaseTask')

@push('styles')
<style>
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }
    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.65rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .page-subtitle {
        font-size: 0.68rem;
        color: var(--text-muted);
        letter-spacing: 0.1em;
        text-transform: uppercase;
        opacity: 0.7;
        margin-bottom: 1.75rem;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.25rem;
        text-align: center;
    }
    .stat-icon {
        font-size: 1.3rem;
        margin-bottom: 0.3rem;
    }
    .stat-value {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text);
    }
    .stat-label {
        font-size: 0.62rem;
        color: var(--text-muted);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-top: 0.15rem;
    }

    /* Honor Note */
    .honor-card {
        background: linear-gradient(135deg, rgba(142,125,255,0.12), rgba(110,231,183,0.08));
        border: 1px solid rgba(142,125,255,0.25);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        box-shadow: var(--shadow-sm);
    }
    .honor-icon {
        font-size: 2.2rem;
        flex-shrink: 0;
        line-height: 1;
    }
    .honor-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 0.2rem;
    }
    .honor-message {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* Calendar */
    .calendar-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    .calendar-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
    }
    .calendar-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .calendar-nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--surface2);
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.15s;
        min-height: 44px;
        min-width: 44px;
    }
    .calendar-nav a:hover {
        background: rgba(142,125,255,0.15);
        color: var(--accent);
    }
    .calendar-nav .month-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--text);
        min-width: 100px;
        text-align: center;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }
    .cal-day-name {
        text-align: center;
        font-size: 0.6rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.4rem 0;
    }
    .cal-day {
        aspect-ratio: 1;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        background: var(--surface2);
        border: 1px solid transparent;
        transition: all 0.15s;
        min-height: 44px;
        position: relative;
    }
    .cal-day.empty {
        background: transparent;
        border-color: transparent;
    }
    .cal-day.today {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(142,125,255,0.2);
    }
    .cal-day.level-high {
        background: rgba(110,231,183,0.15);
        border-color: rgba(110,231,183,0.3);
    }
    .cal-day.level-medium {
        background: rgba(251,191,36,0.12);
        border-color: rgba(251,191,36,0.25);
    }
    .cal-day.level-low {
        background: rgba(142,125,255,0.08);
        border-color: rgba(142,125,255,0.15);
    }
    .cal-day.level-none {
        background: transparent;
        border-color: transparent;
    }
    .cal-day.level-none:hover {
        background: var(--surface2);
    }
    .cal-day-num {
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--text);
    }
    .cal-day-stats {
        font-size: 0.55rem;
        color: var(--text-muted);
        line-height: 1.2;
        text-align: center;
    }
    .cal-day-stats .pts {
        color: var(--accent);
        font-weight: 600;
    }
    .cal-summary {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    .cal-summary span {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .cal-summary strong {
        color: var(--text);
        font-weight: 600;
    }

    /* Legend */
    .cal-legend {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.75rem;
        font-size: 0.65rem;
        color: var(--text-muted);
    }
    .cal-legend-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .cal-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 4px;
    }
    .cal-legend-dot.high { background: rgba(110,231,183,0.5); border: 1px solid rgba(110,231,183,0.5); }
    .cal-legend-dot.medium { background: rgba(251,191,36,0.4); border: 1px solid rgba(251,191,36,0.4); }
    .cal-legend-dot.low { background: rgba(142,125,255,0.25); border: 1px solid rgba(142,125,255,0.25); }

    /* Weekly Breakdown */
    .weekly-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .weekly-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 1rem;
    }
    .weekly-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid var(--border);
    }
    .weekly-row:last-child { border-bottom: none; }
    .weekly-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text);
        min-width: 80px;
    }
    .weekly-bar-wrap {
        flex: 1;
        height: 8px;
        background: var(--surface2);
        border-radius: 999px;
        overflow: hidden;
    }
    .weekly-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.5s ease;
    }
    .weekly-bar.points {
        background: linear-gradient(90deg, var(--accent), #a599ff);
    }
    .weekly-bar.tasks {
        background: linear-gradient(90deg, var(--success), #7df5c5);
    }
    .weekly-stat {
        font-size: 0.72rem;
        color: var(--text-muted);
        min-width: 50px;
        text-align: right;
    }
    .weekly-stat strong {
        color: var(--text);
        font-weight: 600;
    }
    .weekly-this {
        background: rgba(142,125,255,0.06);
        border-radius: 10px;
        padding: 0 0.5rem;
        margin: 0 -0.5rem;
    }

    @media (max-width: 640px) {
        .container { padding: 1rem; }
        .honor-card { padding: 1.25rem; flex-direction: column; gap: 0.75rem; }
        .cal-day { min-height: 36px; }
        .cal-day-stats { font-size: 0.5rem; }
        .month-label { font-size: 0.75rem; min-width: 80px; }
    }
</style>
@endpush

@section('content')
<div class="container">

    <div class="page-title">📊 Productivity</div>
    <div class="page-subtitle">Track your progress and celebrate your wins ✦</div>

    {{-- Honor Note --}}
    <div class="honor-card">
        <div class="honor-icon">{{ $honorNote['icon'] }}</div>
        <div>
            <div class="honor-title">{{ $honorNote['title'] }}</div>
            <div class="honor-message">{{ $honorNote['message'] }}</div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $currentWeek['tasks_completed'] ?? 0 }}</div>
            <div class="stat-label">Tasks This Week</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-value">{{ $currentWeek['points'] ?? 0 }}</div>
            <div class="stat-label">Points This Week</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔥</div>
            <div class="stat-value">{{ $streak['current'] }}</div>
            <div class="stat-label">Day Streak</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💎</div>
            <div class="stat-value">{{ $streak['longest'] }}</div>
            <div class="stat-label">Best Streak</div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="calendar-wrap">
        <div class="calendar-header">
            <div class="calendar-title">📅 {{ $monthlyStats['month_name'] }} {{ $year }}</div>
            <div class="calendar-nav">
                <a href="?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}" title="Previous month">←</a>
                <span class="month-label">{{ $monthlyStats['month_name'] }} {{ $year }}</span>
                <a href="?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}" title="Next month">→</a>
            </div>
        </div>

        <div class="cal-legend">
            <span class="cal-legend-item"><span class="cal-legend-dot high"></span> High (20+)</span>
            <span class="cal-legend-item"><span class="cal-legend-dot medium"></span> Medium (10-19)</span>
            <span class="cal-legend-item"><span class="cal-legend-dot low"></span> Low (1-9)</span>
        </div>

        <div class="calendar-grid">
            <div class="cal-day-name">Mon</div>
            <div class="cal-day-name">Tue</div>
            <div class="cal-day-name">Wed</div>
            <div class="cal-day-name">Thu</div>
            <div class="cal-day-name">Fri</div>
            <div class="cal-day-name">Sat</div>
            <div class="cal-day-name">Sun</div>

            @php
                $firstDay = $monthlyStats['first_day_of_week'];
                $dowMap = [0 => 6, 1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5];
                $leadingBlanks = $dowMap[$firstDay] ?? 0;
            @endphp

            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div class="cal-day empty"></div>
            @endfor

            @foreach ($monthlyStats['calendar'] as $day)
                @php
                    $classes = 'cal-day';
                    if ($day['is_today']) $classes .= ' today';
                    if ($day['level'] !== 'none') $classes .= ' level-' . $day['level'];
                    else $classes .= ' level-none';
                @endphp
                <div class="{{ $classes }}" title="{{ $day['date'] }}">
                    <div class="cal-day-num">{{ $day['day'] }}</div>
                    @if ($day['tasks_completed'] > 0)
                        <div class="cal-day-stats">
                            {{ $day['tasks_completed'] }}t <span class="pts">+{{ $day['points'] }}p</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="cal-summary">
            <span>📌 Tasks done: <strong>{{ $monthlyStats['total_tasks'] }}</strong></span>
            <span>🏆 Points earned: <strong>{{ $monthlyStats['total_points'] }}</strong></span>
        </div>
    </div>

    {{-- Weekly Breakdown --}}
    <div class="weekly-wrap">
        <div class="weekly-title">📈 Weekly Breakdown</div>

        @php
            $maxPoints = max(array_column($weeklyStats, 'points')) ?: 1;
            $maxTasks = max(array_column($weeklyStats, 'tasks_completed')) ?: 1;
        @endphp

        @foreach ($weeklyStats as $week)
            @php
                $barPoints = round(($week['points'] / $maxPoints) * 100);
                $barTasks = round(($week['tasks_completed'] / $maxTasks) * 100);
                $isCurrent = $loop->first;
            @endphp
            <div class="weekly-row @if ($isCurrent) weekly-this @endif">
                <div class="weekly-label">{{ $week['label'] }}</div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div class="weekly-bar-wrap" style="flex: 1;">
                            <div class="weekly-bar points" style="width: {{ $barPoints }}%;"></div>
                        </div>
                        <div class="weekly-stat"><strong>{{ $week['points'] }}</strong> pts</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div class="weekly-bar-wrap" style="flex: 1;">
                            <div class="weekly-bar tasks" style="width: {{ $barTasks }}%;"></div>
                        </div>
                        <div class="weekly-stat"><strong>{{ $week['tasks_completed'] }}</strong> tasks</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
