<?php
/*
 * public/index.php
 *
 * Purpose: Lightweight entry point that routes users depending on authentication state.
 * Why: Keeps the base URL simple and ensures unauthenticated users land on login.
 */

// Start session to read auth state. Use minimal session start here since
// secure session settings are applied in controllers for authenticated pages.
session_start();

// If a user_id is present in session, consider the user authenticated and
// send them to the dashboard. Otherwise redirect to login.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
