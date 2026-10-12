<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

function h(?string $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function admin_flash(?string $message = null, string $type = 'success'): ?array {
    start_secure_session();
    if ($message !== null) { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; return null; }
    $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $flash;
}
function admin_svg_icon(string $name, string $class = 'ui-icon'): string {
    $paths = [
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="var(--c-accent, #ffd400)"/>',
        'rice' => '<path d="M4 16h16M6 16a6 6 0 0 1 12 0M5 20h14"/><path d="M8 11c0-2 1-4 2-4s2 2 2 4M13 11c0-2 1-4 2-4s2 2 2 4"/>',
        'noodles' => '<path d="M4 15h16l-2 5H6l-2-5Z"/><path d="M7 11c0-2 2-2 2-4s2-2 2-4M12 11c0-2 2-2 2-4s2-2 2-4"/>',
        'bread' => '<path d="M4 17c0-4 1-8 5-8 3 0 4 2 5 2 2 0 3 2 3 4v2H4Z"/><path d="M8 12c1 1 2 1 3 0M13 13c1 1 2 1 3 0"/>',
        'grill' => '<path d="M4 9h16M6 9l2 10M18 9l-2 10M5 19h14M8 5h8"/><path d="M9 5c0-1.5 1-2 1-3M13 5c0-1.5 1-2 1-3"/>',
        'snack' => '<path d="M2 22l20-20"/><circle cx="8.5" cy="15.5" r="3"/><circle cx="12" cy="12" r="3"/><circle cx="15.5" cy="8.5" r="3"/><path d="M7 14l3 3M10.5 10.5l3 3M14 7l3 3"/>',
        'leaf' => '<path d="M20 4C10 4 5 8 5 15c0 3 2 5 5 5 7 0 10-7 10-16Z"/><path d="M4 21c4-5 8-8 13-11"/>',
        'coffee' => '<path d="M4 8h12v7a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8Z"/><path d="M16 10h2a2 2 0 0 1 0 4h-2M6 4c0 1 1 1 1 2M10 4c0 1 1 1 1 2"/>',
        'plate' => '<path d="M4 15h16M6 15a6 6 0 0 1 12 0M3 19h18"/><path d="M12 7V4M10 5h4"/>',
        'logo' => '<circle cx="16" cy="16" r="14" fill="var(--c-primary, #ff4d2e)"/><path d="M10 20c0-4 2-8 6-8s6 4 6 8" stroke="#fff" stroke-width="2" stroke-linecap="round"/><circle cx="16" cy="11" r="2" fill="#fff"/><path d="M9 22h14" stroke="#fff" stroke-width="2" stroke-linecap="round"/>',
    ];
    $svg = $paths[$name] ?? $paths['plate'];
    $viewBox = $name === 'logo' ? '0 0 32 32' : '0 0 24 24';
    $fill = $name === 'star' ? 'currentColor' : 'none';
    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" viewBox="' . $viewBox . '" fill="' . $fill . '" stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" style="width:1.25em;height:1.25em;vertical-align:-0.22em;display:inline-block;" aria-hidden="true">' . $svg . '</svg>';
}
function admin_category_icon(string $slugOrIcon): string {
    $alias = [
        'com' => 'rice', 'rice' => 'rice', '🍚' => 'rice',
        'bun-pho' => 'noodles', 'noodles' => 'noodles', '🍜' => 'noodles',
        'banh-mi' => 'bread', 'bread' => 'bread', '🥖' => 'bread',
        'lau-nuong' => 'grill', 'grill' => 'grill', '🔥' => 'grill',
        'an-vat' => 'snack', 'snack' => 'snack', '🍡' => 'snack',
        'chay' => 'leaf', 'leaf' => 'leaf', '🥬' => 'leaf',
        'ca-phe' => 'coffee', 'coffee' => 'coffee', '☕' => 'coffee',
    ];
    $key = $alias[trim($slugOrIcon)] ?? 'plate';
    return admin_svg_icon($key, 'ui-icon category-glyph');
}
function admin_header(string $title): void { $flash = admin_flash(); ?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= h($title) ?> — Food Finder</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/tokens.css"><link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css"><link rel="stylesheet" href="assets/admin.css"></head><body>
<header class="admin-header"><a href="dashboard.php" class="admin-brand"><?= admin_svg_icon('logo') ?> Food Finder <small>Quản trị</small></a><nav><a href="dashboard.php">Tổng quan</a><a href="index.php">Quán ăn</a><a href="categories.php">Danh mục</a><a href="reviews.php">Đánh giá</a><a href="restaurant_form.php">+ Thêm quán</a><a href="../index.html" target="_blank" rel="noopener">Xem bản đồ</a><form action="logout.php" method="post" class="inline"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><button class="link-button">Đăng xuất</button></form></nav></header><main class="admin-container">
<?php if ($flash): ?><div class="admin-flash admin-flash--<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
<?php }
function admin_footer(): void { ?></main></body></html><?php }

