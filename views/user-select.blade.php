@extends('layouts.app')

@section('title', 'Who are you? - HomeSrv')

@section('content')
    <h1>Who are you?</h1>

    @if(empty($users))
        <p>No users yet. <a href="/settings/">Go to settings</a> to add users.</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
            @foreach($users as $user)
                <form method="POST" action="/user/select.php">
                    <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">{{ $user['name'] }}</button>
                </form>
            @endforeach
        </div>
    @endif
@endsection
