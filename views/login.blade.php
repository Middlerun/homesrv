@extends('layouts.app')

@section('title', 'Login - HomeSrv')

@section('content')
    <h1>Login</h1>

    @if(!empty($error))
        <p style="color: var(--color-danger); margin-bottom: 1rem;">{{ $error }}</p>
    @endif

    <form method="POST" action="/login.php">
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autofocus required>
        </div>
        <button type="submit" class="btn btn-primary">Log in</button>
    </form>
@endsection
