<?php

namespace Modules\DentalTrack\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case OnHold = 'on_hold';
}
