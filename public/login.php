<?php
/*
 * public/login.php
 *
 * Purpose: Public entry point for user login. Delegates to the AuthController
 * which handles input validation, CSRF checks, rate-limiting, and view rendering.
 */

// Load the authentication controller from the application layer.
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Run the login action.
(new AuthController())->login();

