<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

api_guard(function (): void {
    start_secure_session(); $q = optional_text($_GET, 'q', 100);
    if (mb_strlen($q) < 3) json_error('INVALID_PARAM', 'Hãy nhập ít nhất 3 ký tự', 400, 'q');
    $now = microtime(true); $last = (float) ($_SESSION['geocode_at'] ?? 0);
    if ($now - $last < 1) json_error('RATE_LIMITED', 'Vui lòng chờ một giây trước khi tìm tiếp.', 429);
    $_SESSION['geocode_at'] = $now;
    $key = 'food-finder-geocode-' . hash('sha256', mb_strtolower($q));
    $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key . '.json';
    if (is_file($file) && filemtime($file) > time() - 86400) $data = file_get_contents($file);
    else {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query(['q' => $q, 'format' => 'jsonv2', 'limit' => 5, 'countrycodes' => 'vn']);
        $options = ['http' => ['header' => "User-Agent: FoodFinder/1.0\r\nAccept: application/json\r\n", 'timeout' => 8]];
        $data = @file_get_contents($url, false, stream_context_create($options));
        if ($data === false) json_error('SERVER_ERROR', 'Không thể tìm địa chỉ lúc này.', 503);
        @file_put_contents($file, $data, LOCK_EX);
    }
    $decoded = json_decode($data, true); if (!is_array($decoded)) json_error('SERVER_ERROR', 'Dữ liệu địa chỉ không hợp lệ.', 502);
    $result = array_map(static fn(array $item): array => ['name' => (string) ($item['display_name'] ?? ''), 'lat' => (float) $item['lat'], 'lng' => (float) $item['lon']], $decoded);
    json_ok($result);
});
