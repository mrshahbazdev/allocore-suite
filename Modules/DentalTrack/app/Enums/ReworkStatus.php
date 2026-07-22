<?php

namespace Modules\DentalTrack\Enums;

enum ReworkStatus: string
{
    case Pending = 'pending';
    case InRework = 'in_rework';
    case Resolved = 'resolved';
}
