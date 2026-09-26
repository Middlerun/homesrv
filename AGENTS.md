# HomeSrv

Household task tracking web app. PHP 8.1+, SQLite, HTMX, BladeOne templating.

## Quick reference

- **Web root**: `public/` (served by Apache on port 8082)
- **Database**: `database/homesrv.sqlite` (created by `php bin/setup.php`)
- **Plan**: `~/.claude/plans/sunny-frolicking-bentley.md`

## Testing

Use `?_db=test.sqlite` to run against a separate database, keeping the production one intact:

```bash
php bin/setup.php test.sqlite
# Then use ?_db=test.sqlite on all URLs in curl commands
curl -s "http://localhost:8082/tasks/?_db=test.sqlite"
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

`kobo/` holds a native e-ink client for Kobo e-readers (C + FBInk,
cross-compiled via the NickelTC podman image, launched by NickelMenu on the
device). Build with `make -C kobo`; install onto a USB-mounted Kobo with
`make -C kobo install`. See `kobo/README.md`.

The UI is declarative: `kobo/src/screens.c` builds a tree of nodes and a layout
engine assigns every node a rect. Touch targets come from the painted tree, so
never hard-code hit rectangles. The schema is `kobo/docs/ui-schema.md`.

**Do not iterate on Kobo layout by cross-compiling and reinstalling.** The
layout engine builds for the host:

```bash
make -C kobo preview                   # PNGs in kobo/build/, ~1s
make -C kobo preview FIXTURE=overflow  # busy day; also: empty
make -C kobo test                      # golden rect comparison
```

After a deliberate layout change, `make -C kobo test` will fail; review the
diff and the PNGs, then `make -C kobo golden` to accept.
