<?php

namespace Cocosmos\FilamentStickySaveBar\Enums;

enum ShowOn: string
{
    case Dirty = 'dirty';
    case DirtyAlways = 'dirty-always';
    case Always = 'always';
}
