<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = dirname(__DIR__) . '/config.php';
    if (!is_file($config)) {
        $exampleConfig = dirname(__DIR__) . '/config.example.php';
        if (is_file($exampleConfig)) {
            require_once $exampleConfig;
        } else {
            throw new RuntimeException('Thiếu app/config.php. Hãy sao chép config.example.php và điền cấu hình CSDL.');
        }
    } else {
        require_once $config;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '+07:00'");
    return $pdo;
}
