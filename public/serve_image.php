<?php
// serve profile images stored outside the web root
require_once __DIR__ . '/../app/services/SecurityService.php';
SecurityService::secureSessionStart();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Forbidden');
}

$file = $_GET['file'] ?? '';

// Validate filename: only allow alphanumeric, hyphens, underscores, and a single dot for extension
if (!preg_match('/^[a-f0-9]+\.(jpg|jpeg|png|gif)$/i', $file)) {
    http_response_code(400);
    exit('Invalid file');
}

$uploadDir = realpath(__DIR__ . '/../storage/uploads');
$filePath = $uploadDir . DIRECTORY_SEPARATOR . $file;

// Prevent path traversal
if (strpos(realpath($filePath), $uploadDir) !== 0 || !is_file($filePath)) {
    http_response_code(404);
    exit('Not found');
}

$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
readfile($filePath);
exit();
