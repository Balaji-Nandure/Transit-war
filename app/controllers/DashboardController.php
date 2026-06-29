<?php
require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
class DashboardController {
    public function show() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('dashboard.php');
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $pdo = require __DIR__ . '/../../config/db.php';
        $user_id = $_SESSION['user_id'];

        // Fetch current balance
        $stmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $balance = $stmt->fetchColumn();

        // Fetch recent transactions (last 10)
        $stmt = $pdo->prepare(
            'SELECT t.transaction_id, t.sender_id, t.receiver_id, t.amount, t.comment, t.created_at,
                    s.username AS sender_name, r.username AS receiver_name
             FROM transactions t
             JOIN users s ON t.sender_id = s.user_id
             JOIN users r ON t.receiver_id = r.user_id
             WHERE t.sender_id = ? OR t.receiver_id = ?
             ORDER BY t.created_at DESC LIMIT 10'
        );
        $stmt->execute([$user_id, $user_id]);
        $transactions = $stmt->fetchAll();

        include $_SERVER['DOCUMENT_ROOT'] . '/dashboard_view.php';
    }
}
