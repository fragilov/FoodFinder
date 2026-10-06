<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_admin();

$pdo = db();

// Đảm bảo bảng reviews tồn tại
$pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  author_name VARCHAR(80) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  comment TEXT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_restaurant_rating (restaurant_id, rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

// Xử lý toggle ẩn/hiện hoặc xóa review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $reviewId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'toggle' && $reviewId) {
        $status = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT) ?: 0;
        $stmt = $pdo->prepare('UPDATE reviews SET is_approved = ? WHERE id = ?');
        $stmt->execute([$status, $reviewId]);
        admin_flash($status ? 'Đã duyệt hiển thị đánh giá.' : 'Đã ẩn đánh giá.');
    } elseif ($action === 'delete' && $reviewId) {
        $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->execute([$reviewId]);
        admin_flash('Đã xóa đánh giá.');
    }
    header('Location: reviews.php');
    exit;
}

// Nếu chưa có review nào thì chèn dữ liệu mẫu ban đầu
$count = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
if ($count === 0) {
    $firstResto = $pdo->query('SELECT id FROM restaurants LIMIT 1')->fetchColumn() ?: 1;
    $sampleReviews = [
        [$firstResto, 'Nguyễn Minh Tuấn', 5, 'Quán cơm tấm rất ngon, sườn ướp vừa vị, nước mắm kẹo đậm đà. Nhân viên phục vụ nhiệt tình!'],
        [$firstResto, 'Trần Thị Lan', 4, 'Đồ ăn vừa miệng, không gian sạch sẽ thoáng mát. Buổi trưa hơi đông khách một chút.'],
        [$firstResto, 'Lê Văn Bình', 5, 'Phở bò tái nạm nước dùng trong, ngọt thanh từ xương. Giá cả sinh viên rất hợp lý.'],
        [$firstResto, 'Phạm Hoàng Nam', 3, 'Hương vị ổn nhưng quán phục vụ hơi chậm vào giờ cao điểm.'],
    ];
    $ins = $pdo->prepare('INSERT INTO reviews (restaurant_id, author_name, rating, comment, is_approved) VALUES (?, ?, ?, ?, 1)');
    foreach ($sampleReviews as $sr) {
        $ins->execute($sr);
    }
}

// Lấy danh sách reviews
$reviews = $pdo->query('
    SELECT rv.*, r.name AS restaurant_name
    FROM reviews rv
    LEFT JOIN restaurants r ON r.id = rv.restaurant_id
    ORDER BY rv.created_at DESC
')->fetchAll();

admin_header('Quản lý đánh giá');
?>

<div class="admin-heading">
  <div>
    <h1>Quản lý đánh giá & phản hồi</h1>
    <p class="admin-note">Duyệt và kiểm soát các bình luận, phản hồi từ khách hàng</p>
  </div>
  <a href="dashboard.php" class="btn btn--ghost">&larr; Về tổng quan</a>
</div>

<div class="admin-table-wrap">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Quán ăn</th>
        <th>Người đánh giá</th>
        <th>Số sao</th>
        <th>Nội dung bình luận</th>
        <th>Thời gian</th>
        <th>Trạng thái</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reviews as $rev): ?>
        <tr>
          <td>
            <strong><?= h($rev['restaurant_name'] ?? 'Quán #' . $rev['restaurant_id']) ?></strong>
          </td>
          <td><strong><?= h($rev['author_name']) ?></strong></td>
          <td>
            <span style="display:inline-flex; align-items:center; gap:4px; font-weight:700; color:var(--c-accent, #f2b01b);">
              <?= admin_svg_icon('star') ?> <?= (int)$rev['rating'] ?>
            </span>
          </td>
          <td style="max-width: 320px;">
            <p style="margin: 0; line-height: 1.4; font-size: 0.85rem;"><?= h($rev['comment']) ?></p>
          </td>
          <td><small><?= date('H:i d/m/Y', strtotime($rev['created_at'])) ?></small></td>
          <td>
            <span class="badge" style="padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; background: <?= (int)$rev['is_approved'] ? '#eaf7ef' : '#fff0ed' ?>; color: <?= (int)$rev['is_approved'] ? '#145a32' : '#9c271e' ?>;">
              <?= (int)$rev['is_approved'] ? 'Đã duyệt' : 'Đang ẩn' ?>
            </span>
          </td>
          <td>
            <div class="actions">
              <!-- Toggle status form -->
              <form method="post" action="reviews.php" class="inline">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$rev['id'] ?>">
                <input type="hidden" name="status" value="<?= (int)$rev['is_approved'] ? 0 : 1 ?>">
                <button class="btn btn--ghost btn--sm <?= (int)$rev['is_approved'] ? 'danger' : '' ?>" type="submit">
                  <?= (int)$rev['is_approved'] ? 'Ẩn' : 'Duyệt' ?>
                </button>
              </form>

              <!-- Delete form -->
              <form method="post" action="reviews.php" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$rev['id'] ?>">
                <button class="btn btn--ghost btn--sm danger" type="submit">Xóa</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$reviews): ?>
        <tr><td colspan="7">Chưa có đánh giá nào trong hệ thống.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php admin_footer(); ?>
