<?php
/*
 * public/serve_image.php
 *
 * Purpose: Serve profile images from storage/uploads while preventing direct
 * web-root access and protecting against path traversal and unvalidated file reads.
 */

// Reuse centralized security utilities and ensure secure session context.
require_once __DIR__ . '/../app/services/SecurityService.php';
SecurityService::secureSessionStart();

// Only allow authenticated users to fetch images in this implementation.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Forbidden');
}

// Requested filename (from URL). We validate before using it on disk.
$file = $_GET['file'] ?? '';

// Validate filename strictly: only allow hex filenames with a single extension.
// This prevents attackers from requesting arbitrary paths or files.
if (!preg_match('/^[a-f0-9]+\.(jpg|jpeg|png|gif)$/i', $file)) {
    http_response_code(400);
    exit('Invalid file');
}

// Resolve uploads directory and build the file path. Use realpath for canonicalization.
$uploadDir = realpath(__DIR__ . '/../storage/uploads');
$filePath = $uploadDir . DIRECTORY_SEPARATOR . $file;

// Prevent path traversal by ensuring the resolved path starts with the uploads directory
// and that the target is an actual file.
if (strpos(realpath($filePath), $uploadDir) !== 0 || !is_file($filePath)) {
    http_response_code(404);
    exit('Not found');
}

// Map extension to MIME type for proper Content-Type header.
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

// Send headers and stream the file. Cache-Control is conservative (private).
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
readfile($filePath);
exit();
