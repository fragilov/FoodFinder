<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/validate.php';
require_once __DIR__ . '/../../app/restaurant_query.php';

api_guard(function (): void {
    header('Cache-Control: no-store');
    $filters = restaurant_filters($_GET);
    $rows = restaurant_rows(db(), $filters);
    json_ok($rows, ['count' => count($rows), 'radius_km' => $filters['radius'], 'limit' => $filters['limit'], 'offset' => $filters['offset']]);
});

