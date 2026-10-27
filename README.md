# HomeSrv

Household task tracking web app. PHP 8.1+, SQLite, HTMX, BladeOne templating.

## Features

- **Recurring tasks** — daily, weekly, monthly, and interval-based recurrence with per-user assignment
- **Task log** — complete/defer/create/edit actions with history
- **Calendar feeds** — iCal (`.ics`) feeds via Sabre/VObject, color-coded, cached
- **Weather** — current conditions + 5-day forecast via Open-Meteo (no API key)
- **Web dashboard** — dark-themed wall display with tasks, calendar, and weather
- **Mobile interface** — light-themed layout for phones
- **Kobo dashboard** — serves a dashboard to [Koboard](https://github.com/Middlerun/koboard), a native e-ink client for Kobo e-readers, at `/api/ui.php`

## Requirements

- PHP 8.1+
- Composer
- Apache (or any server pointing at `public/`)

## Setup

```bash
composer install
php bin/setup.php
```

Point your web server's document root at `public/` (Apache is configured for
port 8082). Or for quick local testing:

```bash
php -S localhost:8082 -t public
```

Then open http://localhost:8082/ and follow the first-run flow:

1. **Settings** — set an app password (first run redirects everything here)
2. **Login** — with the shared password
3. **Select user** — pick which household member you are
4. **Settings** — add users, calendar feeds, weather location, and timezone

## Configuration

All configuration lives in **Settings** in the web UI:

- **App password** — shared across all household members
- **Users** — household members (tasks are assigned to users)
- **Calendar feeds** — iCal URLs with a display color
- **Weather** — latitude, longitude, units (celsius/fahrenheit)
- **Timezone** — used for due-date calculations
- **API token** — used by the Kobo client and API endpoints

## API

JSON API, authenticated via the `X-Api-Token` header (the Settings API token).

- `GET /api/dashboard.php` — tasks (today/overdue/future), today & tomorrow's calendar events, weather
- `GET /api/ping.php` — health check
- `POST /api/tasks/complete.php` — complete a task
- `POST /api/tasks/defer.php` — defer a task

## Testing

Use `?_db=test.sqlite` to run against a separate database, keeping the production one intact:

```bash
php bin/setup.php test.sqlite
# Then use ?_db=test.sqlite on all URLs in curl commands
curl -s "http://localhost:8082/tasks/?_db=test.sqlite"
```

Clean up with `rm database/test.sqlite` when done.

## Maintenance

- **Fetch calendars**: `php bin/fetch-calendars.php` (also meant for cron)
- **Reset DB**: `rm database/homesrv.sqlite && php bin/setup.php`

## Structure

- `public/` — web root; filesystem-based routing (URLs map to PHP files)
- `src/Models/` — static classes with PDO queries
- `src/Services/` — business logic (recurrence, calendar, weather)
- `views/` — Blade templates; compiled cache in `cache/`
- `database/` — SQLite database and schema
- `bin/` — CLI scripts
