<?php

class CraftgateUtil
{

    public static function format_price($number): float
    {
        return round($number, 2);
    }

}