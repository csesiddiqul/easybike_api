<?php

namespace App\Enums;

enum BloodGroupEnum: string
{
    case A_Positive = 'A+';
    case A_Negative = 'A-';
    case B_Positive = 'B+';
    case B_Negative = 'B-';
    case AB_Positive = 'AB+';
    case AB_Negative = 'AB-';
    case O_Positive = 'O+';
    case O_Negative = 'O-';
}
