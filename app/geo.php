<?php
declare(strict_types=1);

const EARTH_R_KM = 6371.0088;
const KM_PER_DEG_LAT = 111.045;

function bounding_box(float $lat, float $lng, float $radiusKm): array
{
    $margin = $radiusKm * 1.02;
    $dLat = $margin / KM_PER_DEG_LAT;
    $cos = max(cos(deg2rad($lat)), 0.00001);
    $dLng = $margin / (KM_PER_DEG_LAT * $cos);
    return ['lat_min' => $lat - $dLat, 'lat_max' => $lat + $dLat, 'lng_min' => $lng - $dLng, 'lng_max' => $lng + $dLng];
}

function is_open_now(array $row): bool
{
    if ((int) $row['is_24h'] === 1) return true;
    if (empty($row['open_time']) || empty($row['close_time'])) return false;
    $now = date('H:i:s');
    return $row['close_time'] > $row['open_time']
        ? $now >= $row['open_time'] && $now < $row['close_time']
        : $now >= $row['open_time'] || $now < $row['close_time'];
}

