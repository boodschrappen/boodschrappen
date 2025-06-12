<?php

namespace App\Data\Discounts;

enum DiscountUnit: string
{
    case Percentage = "percentage";
    case Money = "money";
}
