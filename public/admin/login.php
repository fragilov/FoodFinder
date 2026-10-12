<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
start_secure_session();
if (!empty($_SESSION['admin'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $username = trim((string) ($_POST['username'] ?? '')); $password = (string) ($_POST['password'] ?? '');
    if (!attempt_login($username, $password)) $error = 'Thông tin đăng nhập không đúng hoặc bạn cần thử lại sau ít phút.';
    else { header('Location: index.php'); exit; }
}
?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Đăng nhập quản trị — Food Finder</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/css/tokens.css"><link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/components.css"><link rel="stylesheet" href="assets/admin.css"></head><body><main class="admin-container" style="max-width:440px;margin-top:8vh"><h1>Đăng nhập quản trị</h1><p class="admin-note">Dành cho người quản lý Food Finder.</p><?php if ($error): ?><div class="admin-flash admin-flash--error"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?><form method="post" class="admin-form" style="grid-template-columns:1fr"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>"><label>Tên đăng nhập<input required autocomplete="username" class="admin-input" name="username"></label><label>Mật khẩu<input required autocomplete="current-password" class="admin-input" type="password" name="password"></label><button class="btn btn--primary" type="submit">Đăng nhập</button></form></main></body></html>
