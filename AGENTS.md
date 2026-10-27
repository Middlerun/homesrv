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
- The Kobo dashboard is a UI tree built in `src/Ui/` and served by `/api/ui.php`, for the Koboard client
- Mobile interface uses the light-themed layout (`layouts/app.blade.php`, `app.css`)

## Common tasks

- **Add a dependency**: `composer require package/name`
- **Reset DB**: `rm database/homesrv.sqlite && php bin/setup.php`
- **Fetch calendars**: `php bin/fetch-calendars.php` (also meant for cron)

## Koboard (the Kobo e-reader client)

The e-ink client lives in its own repo, Koboard, expected as a sibling
checkout at `../koboard` (override with `KOBOARD_DIR`). It is a general-purpose
client: it fetches a UI tree from a URL, lays it out and paints it, and knows
nothing about HomeSrv. This repo is one server for it.

What the dashboard looks like is decided here, in `src/Ui/`:
`DashboardScreen.php` builds the tree and `/api/ui.php` serves it. The wire
format is Koboard's `docs/ui-schema.md`; `src/Ui/Node.php` validates every
property against it, so a typo throws here instead of being silently ignored on
the device. Touch targets come from the painted tree, so never hard-code hit
rectangles.

**Do not iterate on the dashboard by reinstalling the client.** Koboard's layout
engine builds for the host:

```bash
bin/kobo-preview                       # PNGs in ../koboard/build/, ~1s
bin/kobo-preview overflow              # busy day; also: empty, finished
bin/test-ui                            # the trees /api/ui.php sends, vs goldens
```

After a deliberate dashboard change, `bin/test-ui` will fail; check the PNGs,
then `bin/test-ui --update` to accept. Data fixtures are in `tests/ui/fixtures/`
(recordings of `/api/dashboard.php`). `bin/test-ui` needs only PHP;
`bin/kobo-preview` needs the Koboard checkout.

Changes to the client itself — its layout engine, chrome, menu or protocol
handling — belong in Koboard, not here.
