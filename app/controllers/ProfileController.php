<?php
require_once __DIR__ . '/../services/SecurityService.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
class ProfileController {
    public function profile() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('profile.php');
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $pdo = require __DIR__ . '/../../config/db.php';
        $user_id = $_SESSION['user_id'];
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bio = $_POST['bio'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';
            if (!SecurityService::validateCSRFToken($csrf)) {
                $error = 'Invalid CSRF token.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET bio = ? WHERE user_id = ?');
                $stmt->execute([trim($bio), $user_id]);
                $success = 'Profile updated.';
            }
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadValidationError = '';
                if (SecurityService::validateImageUpload($_FILES['profile_image'], $uploadValidationError)) {
                    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                    if ($ext === 'jpeg') {
                        $ext = 'jpg';
                    }
                    $filename = SecurityService::randomFileName($ext);
                    $uploadPath = __DIR__ . '/../../storage/uploads/' . $filename;
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                        $stmt = $pdo->prepare('UPDATE users SET profile_image = ? WHERE user_id = ?');
                        $stmt->execute([$filename, $user_id]);
                        $success = 'Profile image updated.';
                    } else {
                        $error = 'Image upload failed.';
                    }
                } else {
                    $error = $uploadValidationError ?: 'Invalid profile image.';
                }
            }
        }
        $stmt = $pdo->prepare('SELECT username, email, bio, profile_image FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        include $_SERVER['DOCUMENT_ROOT'] . '/profile_view.php';
    }

    public function viewOther() {
        SecurityService::secureSessionStart();
        LoggerMiddleware::log('view_profile.php');

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $target_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        // If viewing own profile, redirect to the editable profile page
        if ($target_id === (int)$_SESSION['user_id']) {
            header('Location: profile.php');
            exit();
        }

        $pdo = require __DIR__ . '/../../config/db.php';
        $stmt = $pdo->prepare('SELECT user_id, username, bio, profile_image, created_at FROM users WHERE user_id = ?');
        $stmt->execute([$target_id]);
        $profile = $stmt->fetch();

        if (!$profile) {
            $error = 'User not found.';
        }

        include $_SERVER['DOCUMENT_ROOT'] . '/view_profile_view.php';
    }
}
