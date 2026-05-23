<?php

namespace Cocosmos\FilamentStickySaveBar\Concerns;

/**
 * Add this trait to an EditRecord or CreateRecord page to opt out of the sticky save bar.
 */
trait HasStickySaveBarDisabled
{
    public bool $stickySaveBarEnabled = false;
}
