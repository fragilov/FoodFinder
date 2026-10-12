<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/services/restaurant_query.php';

api_guard(function (): void {
    start_secure_session();
    $now = time();
    $_SESSION['random_requests'] = array_values(array_filter($_SESSION['random_requests'] ?? [], static fn(int $time): bool => $time > $now - 60));
    if (count($_SESSION['random_requests']) >= 30) json_error('RATE_LIMITED', 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.', 429);
    $_SESSION['random_requests'][] = $now;
    $filters = restaurant_filters($_GET); $filters['limit'] = 100; $filters['offset'] = 0;
    $rows = restaurant_rows(db(), $filters, false);
    $exclude = array_slice(array_filter(array_map('intval', explode(',', (string) ($_GET['exclude'] ?? ''))), static fn(int $id): bool => $id > 0), 0, 10);
    $pool = array_values(array_filter($rows, static fn(array $row): bool => !in_array($row['id'], $exclude, true)));
    if (!$pool) $pool = $rows;
    if (!$pool) json_ok(null, ['reason' => 'no_candidates']);
    $weights = array_map(static fn(array $row): float => (0.30 + $row['rating'] / 5) * exp(-$row['distance_km'] / 1.5), $pool);
    $target = (random_int(0, PHP_INT_MAX) / PHP_INT_MAX) * array_sum($weights);
    foreach ($pool as $index => $row) { $target -= $weights[$index]; if ($target <= 0) json_ok($row); }
    json_ok(end($pool));
});
