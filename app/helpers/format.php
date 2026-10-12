<?php
declare(strict_types=1);

function format_currency(int|float $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' ₫';
}

function format_distance(float $km): string
{
    if ($km < 1.0) {
        return round($km * 1000) . ' m';
    }
    return round($km, 1) . ' km';
}
