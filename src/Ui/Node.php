<?php

namespace HomeSrv\Ui;

use InvalidArgumentException;
use JsonSerializable;

/**
 * A node in a Kobo UI tree.
 *
 * This is the server-side mirror of the BOX()/TEXT() macros in kobo/src/ui.h,
 * and it emits exactly the wire format documented in kobo/docs/ui-schema.md.
 * The device parses this into its own tree and lays it out; nothing here knows
 * about pixels beyond the lengths it is given.
 *
 * Properties are validated against the schema on the way in. The client
 * ignores anything it does not recognise, so a typo would otherwise be a
 * silently missing style rather than an error.
 */
final class Node implements JsonSerializable
{
    // Grays. The panel is 8-bit grayscale; these are the shades the UI uses.
    public const BLACK = 0;
    public const DARK  = 64;
    public const MED   = 128;
    public const LIGHT = 192;
    public const WHITE = 255;

    // Main-axis distribution (box only).
    public const MAIN_START   = 0;
    public const MAIN_CENTER  = 1;
    public const MAIN_END     = 2;
    public const MAIN_BETWEEN = 3;

    // Cross-axis placement (box only).
    public const CROSS_STRETCH = 0;
    public const CROSS_START   = 1;
    public const CROSS_CENTER  = 2;
    public const CROSS_END     = 3;

    // Text alignment within the node's own rect.
    public const ALIGN_START  = 0;
    public const ALIGN_CENTER = 1;
    public const ALIGN_END    = 2;

    // Font faces. Missing faces fall back to regular on the device.
    public const FONT_REGULAR     = 0;
    public const FONT_BOLD        = 1;
    public const FONT_ITALIC      = 2;
    public const FONT_BOLD_ITALIC = 3;

    // Action kinds the client accepts from the wire. Quitting the app and
    // switching servers are the device's own business — the menu owns those —
    // and ui_from_json() rejects them, so there is nothing to send here.
    public const ACT_POST    = 1;
    public const ACT_REFRESH = 2;
    public const ACT_OPEN    = 4;

    private const COMMON_PROPS = [
        'key' => 'string',
        'w' => 'length',
        'h' => 'length',
        'pad' => 'pad',
        'border' => 'int',
        'borderColor' => 'gray',
        'bg' => 'gray',
        'fg' => 'gray',
        'action' => 'action',
    ];

    private const BOX_PROPS = [
        'dir' => 'dir',
        'gap' => 'int',
        'main' => 'int',
        'cross' => 'int',
        'clip' => 'bool',
        'overflowText' => 'string',
    ];

    private const TEXT_PROPS = [
        'text' => 'string',
        'size' => 'int',
        'style' => 'int',
        'align' => 'int',
        'maxLines' => 'int',
        'noEllipsis' => 'bool',
    ];

    private const ACTION_PROPS = ['kind', 'path', 'body', 'target', 'toast'];

    /** @var array<string, mixed> */
    private array $props;

    /** @var Node[] */
    private array $children = [];

    /** @param array<string, mixed> $props */
    private function __construct(private string $kind, array $props)
    {
        $allowed = self::COMMON_PROPS
            + ($kind === 'box' ? self::BOX_PROPS : self::TEXT_PROPS);

        $checked = [];
        foreach ($props as $name => $value) {
            if ($value === null) {
                continue;
            }
            if (!isset($allowed[$name])) {
                throw new InvalidArgumentException(
                    "unknown $kind property '$name' (see kobo/docs/ui-schema.md)"
                );
            }
            $checked[$name] = self::coerce($name, $allowed[$name], $value);
        }
        $this->props = $checked;
    }

    /**
     * @param array<string, mixed> $props
     * @param Node[] $children
     */
    public static function box(array $props = [], array $children = []): self
    {
        $node = new self('box', $props);
        return $node->add(...$children);
    }

    /** @param array<string, mixed> $props */
    public static function text(string $text, array $props = []): self
    {
        return new self('text', ['text' => $text] + $props);
    }

    /** A fixed-size gap. A box with no background draws nothing. */
    public static function spacer(int $px, bool $horizontal = false): self
    {
        return self::box($horizontal ? ['w' => $px] : ['h' => $px]);
    }

    /** A filled bar: a divider is just a small box with a background. */
    public static function rule(int $thickness, int $gray = self::DARK, bool $vertical = false): self
    {
        return self::box(($vertical ? ['w' => $thickness] : ['h' => $thickness])
            + ['bg' => $gray]);
    }

    // ---- lengths ---------------------------------------------------------

    /** A share of the parent's leftover main-axis space. */
    public static function flex(int $n = 1): string
    {
        return "flex:$n";
    }

    /** A share of the parent's content box on this axis. */
    public static function pct(int $n): string
    {
        return "$n%";
    }

    // ---- client-side values ----------------------------------------------

    /**
     * A placeholder the device fills in when it draws the tree.
     *
     * The time cannot be sent: it is already stale when the tree arrives, and
     * it has to keep ticking between fetches and while the server is
     * unreachable. So the format is decided here and the value is the device's
     * own clock, substituted on every frame. `$format` is strftime(3), as C
     * spells it — not PHP's date() letters.
     */
    public static function clock(string $format = ''): string
    {
        if (str_contains($format, '}')) {
            throw new InvalidArgumentException("clock format cannot contain '}'");
        }
        return $format === '' ? '{clock}' : '{clock:' . $format . '}';
    }

    // ---- actions ---------------------------------------------------------

    /**
     * A form-encoded POST, optionally with optimistic toast feedback shown in
     * the node's own rect while the request is in flight.
     */
    public static function post(string $path, string $body, ?string $toast = null): array
    {
        return array_filter([
            'kind' => self::ACT_POST,
            'path' => $path,
            'body' => $body,
            'toast' => $toast,
        ], fn ($v) => $v !== null);
    }

    // ---- tree ------------------------------------------------------------

    /** Append children. Nulls are skipped, so callers can build conditionally. */
    public function add(?self ...$children): self
    {
        foreach ($children as $child) {
            if ($child !== null) {
                $this->children[] = $child;
            }
        }
        return $this;
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    /**
     * Add to the node's top padding. Used for spacing that depends on what
     * came before it in a column, which the node itself cannot know.
     */
    public function padTop(int $extra): self
    {
        $pad = $this->props['pad'] ?? [0, 0, 0, 0];
        $pad[0] += $extra;
        $this->props['pad'] = $pad;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $out = ['kind' => $this->kind] + $this->props;
        if ($this->children) {
            $out['children'] = $this->children;
        }
        return $out;
    }

    // ---- validation ------------------------------------------------------

    private static function coerce(string $name, string $type, mixed $value): mixed
    {
        switch ($type) {
            case 'string':
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'$name' must be a string");
                }
                return $value;

            case 'int':
                if (!is_int($value)) {
                    throw new InvalidArgumentException("'$name' must be an int");
                }
                return $value;

            case 'bool':
                return (bool) $value;

            case 'gray':
                if (!is_int($value) || $value < 0 || $value > 255) {
                    throw new InvalidArgumentException("'$name' must be a gray 0-255");
                }
                return $value;

            case 'dir':
                if ($value !== 'row' && $value !== 'col') {
                    throw new InvalidArgumentException("'dir' must be 'row' or 'col'");
                }
                return $value;

            case 'length':
                // Ints are pixels; the wire format carries every length as a
                // string so "200", "37%" and "flex:1" stay one field.
                if (is_int($value)) {
                    return (string) $value;
                }
                if (is_string($value) && preg_match('/^(auto|-?\d+|\d+%|flex:\d+)$/', $value)) {
                    return $value;
                }
                throw new InvalidArgumentException(
                    "'$name' must be an int, 'auto', 'N%' or 'flex:N'"
                );

            case 'pad':
                if (is_int($value)) {
                    return array_fill(0, 4, $value);
                }
                if (is_array($value) && count($value) === 4) {
                    foreach ($value as $v) {
                        if (!is_int($v)) {
                            throw new InvalidArgumentException("'pad' entries must be ints");
                        }
                    }
                    return array_values($value);
                }
                throw new InvalidArgumentException("'pad' must be an int or [top, right, bottom, left]");

            case 'action':
                if (!is_array($value) || !isset($value['kind'])) {
                    throw new InvalidArgumentException("'action' must be an array with a 'kind'");
                }
                foreach (array_keys($value) as $k) {
                    if (!in_array($k, self::ACTION_PROPS, true)) {
                        throw new InvalidArgumentException("unknown action property '$k'");
                    }
                }
                return $value;
        }

        throw new InvalidArgumentException("unhandled property type '$type'");
    }
}
