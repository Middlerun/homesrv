<?php

namespace HomeSrv\Ui;

/**
 * Spacing and point sizes for a screen of a given width.
 *
 * Point sizes are resolved to pixels on the device, against its own DPI, so
 * this stays resolution-independent. Only the spacing scales with the width.
 *
 * The client keeps its own copy of `margin` for the chrome it still draws
 * itself (the status line and the menu); the formula below is the one it uses,
 * in theme_init() in kobo/src/screens.c. Change one, change the other.
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
