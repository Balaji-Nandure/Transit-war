<?php
require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';

class AuthController {
    public function login() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('login.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            if (!SecurityService::validateCSRFToken($csrf)) {
                $error = 'Invalid CSRF token.';
                include $_SERVER['DOCUMENT_ROOT'] . '/login_form.php';
                return;
            }
            if (!SecurityService::validateEmail($email) || empty($password)) {
                $error = 'Invalid credentials.';
                include $_SERVER['DOCUMENT_ROOT'] . '/login_form.php';
                return;
            }
            $pdo = require __DIR__ . '/../../config/db.php';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            // Rate limiting check
            if (SecurityService::isRateLimited($pdo, $email, $ip)) {
                $error = 'Too many failed attempts. Please try again in 15 minutes.';
                include $_SERVER['DOCUMENT_ROOT'] . '/login_form.php';
                return;
            }

            $stmt = $pdo->prepare('SELECT user_id, username, password_hash FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && SecurityService::verifyPassword($password, $user['password_hash'])) {
                SecurityService::clearFailedAttempts($pdo, $email, $ip);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                SecurityService::recordFailedAttempt($pdo, $email, $ip);
                $error = 'Invalid credentials.';
            }
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/login_form.php';
    }
    public function register() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('register.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            if (!SecurityService::validateCSRFToken($csrf)) {
                $error = 'Invalid CSRF token.';
                include $_SERVER['DOCUMENT_ROOT'] . '/register_form.php';
                return;
            }
            if (!SecurityService::validateUsername($username) || !SecurityService::validateEmail($email) || !SecurityService::validatePassword($password)) {
                $error = 'Invalid input.';
                include $_SERVER['DOCUMENT_ROOT'] . '/register_form.php';
                return;
            }
            $pdo = require __DIR__ . '/../../config/db.php';
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
                include $_SERVER['DOCUMENT_ROOT'] . '/register_form.php';
                return;
            }
            $hash = SecurityService::hashPassword($password);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit();
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/register_form.php';
    }
    public function logout() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('logout.php');
        SecurityService::destroySession();
        header('Location: login.php');
        exit();
    }
}
