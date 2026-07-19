# HomeSrv

Household task tracking web app. PHP 8.1+, SQLite, HTMX, BladeOne templating.

## Quick reference

- **Web root**: `public/` (served by Apache on port 8080)
- **Database**: `database/homesrv.sqlite` (created by `php bin/setup.php`)
- **Plan**: `~/.claude/plans/sunny-frolicking-bentley.md`

## Testing

Use `?_db=test.sqlite` to run against a separate database, keeping the production one intact:

```bash
php bin/setup.php test.sqlite
# Then use ?_db=test.sqlite on all URLs in curl commands
curl -s "http://localhost:8080/tasks/?_db=test.sqlite"
```

Clean up with `rm database/test.sqlite` when done.

## Architecture

- **Filesystem-based routing**: URLs map directly to PHP files in `public/`
- **PRG pattern**: All form POST handlers redirect after processing; flash messages via `Bootstrap::flash()`/`getFlash()`
- **HTMX partials**: Endpoints check `Bootstrap::isHtmx()` and return partial HTML for HTMX requests, full pages otherwise
- **Auth flow**: Shared password → user select → session. Settings page is exempt from user-select requirement. First run (no password set) redirects everything to settings.

## Key conventions

- Models (`src/Models/`) are static classes with PDO queries, not ORMs
- Services (`src/Services/`) contain business logic (recurrence, calendar, weather)
- Views use Blade syntax in `views/`, compiled cache in `cache/`
- Dashboard has its own dark-themed layout (`layouts/dashboard.blade.php`, `dashboard.css`)
- Mobile interface uses the light-themed layout (`layouts/app.blade.php`, `app.css`)

## Common tasks

- **Add a dependency**: `composer require package/name`
- **Reset DB**: `rm database/homesrv.sqlite && php bin/setup.php`
- **Fetch calendars**: `php bin/fetch-calendars.php` (also meant for cron)

## Kobo client

`kobo/` holds a native e-ink client skeleton for Kobo e-readers (C + FBInk,
cross-compiled via the NickelTC podman image, launched by NickelMenu on the
device). Build with `make -C kobo`; install onto a USB-mounted Kobo with
`make -C kobo install`. See `kobo/README.md`.
