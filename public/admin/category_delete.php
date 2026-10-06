<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

csrf_check();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

if (!$id) {
    admin_flash('Yêu cầu không hợp lệ.', 'error');
    header('Location: categories.php');
    exit;
}

$pdo = db();

// Kiểm tra xem danh mục có quán không
$check = $pdo->prepare('SELECT COUNT(*) FROM restaurants WHERE category_id = ?');
$check->execute([$id]);
$count = (int) $check->fetchColumn();

if ($count > 0) {
    admin_flash("Không thể xóa danh mục này vì đang có {$count} quán ăn liên kết. Vui lòng chuyển các quán sang danh mục khác trước.", 'error');
    header('Location: categories.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    admin_flash('Đã xóa danh mục thành công!');
} catch (PDOException $e) {
    admin_flash('Lỗi khi xóa danh mục: ' . $e->getMessage(), 'error');
}

header('Location: categories.php');
exit;
