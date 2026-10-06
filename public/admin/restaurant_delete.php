<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; } csrf_check($_POST['csrf'] ?? null);
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]); $active = filter_var($_POST['active'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>1]]);
if (!$id || $active === false) { admin_flash('Dữ liệu thao tác không hợp lệ.', 'error'); header('Location: index.php'); exit; }
$stmt = db()->prepare('UPDATE restaurants SET is_active = :active WHERE id = :id'); $stmt->execute([':active'=>$active, ':id'=>$id]); admin_flash($active ? 'Đã hiện lại quán ăn.' : 'Đã ẩn quán ăn khỏi trang công khai.'); header('Location: index.php');

