@extends('layouts.app')

@section('title', 'Events - HomeSrv')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>Events</h1>
        <form method="post" action="/events/refresh.php" hx-post="/events/refresh.php" hx-target="#events-list" hx-swap="innerHTML" hx-disabled-elt="find button" style="margin: 0;">
            <button type="submit" class="btn btn-secondary">
                <span class="refresh-label">Refresh</span>
                <span class="refresh-busy">Refreshing…</span>
            </button>
        </form>
    </div>

    <div id="events-list">
        @include('events._list', ['events' => $events, 'status' => $status])
    </div>
@endsection
