<?php

namespace App\Enums;

enum StockStatusEnum: string
{
    case Active = 'Active';
    case Expired = 'Expired';
    case Damaged = 'Damaged';
}
