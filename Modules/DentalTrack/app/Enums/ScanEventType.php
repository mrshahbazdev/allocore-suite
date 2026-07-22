<?php

namespace Modules\DentalTrack\Enums;

enum ScanEventType: string
{
    case Start = 'start';
    case Complete = 'complete';
    case Pause = 'pause';
    case TransferToWaiting = 'transfer_to_waiting';
}
