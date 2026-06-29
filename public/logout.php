<?php
/*
 * public/logout.php
 *
 * Purpose: Public endpoint to terminate the authenticated session. It delegates
 * to the AuthController to ensure session destruction follows the same
 * centralized logic and logging.
 */

require_once __DIR__ . '/../app/controllers/AuthController.php';
(new AuthController())->logout();
