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
- The Kobo client's UI is built server-side in `src/Ui/` and served by `/api/ui.php`
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

The UI is declarative *and server-built*: `src/Ui/DashboardScreen.php` produces
a tree of nodes, `/api/ui.php` serves it, and the client's layout engine assigns
every node a rect. Touch targets come from the painted tree, so never hard-code
hit rectangles. The schema is `kobo/docs/ui-schema.md`.

**Change the dashboard's appearance in `src/Ui/`, not in C.** The device only
needs rebuilding when the engine changes; `kobo/src/screens.c` is now just the
chrome that must survive the server being down: the status line with its two
invisible corner targets (refresh, menu), the message screen, and the menu that
switches between the servers in `kobo/src/config.c`'s config file. Even the
clock is server-sent, as a `{clock:...}` placeholder the device fills in each
frame (see `UiVars` in `kobo/src/ui_json.h`).

**Do not iterate on Kobo layout by cross-compiling and reinstalling.** The
layout engine builds for the host. For the dashboard (`src/Ui/`):

```bash
bin/kobo-preview                       # PNGs in kobo/build/, ~1s
bin/kobo-preview overflow              # busy day; also: empty, finished
bin/test-ui                            # the trees /api/ui.php sends, vs goldens
```

After a deliberate dashboard change, `bin/test-ui` will fail; check the PNGs,
then `bin/test-ui --update` to accept. Data fixtures are in `tests/ui/fixtures/`
(recordings of `/api/dashboard.php`).

The client is tested on its own, against UI-tree fixtures, with no PHP
involved — it is meant to be extractable as a standalone app:

```bash
make -C kobo test                      # golden rect comparison
make -C kobo golden                    # accept after a deliberate engine change
```

The protocol (screen URL, bearer token, envelope fields) is in
`kobo/docs/ui-schema.md` under "The wire".
