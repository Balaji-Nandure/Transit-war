<?php
// SecurityService: Centralized security and validation functions
class SecurityService {
    private const IMAGE_ALLOWED_MIME_TO_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        //'image/gif' => 'gif',
    ];
    private const IMAGE_MIN_SIZE_BYTES = 200 * 1024; // 200KB
    private const IMAGE_MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2MB

    // Input validation
    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username);
    }
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public static function validatePassword($password) {
        return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,64}$/', $password);
    }
    public static function validateAmount($amount) {
        // minimum difference 0.01
        return is_numeric($amount) && $amount > 0 && $amount < 1000000  && floor($amount * 100) == $amount * 100;
    }
    public static function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    // CSRF token generation/validation
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function validateCSRFToken($token) {
        $valid = isset($_SESSION['csrf_token']) &&
                hash_equals($_SESSION['csrf_token'], $token);

        if ($valid) {
            unset($_SESSION['csrf_token']); // forced tthe regeneration
        }

        return $valid;
    }
    // Session security
    public static function secureSessionStart() {
        // Security headers
	date_default_timezone_set('Asia/Kolkata');
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: no-referrer");
        header("Content-Security-Policy: default-src 'self' https://stackpath.bootstrapcdn.com; img-src 'self' data:; style-src 'self' https://stackpath.bootstrapcdn.com; script-src 'self' https://stackpath.bootstrapcdn.com;");

        // session directory exists and set session save path
        if (!is_dir('/var/www/sessions')) {
            mkdir('/var/www/sessions', 0777, true);
        }
        ini_set('session.save_path', '/var/www/sessions');
        ini_set('session.use_strict_mode', '1'); // Reject uninitialized session IDs to prevent session fixation
        $cookieParams = session_get_cookie_params();
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookieParams['path'],
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    public static function destroySession() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
    // Password hashing/verification
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    // File upload security
    public static function validateImageUpload($file, &$errorMessage = null) {
        if (!isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])) {
            $errorMessage = 'Invalid upload payload.';
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'File upload failed.';
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $errorMessage = 'Invalid upload source.';
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        if (!in_array($ext, ['jpg', 'png'], true)) {
            $errorMessage = 'Only JPG, and PNG files are allowed.';
            return false;
        }

        if ($file['size'] < self::IMAGE_MIN_SIZE_BYTES) {
            $errorMessage = 'Image must be at least 200KB.';
            return false;
        }

        if ($file['size'] > self::IMAGE_MAX_SIZE_BYTES) {
            $errorMessage = 'Image must be at most 2MB.';
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset(self::IMAGE_ALLOWED_MIME_TO_EXT[$mime])) {
            $errorMessage = 'Unsupported image format.';
            return false;
        }

        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];

        // Use EXIF if available; otherwise fall back to getimagesize() type detection.
        if (function_exists('exif_imagetype')) {
            $actualImageType = @exif_imagetype($file['tmp_name']);
            if (!in_array($actualImageType, $allowedImageTypes, true)) {
                $errorMessage = 'Uploaded file is not a valid image.';
                return false;
            }
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errorMessage = 'Uploaded file is not a valid image.';
            return false;
        }

        $detectedImageType = $imageInfo[2] ?? null;
        if (!in_array($detectedImageType, $allowedImageTypes, true)) {
            $errorMessage = 'Uploaded file is not a valid image.';
            return false;
        }

        $expectedExt = self::IMAGE_ALLOWED_MIME_TO_EXT[$mime];
        if ($expectedExt !== $ext) {
            $errorMessage = 'File extension does not match image content.';
            return false;
        }

        return true;
    }
    public static function randomFileName($ext) {
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    // Rate limiting: check if IP/email is locked out
    public static function isRateLimited($pdo, $email, $ip) {
        $windowMinutes = 15;
        $maxAttempts = 5;
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = ? OR ip_address = ?)
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$email, $ip, $windowMinutes]);
        return (int)$stmt->fetchColumn() >= $maxAttempts;
    }

    // Rate limiting: failed login attempt
    public static function recordFailedAttempt($pdo, $email, $ip) {
        $stmt = $pdo->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
        $stmt->execute([$email, $ip]);
    }

    // Rate limiting: successful login
    public static function clearFailedAttempts($pdo, $email, $ip) {
        $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ? OR ip_address = ?');
        $stmt->execute([$email, $ip]);
    }
}
