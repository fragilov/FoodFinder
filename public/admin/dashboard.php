<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_admin();

$pdo = db();

// Thống kê số liệu KPI
$totalRestaurants = (int) $pdo->query('SELECT COUNT(*) FROM restaurants')->fetchColumn();
$activeRestaurants = (int) $pdo->query('SELECT COUNT(*) FROM restaurants WHERE is_active = 1')->fetchColumn();
$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$avgRating = (float) ($pdo->query('SELECT AVG(rating) FROM restaurants WHERE is_active = 1')->fetchColumn() ?: 0.0);
$totalReviews = (int) ($pdo->query('SELECT SUM(rating_count) FROM restaurants')->fetchColumn() ?: 0);

// Thống kê phân bố theo danh mục
$catDistribution = $pdo->query('
    SELECT c.id, c.name, c.slug, c.icon, COUNT(r.id) AS resto_count
    FROM categories c
    LEFT JOIN restaurants r ON r.category_id = c.id
    GROUP BY c.id
    ORDER BY resto_count DESC, c.sort_order ASC
')->fetchAll();

// Top 5 quán đánh giá cao nhất
$topRated = $pdo->query('
    SELECT r.id, r.name, r.address, r.rating, r.rating_count, r.image_url, c.name AS category_name
    FROM restaurants r
    JOIN categories c ON c.id = r.category_id
    WHERE r.is_active = 1
    ORDER BY r.rating DESC, r.rating_count DESC
    LIMIT 5
')->fetchAll();

// 5 quán mới cập nhật gần đây
$recentlyUpdated = $pdo->query('
    SELECT r.id, r.name, r.address, r.updated_at, r.is_active, c.name AS category_name
    FROM restaurants r
    JOIN categories c ON c.id = r.category_id
    ORDER BY r.updated_at DESC
    LIMIT 5
')->fetchAll();

admin_header('Bảng điều khiển');
?>

<div class="admin-heading">
  <div>
    <h1>Bảng điều khiển tổng quan</h1>
    <p class="admin-note">Số liệu thống kê và hoạt động của hệ thống Food Finder</p>
  </div>
  <div style="display: flex; gap: var(--sp-2);">
    <a href="restaurant_form.php" class="btn btn--primary">+ Thêm quán mới</a>
    <a href="categories.php" class="btn btn--ghost">Quản lý danh mục</a>
  </div>
</div>

<!-- KPI Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
  
  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem; display: flex; flex-direction: column;">
    <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--c-ink-soft, #65706a); font-weight: 700; letter-spacing: .05em;">Tổng số quán ăn</span>
    <div style="font-size: 2.2rem; font-weight: 800; color: var(--c-primary, #d22b1f); margin: 0.25rem 0;">
      <?= number_format($totalRestaurants) ?>
    </div>
    <small style="color: var(--c-ink-soft, #65706a);"><?= $activeRestaurants ?> quán đang hoạt động</small>
  </div>

  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem; display: flex; flex-direction: column;">
    <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--c-ink-soft, #65706a); font-weight: 700; letter-spacing: .05em;">Danh mục ẩm thực</span>
    <div style="font-size: 2.2rem; font-weight: 800; color: var(--c-ink, #18231e); margin: 0.25rem 0;">
      <?= number_format($totalCategories) ?>
    </div>
    <small style="color: var(--c-ink-soft, #65706a);">Cơm, Bún, Phở, Lẩu, Chay...</small>
  </div>

  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem; display: flex; flex-direction: column;">
    <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--c-ink-soft, #65706a); font-weight: 700; letter-spacing: .05em;">Điểm sao trung bình</span>
    <div style="font-size: 2.2rem; font-weight: 800; color: var(--c-accent, #f2b01b); margin: 0.25rem 0; display: flex; align-items: center; gap: 6px;">
      <?= admin_svg_icon('star') ?> <?= number_format($avgRating, 1) ?>
    </div>
    <small style="color: var(--c-ink-soft, #65706a);">Trên thang điểm 5.0</small>
  </div>

  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem; display: flex; flex-direction: column;">
    <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--c-ink-soft, #65706a); font-weight: 700; letter-spacing: .05em;">Lượt khách đánh giá</span>
    <div style="font-size: 2.2rem; font-weight: 800; color: var(--c-open, #14705a); margin: 0.25rem 0;">
      <?= number_format($totalReviews) ?>
    </div>
    <small style="color: var(--c-ink-soft, #65706a);">Tổng phản hồi thu thập</small>
  </div>

</div>

<!-- 2-Column Section: Distribution & Top Rated -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">

  <!-- Phân bố theo danh mục -->
  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem;">
    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
      <span>Phân bố quán theo danh mục</span>
      <a href="categories.php" style="font-size: 0.8rem; color: var(--c-primary, #d22b1f); text-decoration: none;">Chi tiết &rarr;</a>
    </h3>

    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
      <?php foreach ($catDistribution as $cat): 
        $pct = $totalRestaurants > 0 ? round(((int)$cat['resto_count'] / $totalRestaurants) * 100) : 0;
      ?>
        <div>
          <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 3px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
              <?= admin_category_icon($cat['slug'] ?? $cat['icon']) ?> <?= h($cat['name']) ?>
            </span>
            <span style="color: var(--c-ink-soft, #65706a);"><?= (int)$cat['resto_count'] ?> quán (<?= $pct ?>%)</span>
          </div>
          <div style="height: 8px; background: var(--c-bg, #f7f5ef); border-radius: 999px; overflow: hidden;">
            <div style="height: 100%; width: <?= $pct ?>%; background: var(--c-primary, #d22b1f); border-radius: 999px;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Top 5 quán đánh giá cao -->
  <div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem;">
    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">Top quán ăn đánh giá cao</h3>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
      <?php foreach ($topRated as $i => $item): ?>
        <div style="display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.65rem; border-bottom: 1px solid var(--c-border, #eee);">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: <?= $i === 0 ? 'var(--c-accent, #ffd400)' : 'var(--c-bg, #f7f5ef)' ?>; color: #000; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= $i + 1 ?>
          </div>
          <?php if ($item['image_url']): ?>
            <img src="../<?= h($item['image_url']) ?>" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex-shrink: 0;" alt="">
          <?php endif; ?>
          <div style="flex: 1; min-width: 0;">
            <div style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              <a href="restaurant_form.php?id=<?= (int)$item['id'] ?>" style="color: inherit; text-decoration: none;"><?= h($item['name']) ?></a>
            </div>
            <small style="color: var(--c-ink-soft, #65706a);"><?= h($item['category_name']) ?> &bull; <?= h($item['address']) ?></small>
          </div>
          <div style="display: flex; align-items: center; gap: 4px; font-weight: 700; color: var(--c-ink, #18231e); font-size: 0.85rem;">
            <?= admin_svg_icon('star') ?> <?= h($item['rating']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Recent Updates Table -->
<div style="background: var(--c-surface, #fff); border: 1px solid var(--c-border, #ddd); border-radius: .75rem; padding: 1.25rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h3 style="margin: 0; font-size: 1.1rem;">Quán ăn cập nhật gần nhất</h3>
    <a href="index.php" style="font-size: 0.85rem; color: var(--c-primary, #d22b1f); text-decoration: none; font-weight: 600;">Xem tất cả quán &rarr;</a>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Tên quán</th>
          <th>Danh mục</th>
          <th>Địa chỉ</th>
          <th>Trạng thái</th>
          <th>Thời gian cập nhật</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentlyUpdated as $row): ?>
          <tr>
            <td><strong><?= h($row['name']) ?></strong></td>
            <td><?= h($row['category_name']) ?></td>
            <td><small><?= h($row['address']) ?></small></td>
            <td>
              <span class="badge" style="padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; background: <?= (int)$row['is_active'] ? '#eaf7ef' : '#fff0ed' ?>; color: <?= (int)$row['is_active'] ? '#145a32' : '#9c271e' ?>;">
                <?= (int)$row['is_active'] ? 'Hoạt động' : 'Đã ẩn' ?>
              </span>
            </td>
            <td><small><?= date('H:i d/m/Y', strtotime($row['updated_at'])) ?></small></td>
            <td>
              <a href="restaurant_form.php?id=<?= (int)$row['id'] ?>" class="btn btn--ghost btn--sm">Chỉnh sửa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer(); ?>
