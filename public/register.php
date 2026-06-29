<?php
/*
 * public/register.php
 *
 * Purpose: Public entry point for user registration. Delegates to
 * `AuthController::register()` which performs validation, hashing and
 * account creation.
 */

// Load the registration handler from app layer
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Execute the registration action which will render the form or process POST data.
(new AuthController())->register();
