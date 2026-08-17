<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class HiddenEvent
{
    public static function hide(int $feedId, string $uid): void
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT OR IGNORE INTO hidden_events (feed_id, uid) VALUES (?, ?)');
        $stmt->execute([$feedId, $uid]);
    }

    public static function restore(int $feedId, string $uid): void
    {
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM hidden_events WHERE feed_id = ? AND uid = ?');
        $stmt->execute([$feedId, $uid]);
    }

    /**
     * Return a lookup of hidden event UIDs keyed by feed id.
     *
     * @return array<int, array<string, bool>>
     */
    public static function hiddenUids(): array
    {
        $db = Database::get();
        $map = [];
        foreach ($db->query('SELECT feed_id, uid FROM hidden_events')->fetchAll() as $row) {
            $map[(int) $row['feed_id']][$row['uid']] = true;
        }
        return $map;
    }
}
