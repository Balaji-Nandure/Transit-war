<?php
require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
class TransferController {
    public function transfer() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('transfer.php');
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $pdo = require __DIR__ . '/../../config/db.php';
        $user_id = $_SESSION['user_id'];
        $error = $success = '';

        // Fetch current balance for display
        $balStmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = ?');
        $balStmt->execute([$user_id]);
        $balance = $balStmt->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiver_id = $_POST['receiver_id'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $comment = $_POST['comment'] ?? '';
            $comment = substr(trim($comment), 0, 255);
            $csrf = $_POST['csrf_token'] ?? '';
            if (!SecurityService::validateCSRFToken($csrf)) {
                $error = 'Invalid CSRF token.';
            } elseif (!SecurityService::validateAmount($amount) || $receiver_id == $user_id) {
                $error = 'Invalid transfer.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = ? FOR UPDATE');
                    $stmt->execute([$user_id]);
                    $sender = $stmt->fetch();
                    $stmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = ? FOR UPDATE');
                    $stmt->execute([$receiver_id]);
                    $receiver = $stmt->fetch();
                    if (!$sender || !$receiver) {
                        throw new Exception('User not found.');
                    }
                    if ($sender['balance'] < $amount) {
                        throw new Exception('Insufficient balance.');
                    }
                    $stmt = $pdo->prepare('UPDATE users SET balance = balance - ? WHERE user_id = ?');
                    $stmt->execute([$amount, $user_id]);
                    $stmt = $pdo->prepare('UPDATE users SET balance = balance + ? WHERE user_id = ?');
                    $stmt->execute([$amount, $receiver_id]);
                    $stmt = $pdo->prepare('INSERT INTO transactions (sender_id, receiver_id, amount, comment) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$user_id, $receiver_id, $amount, trim($comment)]);
                    $pdo->commit();
                    $success = 'Transfer successful!';
                    // Refresh balance after transfer
                    $balStmt->execute([$user_id]);
                    $balance = $balStmt->fetchColumn();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
                }
            }
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/transfer_form.php';
    }
}
