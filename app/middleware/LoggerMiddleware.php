<?php
// LoggerMiddleware: Logs every request with page, username, timestamp, and IP
class LoggerMiddleware {
    public static function log($page) {
        date_default_timezone_set('Asia/Kolkata');
	$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');

        // Sanitize for file logging (prevent log injection)
        $safePage = preg_replace('/[^a-zA-Z0-9_.\/\-]/', '', $page);
        $safeUser = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        $safeIp = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'invalid';

        // Write to flat file log
        $logLine = "$safePage | Username: $safeUser | [$timestamp] IP: $safeIp\n";
        $logFile = __DIR__ . '/../../storage/logs/user_activity.log';
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Write to database logs table
        try {
            $pdo = require __DIR__ . '/../../config/db.php';
            $stmt = $pdo->prepare('INSERT INTO logs (username, page, ip_address) VALUES (?, ?, ?)');
            $stmt->execute([$safeUser, $safePage, $safeIp]);
        } catch (Exception $e) {
            
        }
    }
}
