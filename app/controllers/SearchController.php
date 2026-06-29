<?php
require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
class SearchController {
    public function search() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('search.php');
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $pdo = require __DIR__ . '/../../config/db.php';
        $results = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $query = trim($_POST['query'] ?? '');
            if ($query !== '') {
                $stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE username LIKE ? OR user_id = ? LIMIT 20');
                $stmt->execute(['%' . $query . '%', $query]);
                $results = $stmt->fetchAll();
            }
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/search_form.php';
    }
}
