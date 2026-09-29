<?php

namespace HomeSrv\Ui;

/**
 * The envelope a UI tree travels in.
 *
 * Keeping the root node inside an envelope leaves room to add whatever the
 * client needs to be told next (refresh policy, a named screen to open) without
 * changing the node format itself. The client rejects a schema version it does
 * not understand rather than guessing.
 */
final class Screen
{
    /** Bumped only for a change the current client could not parse safely. */
    public const SCHEMA = 1;

    public static function envelope(Node $root, string $name = 'dashboard'): array
    {
        return [
            'schema' => self::SCHEMA,
            'screen' => $name,
            'root' => $root,
        ];
    }
}
