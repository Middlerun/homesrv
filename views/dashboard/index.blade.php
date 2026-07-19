@extends('layouts.dashboard')

@section('content')
    <div class="dashboard-grid">
        <div class="dashboard-panel" id="tasks-panel"
            hx-get="/dashboard/partials/tasks.php"
            hx-trigger="load, every 60s"
            hx-swap="innerHTML">
            <h2>Tasks</h2>
            <p style="color: #64748b;">Loading...</p>
        </div>

        <div class="dashboard-sidebar">
            <div class="dashboard-panel" id="calendar-panel"
                hx-get="/dashboard/partials/calendar.php"
                hx-trigger="load, every 300s"
                hx-swap="innerHTML">
                <h2>Calendar</h2>
                <p style="color: #64748b;">Loading...</p>
            </div>

            <div class="dashboard-panel" id="weather-panel"
                hx-get="/dashboard/partials/weather.php"
                hx-trigger="load, every 600s"
                hx-swap="innerHTML">
                <h2>Weather</h2>
                <p style="color: #64748b;">Loading...</p>
            </div>
        </div>
    </div>

    <div id="defer-modal"></div>
@endsection
