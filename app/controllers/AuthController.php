<?php
/*
 * AuthController.php
 *
 * Purpose: Handle authentication-related user actions: login, register, logout.
 * Why: Centralizing auth logic here keeps the public-facing scripts thin
 * (login.php, register.php) and makes it easier to apply consistent
 * security controls (CSRF checks, rate limiting, session hardening).
 */

require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';

class AuthController {
    public function login() {
        // Ensure secure session settings and headers are applied before any output
        // This sets cookie flags, content security headers, and starts the session.
        SecurityService::secureSessionStart();

        // Record the page access for auditing and debugging.
        LoggerMiddleware::log('login.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            // Validate the CSRF token to avoid cross-site request forgery attacks.
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
            // Verify password using a secure constant-time comparison.
            if ($user && SecurityService::verifyPassword($password, $user['password_hash'])) {
                // On successful auth: clear rate-limiting records, regenerate session id
                // to prevent session fixation, and set minimal session state.
                SecurityService::clearFailedAttempts($pdo, $email, $ip);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                // Record failed login attempts for rate-limiting and monitoring.
                SecurityService::recordFailedAttempt($pdo, $email, $ip);
                $error = 'Invalid credentials.';
            }
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/login_form.php';
    }
    public function register() {
        // Same session and logging protections apply for registration flows.
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('register.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            // Protect the registration endpoint using CSRF tokens.
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
            // Hash passwords using PHP's password API. This abstracts algorithm details
            // and ensures future-proof hashing (bcrypt/argon2 depending on PHP build).
            $hash = SecurityService::hashPassword($password);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            // Set minimal session state and regenerate id after creating the account.
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
        // Destroy the session completely to remove any authentication traces.
        SecurityService::destroySession();
        header('Location: login.php');
        exit();
    }
}
