<?php

namespace Modules\DentalTrack\Enums;

enum StepStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Skipped = 'skipped';
}
