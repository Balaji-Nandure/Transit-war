<?php
// Entry point: redirect to login or dashboard based on session
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
