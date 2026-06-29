<?php
/*
 * SearchController.php
 *
 * Purpose: Provide a safe user search endpoint. It limits results and
 * uses prepared statements to avoid SQL injection while keeping the UI
 * responsive by capping results.
 */

require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
class SearchController {
    public function search() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('search.php');

        // Require login to access user directory/search features.
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $pdo = require __DIR__ . '/../../config/db.php';
        $results = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $query = trim($_POST['query'] ?? '');
            if ($query !== '') {
                // Use prepared statements with parameter binding to avoid SQL injection.
                // Limit results to avoid expensive queries and to keep the UI snappy.
                $stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE username LIKE ? OR user_id = ? LIMIT 20');
                $stmt->execute(['%' . $query . '%', $query]);
                $results = $stmt->fetchAll();
            }
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/search_form.php';
    }
}
