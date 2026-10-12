<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit("Run this script from the command line only.\n");
require_once __DIR__ . '/../app/bootstrap.php';
function prompt(string $label): string { echo $label; return trim((string) fgets(STDIN)); }
$username = prompt('Username: ');
$displayName = prompt('Display name: ');
if ($username === '' || $displayName === '') exit("Username and display name are required.\n");
$password = prompt('Password: ');
if (strlen($password) < 12) exit("Use a password with at least 12 characters.\n");
$stmt = db()->prepare('INSERT INTO admins (username, password_hash, display_name) VALUES (:username, :hash, :display_name)');
$stmt->execute([':username' => $username, ':hash' => password_hash($password, PASSWORD_DEFAULT), ':display_name' => $displayName]);
echo "Admin created. Delete database/create_admin.php before deploying.\n";
