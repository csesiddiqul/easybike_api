<?php

namespace App\Enums;

enum DistributionTypes: string
{
    case Parents = 'Parents';
    case Children = 'Children';
    case Spouse = 'Spouse';
    case Self = 'Self';
    case Other = 'Other';
}
