<?php

namespace HomeSrv\Ui;

/**
 * Spacing and point sizes for a screen of a given width.
 *
 * Point sizes are resolved to pixels on the device, against its own DPI, so
 * this stays resolution-independent. Only the spacing scales with the width.
 *
 * `margin` uses the same formula as Koboard's own (theme_init() in its
 * src/screens.c), so the body lines up with the status line the client draws
 * under it.
 */
final class Theme
{
    public int $margin;
    public int $gap;
    public int $gapSmall;
    public int $gapTiny;

    public int $fontDate   = 14;
    public int $fontTemp   = 30;
    public int $fontDetail = 11;
    public int $fontFcast  = 10;
    public int $fontHeader = 14;
    public int $fontTitle  = 13;
    public int $fontMeta   = 9;
    public int $fontBtn    = 9;
    public int $fontEvt    = 11;
    public int $fontEvtSub = 8;

    public function __construct(int $screenWidth)
    {
        $this->margin = max(10, intdiv($screenWidth, 30));
        $this->gap = $this->margin;
        $this->gapSmall = intdiv($this->margin, 2);
        $this->gapTiny = max(2, intdiv($this->margin, 6));
    }
}
