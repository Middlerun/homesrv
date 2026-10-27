<?php

namespace HomeSrv\Ui;

use DateTimeImmutable;
use DateTimeZone;

/**
 * The Koboard dashboard, as a UI tree.
 *
 * Koboard (the e-ink client, its own repo) only lays out and paints what it is
 * sent; this decides what the dashboard looks like. Changing the dashboard is
 * a server edit, not a new build on the device.
 *
 * The tree built here is the *body* of the screen, clock included: the times
 * are placeholders (see Node::clock) that the device substitutes from its own
 * clock every frame. The client wraps the result in the one row it still owns,
 * the status line, which has to keep working while the server is unreachable.
 * See screen_frame() in Koboard's src/screens.c.
 */
final class DashboardScreen
{
    private Theme $theme;

    public function __construct(
        private array $data,
        int $screenWidth,
        private ?DateTimeZone $tz = null,
    ) {
        $this->theme = new Theme($screenWidth);
    }

    /**
     * The dashboard as the client receives it: the tree, plus how often to
     * refresh it.
     */
    public function envelope(): array
    {
        return Screen::envelope(
            $this->build(),
            refreshSeconds: 1800,
            // Relative to the screen's own URL (/api/ui.php), so it follows the
            // server wherever it is hosted.
            ping: 'ping.php',
            pingSeconds: 10,
            flashSeconds: 7200,
        );
    }

    public function build(): Node
    {
        $t = $this->theme;
        $weather = $this->data['weather'] ?? null;

        $header = Node::box(['pad' => [0, $t->margin, 0, $t->margin]], [
            Node::spacer(20),
        ]);

        if ($weather) {
            $header->add(
                $this->weatherBlock($weather),
                Node::spacer(40),
            );
            if (!empty($weather['forecast'])) {
                $header->add(
                    $this->forecastRow($weather['forecast']),
                    Node::spacer(30),
                );
            }
        }

        return Node::box(['h' => Node::flex(1)], [
            $this->clockRow(),
            $header,
            // Full-bleed divider: it sits outside the header's padding.
            Node::rule(4),
            Node::spacer(30),
            Node::box(['dir' => 'row', 'h' => Node::flex(1)], [
                $this->tasksColumn(),
                Node::rule(4, Node::DARK, vertical: true),
                $this->calendarColumn(),
            ]),
        ]);
    }

    /**
     * Date on the left, time on the right. Both are placeholders: the device
     * substitutes its own clock every frame and repaints this row on its own
     * on the minute, which is why it carries the key it looks for.
     */
    private function clockRow(): Node
    {
        $t = $this->theme;

        return Node::box([
            'key' => 'clock',
            'dir' => 'row',
            'gap' => $t->margin,
            'cross' => Node::CROSS_END,
            'pad' => [40, $t->margin, 0, $t->margin],
        ], [
            Node::text(Node::clock('%a, %b %e'), [
                'size' => $t->fontDate,
                'w' => Node::flex(1),
                'maxLines' => 1,
            ]),
            Node::text(Node::clock('%I:%M'), [
                'size' => $t->fontDate,
                'align' => Node::ALIGN_END,
                'maxLines' => 1,
            ]),
        ]);
    }

    // ---- weather ---------------------------------------------------------

    private function weatherBlock(array $w): Node
    {
        $t = $this->theme;

        $detail = sprintf(
            "%s · %d°/%d° · ☂%.1fmm · 💧%d%%",
            $w['description'] ?? '',
            (int) ($w['today_high'] ?? 0),
            (int) ($w['today_low'] ?? 0),
            (float) ($w['rain_mm'] ?? 0),
            (int) ($w['humidity'] ?? 0),
        );

        return Node::box(['gap' => $t->gapSmall], [
            Node::text(
                sprintf('%s  %d°', self::weatherIcon((int) ($w['code'] ?? 0)),
                    (int) ($w['temperature'] ?? 0)),
                ['size' => $t->fontTemp, 'align' => Node::ALIGN_CENTER, 'maxLines' => 1],
            ),
            Node::text($detail, [
                'size' => $t->fontDetail,
                'align' => Node::ALIGN_CENTER,
                'maxLines' => 1,
            ]),
        ]);
    }

    private function forecastRow(array $forecast): Node
    {
        $t = $this->theme;
        $row = Node::box(['dir' => 'row', 'gap' => $t->gap]);

        foreach ($forecast as $f) {
            $row->add(Node::box(['dir' => 'col', 'w' => Node::flex(1), 'gap' => 2], [
                Node::text((string) ($f['name'] ?? ''), [
                    'size' => $t->fontFcast,
                    'align' => Node::ALIGN_CENTER,
                    'maxLines' => 1,
                ]),
                Node::text(self::weatherIcon((int) ($f['code'] ?? 0)), [
                    'size' => $t->fontFcast + 4,
                    'align' => Node::ALIGN_CENTER,
                    'maxLines' => 1,
                ]),
                Node::text(sprintf('%d°/%d°', (int) ($f['high'] ?? 0), (int) ($f['low'] ?? 0)), [
                    'size' => $t->fontFcast,
                    'align' => Node::ALIGN_CENTER,
                    'maxLines' => 1,
                ]),
            ]));
        }
        return $row;
    }

    /** WMO weather interpretation codes — https://open-meteo.com/en/docs */
    public static function weatherIcon(int $code): string
    {
        return match (true) {
            $code === 0  => '☀',    // clear sky
            $code === 1  => '🌤',    // mainly clear
            $code === 2  => '⛅',    // partly cloudy
            $code === 3  => '☁',    // overcast
            $code <= 48  => '🌫',    // fog / rime fog
            $code <= 57  => '🌧',    // drizzle / freezing drizzle
            $code <= 67  => '🌧',    // rain / freezing rain
            $code <= 77  => '❄',    // snow fall / snow grains
            $code <= 82  => '🌦',    // rain showers
            $code <= 86  => '🌨',    // snow showers
            $code <= 99  => '⛈',    // thunderstorm / thunderstorm with hail
            default      => '?',
        };
    }

    // ---- tasks -----------------------------------------------------------

    private function tasksColumn(): Node
    {
        $t = $this->theme;
        $col = Node::box([
            'dir' => 'col',
            'w' => Node::flex(1),
            'gap' => $t->gap,
            'pad' => [0, $t->gap, 0, $t->margin],
            'clip' => true,
            'overflowText' => '… +{n} more',
        ], [
            Node::text('TASKS', ['size' => $t->fontHeader, 'maxLines' => 1]),
        ]);

        $tasks = $this->data['tasks'] ?? [];
        if (!$tasks) {
            $col->add(Node::text('Nothing to do', ['size' => $t->fontMeta, 'maxLines' => 1]));
        }
        foreach ($tasks as $task) {
            $col->add($this->taskCard($task));
        }
        return $col;
    }

    private function taskCard(array $task): Node
    {
        $t = $this->theme;

        $meta = [$this->dueLabel((string) $task['next_due'])];
        foreach (['due_time', 'assigned_name', 'recur_type'] as $field) {
            if (!empty($task[$field])) {
                $meta[] = $task[$field];
            }
        }

        return Node::box(['gap' => 4, 'key' => 'task-' . (int) $task['id']], [
            Node::text((string) $task['title'], ['size' => $t->fontTitle, 'maxLines' => 2]),
            Node::text(implode(' · ', $meta), ['size' => $t->fontMeta, 'maxLines' => 1]),
            $this->doneButton((int) $task['id']),
        ]);
    }

    /**
     * The Done button sizes itself from its padding, so the touch target grows
     * with the screen's DPI instead of being a hard-coded pixel height.
     */
    private function doneButton(int $taskId): Node
    {
        $t = $this->theme;

        return Node::box([
            'dir' => 'row',
            'w' => Node::pct(60),
            'main' => Node::MAIN_CENTER,
            'cross' => Node::CROSS_CENTER,
            'pad' => [8, 12, 8, 12],
            'bg' => Node::LIGHT,
            'border' => 2,
            'borderColor' => Node::BLACK,
            // The toast is kept short so it fits the button on one line at
            // both panel sizes (see Koboard's docs/ui-schema.md).
            'action' => Node::post('/api/tasks/complete.php', "task_id=$taskId", 'COMPLETED!'),
        ], [
            Node::text('Done', [
                'size' => $t->fontBtn,
                'fg' => Node::BLACK,
                'style' => Node::FONT_BOLD,
                'align' => Node::ALIGN_CENTER,
                'maxLines' => 1,
            ]),
        ]);
    }

    /** "Due today", "Due on Tuesday", "Was due on 05/09", ... */
    private function dueLabel(string $nextDue): string
    {
        $today = new DateTimeImmutable(
            ($this->data['date_iso'] ?? 'today') . ' 12:00', $this->tz);
        $due = new DateTimeImmutable($nextDue . ' 12:00', $this->tz);
        $diff = (int) $today->diff($due)->format('%r%a');

        if ($diff === 0) {
            return 'Due today';
        }
        if ($diff === -1) {
            return 'Was due yesterday';
        }
        if ($diff > 0 && $diff < 4) {
            return 'Due on ' . $due->format('l');
        }
        if ($diff < 0 && $diff > -4) {
            return 'Was due on ' . $due->format('l');
        }
        return ($diff > 0 ? 'Due on ' : 'Was due on ') . $due->format('d/m');
    }

    // ---- calendar --------------------------------------------------------

    private function calendarColumn(): Node
    {
        $t = $this->theme;
        $col = Node::box([
            'dir' => 'col',
            'w' => Node::flex(1),
            'gap' => $t->gapTiny,
            'pad' => [0, $t->margin, 0, $t->gap],
            'clip' => true,
            'overflowText' => '… +{n} more',
        ]);

        $this->calAdd($col, Node::text('TODAY', ['size' => $t->fontHeader, 'maxLines' => 1]), false);
        $this->addEvents($col, today: true);
        $this->calAdd($col, Node::text('TOMORROW', ['size' => $t->fontHeader, 'maxLines' => 1]), false);
        $this->addEvents($col, today: false);

        return $col;
    }

    /**
     * Add an item to the calendar column. The column's own gap is the tight
     * one, so anything that wants normal separation asks for it here. Leading
     * items never get extra space, which keeps the column top-aligned with
     * TASKS.
     */
    private function calAdd(Node $col, Node $item, bool $tight): void
    {
        if ($col->hasChildren() && !$tight) {
            $item->padTop($this->theme->gap - $this->theme->gapTiny);
        }
        $col->add($item);
    }

    private function addEvents(Node $col, bool $today): void
    {
        $count = 0;
        foreach ($this->data['calendar_events'] ?? [] as $event) {
            if ((bool) ($event['is_today'] ?? false) !== $today) {
                continue;
            }
            $this->calAdd($col, $this->eventRow($event), (bool) ($event['is_over'] ?? false));
            $count++;
        }
        if ($count === 0) {
            $this->calAdd($col, Node::text('Nothing scheduled', [
                'size' => $this->theme->fontMeta,
                'maxLines' => 1,
            ]), false);
        }
    }

    private function eventRow(array $event): Node
    {
        $t = $this->theme;
        $isOver = (bool) ($event['is_over'] ?? false);
        $font = $isOver ? $t->fontEvtSub : $t->fontEvt;
        $when = !empty($event['all_day']) ? 'All day' : (string) ($event['time'] ?? '');

        // Summary and feed name share one column beside the time, so the feed
        // name lines up with the summary instead of floating under the time.
        $detail = Node::box(['dir' => 'col', 'w' => Node::flex(1), 'gap' => 2], [
            Node::text((string) ($event['summary'] ?? ''), [
                'size' => $font,
                'maxLines' => $isOver ? 1 : 3,
            ]),
        ]);
        if (!$isOver) {
            $detail->add(Node::text('— ' . ($event['feed_name'] ?? ''), [
                'size' => $t->fontEvtSub,
                'maxLines' => 1,
            ]));
        }

        return Node::box(['dir' => 'row', 'gap' => $t->gapTiny], [
            Node::text($when, ['size' => $font, 'w' => Node::pct(40), 'maxLines' => 1]),
            $detail,
        ]);
    }
}
