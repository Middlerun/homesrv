@extends('layouts.app')

@section('title', 'Events - HomeSrv')

@section('content')
    <h1>Events</h1>

    <div id="events-list">
        @include('events._list', ['events' => $events])
    </div>
@endsection
