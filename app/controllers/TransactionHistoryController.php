<?php
/*
 * TransactionHistoryController.php
 *
 * Purpose: Return a user's full transaction history and balance.
 * Why: This controller separates reporting logic from transfer logic,
 * allowing optimized read queries while the transfer controller handles
 * transactional consistency and locks.
 */

require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';

class TransactionHistoryController {
    public function show() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('transaction_history.php');

        // Require authentication for private transaction data.
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $pdo = require __DIR__ . '/../../config/db.php';
        $user_id = $_SESSION['user_id'];

        // Fetch balance for quick display. Reads are separate from write locks used
        // in the transfer controller to avoid long-held locks during reporting.
        $stmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $balance = $stmt->fetchColumn();

        // Fetch all transactions for this user. Joins are used to show usernames
        // for sender and receiver; the controller returns raw rows for the view
        // which must escape content before rendering.
        $stmt = $pdo->prepare(
            'SELECT t.transaction_id, t.sender_id, t.receiver_id, t.amount, t.comment, t.created_at,
                    s.username AS sender_name, r.username AS receiver_name
             FROM transactions t
             JOIN users s ON t.sender_id = s.user_id
             JOIN users r ON t.receiver_id = r.user_id
             WHERE t.sender_id = ? OR t.receiver_id = ?
             ORDER BY t.created_at DESC'
        );
        $stmt->execute([$user_id, $user_id]);
        $transactions = $stmt->fetchAll();

        include $_SERVER['DOCUMENT_ROOT'] . '/transaction_history_view.php';
    }
}
