<?php

namespace App\Data\Promotions;

enum PromotionUnit: string
{
    case Percentage = "percentage";
    case Money = "money";
}
