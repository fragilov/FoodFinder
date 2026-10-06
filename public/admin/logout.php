<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
csrf_check($_POST['csrf'] ?? null); $_SESSION = []; session_destroy(); header('Location: login.php');

