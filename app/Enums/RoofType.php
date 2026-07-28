<?php

namespace App\Enums;

enum RoofType: string
{
    case Rcc = 'rcc';
    case TinShed = 'tin_shed';
    case Tiled = 'tiled';
    case Other = 'other';
}
