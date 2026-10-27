<?php

namespace HomeSrv\Ui;

/**
 * The envelope a UI tree travels in.
 *
 * Besides the tree, it carries how often the client should come back, and
 * where to check the server is alive. Every field but the tree is optional;
 * the client has a default for each, and limits on how far a server can push
 * it. See "The wire" in Koboard's docs/ui-schema.md.
 *
 * The client rejects a schema version it does not understand rather than
 * guessing.
 */
final class Screen
{
    /** Bumped only for a change the current client could not parse safely. */
    public const SCHEMA = 1;

    /**
     * @param ?int    $refreshSeconds how often the client refetches this screen
     * @param ?string $ping           a cheap URL that answers 200 while the server
     *                                is up — absolute, or relative to this screen's
     * @param ?int    $pingSeconds    how often to check it; 0 = never
     * @param ?int    $flashSeconds   how often to flash the panel clear of
     *                                ghosting; 0 = never
     */
    public static function envelope(
        Node $root,
        string $name = 'dashboard',
        ?int $refreshSeconds = null,
        ?string $ping = null,
        ?int $pingSeconds = null,
        ?int $flashSeconds = null,
    ): array {
        $refresh = array_filter([
            'seconds' => $refreshSeconds,
            'ping' => $ping,
            'pingSeconds' => $pingSeconds,
            'flashSeconds' => $flashSeconds,
        ], fn ($v) => $v !== null);

        $envelope = ['schema' => self::SCHEMA, 'screen' => $name];
        if ($refresh) {
            $envelope['refresh'] = $refresh;
        }
        $envelope['root'] = $root;
        return $envelope;
    }
}
