@extends('layouts.app')

@section('title', 'Settings - HomeSrv')

@section('content')
    <h1>Settings</h1>

    @if(!empty($success))
        <p style="color: var(--color-success); margin-bottom: 1rem;">{{ $success }}</p>
    @endif

    {{-- Password --}}
    <div class="card">
        <h2>{{ $hasPassword ? 'Change Password' : 'Set Password' }}</h2>
        <form method="POST" action="/settings/index.php">
            <input type="hidden" name="action" value="set_password">
            <div class="form-group">
                <label for="password">{{ $hasPassword ? 'New password' : 'Password' }}</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">{{ $hasPassword ? 'Update Password' : 'Set Password' }}</button>
        </form>
    </div>

    {{-- Users --}}
    <div class="card">
        <h2>Household Members</h2>
        @if(!empty($users))
            <div style="margin-bottom: 1rem;">
                @foreach($users as $user)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-border);">
                        <span>{{ $user['name'] }}</span>
                        <form method="POST" action="/settings/index.php" style="margin: 0;" onsubmit="return confirm('Remove {{ $user['name'] }}?')">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="/settings/index.php" style="display: flex; gap: 0.5rem;">
            <input type="hidden" name="action" value="add_user">
            <input type="text" name="name" placeholder="Name" required style="flex: 1;">
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>

    {{-- Calendar Feeds --}}
    <div class="card">
        <h2>Calendar Feeds</h2>
        @if(!empty($feeds))
            <div style="margin-bottom: 1rem;">
                @foreach($feeds as $feed)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-border);">
                        <div>
                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: {{ $feed['color'] }}; vertical-align: middle;"></span>
                            <span>{{ $feed['name'] }}</span>
                            @if(!$feed['is_enabled'])
                                <span style="color: var(--color-text-muted); font-size: 0.8rem;">(disabled)</span>
                            @endif
                            @if($feed['cached_at'])
                                <span style="color: var(--color-text-muted); font-size: 0.75rem;">Fetched: {{ $feed['cached_at'] }}</span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 0.3rem;">
                            <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                hx-get="/settings/edit-feed.php?feed_id={{ $feed['id'] }}"
                                hx-target="#feed-modal"
                                hx-swap="innerHTML">Edit</button>
                            <form method="POST" action="/settings/index.php" style="margin: 0;">
                                <input type="hidden" name="action" value="toggle_feed">
                                <input type="hidden" name="feed_id" value="{{ $feed['id'] }}">
                                <button type="submit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">{{ $feed['is_enabled'] ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form method="POST" action="/settings/index.php" style="margin: 0;" onsubmit="return confirm('Remove this feed?')">
                                <input type="hidden" name="action" value="delete_feed">
                                <input type="hidden" name="feed_id" value="{{ $feed['id'] }}">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="/settings/index.php">
            <input type="hidden" name="action" value="add_feed">
            <div class="form-group">
                <label for="feed_name">Feed name</label>
                <input type="text" id="feed_name" name="feed_name" placeholder="Work calendar" required>
            </div>
            <div class="form-group">
                <label for="feed_url">iCal URL</label>
                <input type="url" id="feed_url" name="feed_url" placeholder="https://..." required>
            </div>
            <div class="form-group">
                <label for="feed_color">Color</label>
                <input type="color" id="feed_color" name="feed_color" value="#3b82f6" style="width: 60px; height: 36px; padding: 2px;">
            </div>
            <button type="submit" class="btn btn-primary">Add Feed</button>
        </form>
    </div>

    {{-- Weather --}}
    <div class="card">
        <h2>Weather</h2>
        <form method="POST" action="/settings/index.php">
            <input type="hidden" name="action" value="save_weather">
            <div style="display: flex; gap: 0.5rem;">
                <div class="form-group" style="flex: 1;">
                    <label for="weather_latitude">Latitude</label>
                    <input type="text" id="weather_latitude" name="weather_latitude" value="{{ $settings['weather_latitude'] ?? '' }}" placeholder="51.5074">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="weather_longitude">Longitude</label>
                    <input type="text" id="weather_longitude" name="weather_longitude" value="{{ $settings['weather_longitude'] ?? '' }}" placeholder="-0.1278">
                </div>
            </div>
            <div class="form-group">
                <label for="weather_units">Temperature units</label>
                <select id="weather_units" name="weather_units">
                    <option value="celsius" {{ ($settings['weather_units'] ?? 'celsius') === 'celsius' ? 'selected' : '' }}>Celsius</option>
                    <option value="fahrenheit" {{ ($settings['weather_units'] ?? '') === 'fahrenheit' ? 'selected' : '' }}>Fahrenheit</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

    {{-- API Token --}}
    @if(!empty($settings['api_token']))
    <div class="card">
        <h2>API Token</h2>
        <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Use this token to connect the Kobo client. Keep it private.</p>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <input type="text" value="{{ $settings['api_token'] }}" readonly
                   style="flex: 1; font-family: monospace; font-size: 0.85rem; padding: 0.5rem; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text);"
                   onclick="this.select()">
            <button type="button" class="btn btn-secondary" style="white-space: nowrap;"
                    onclick="navigator.clipboard.writeText('{{ $settings['api_token'] }}')">Copy</button>
        </div>
        <form method="POST" action="/settings/index.php" style="margin: 0;"
              onsubmit="return confirm('Regenerate the API token? The old token will stop working.')">
            <input type="hidden" name="action" value="regenerate_token">
            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Regenerate token</button>
        </form>
    </div>
    @endif

    {{-- Timezone --}}
    <div class="card">
        <h2>Timezone</h2>
        <form method="POST" action="/settings/index.php">
            <input type="hidden" name="action" value="save_timezone">
            <div class="form-group">
                <label for="timezone">Timezone</label>
                <input type="text" id="timezone" name="timezone" value="{{ $settings['timezone'] ?? 'UTC' }}" placeholder="America/New_York">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

    @if(!empty($_SESSION['authenticated']))
        <div style="margin-top: 2rem; text-align: center;">
            <a href="/logout.php" class="btn btn-secondary">Log out</a>
        </div>
    @endif

    <div id="feed-modal"></div>
@endsection
