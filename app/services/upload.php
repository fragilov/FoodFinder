<?php
declare(strict_types=1);

function store_restaurant_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('Không thể tải ảnh lên.');
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) throw new InvalidArgumentException('Ảnh không được vượt quá 3 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) throw new InvalidArgumentException('Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.');
    $size = @getimagesize($file['tmp_name']);
    if (!$size || $size[0] > 4000 || $size[1] > 4000) throw new InvalidArgumentException('Kích thước ảnh không hợp lệ.');
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $publicDir = defined('PUBLIC_ROOT') ? PUBLIC_ROOT : dirname(__DIR__, 2) . '/public';
    $dir = $publicDir . '/uploads/restaurants';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('Không tạo được thư mục ảnh.');
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) throw new RuntimeException('Không lưu được ảnh.');
    return 'uploads/restaurants/' . $filename;
}
