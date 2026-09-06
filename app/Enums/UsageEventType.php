<?php

namespace App\Enums;

enum UsageEventType: string
{
    case Message = 'message';
    case Reservation = 'reservation';
    case ReservationReleased = 'reservation_released';
    case Tokens = 'tokens';
}
