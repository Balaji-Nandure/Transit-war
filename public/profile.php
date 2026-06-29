<?php
/*
 * public/profile.php
 *
 * Purpose: Public endpoint for editing the authenticated user's profile.
 * Delegates to `ProfileController::profile()` which enforces auth, handles
 * CSRF validation, file uploads, and saving changes.
 */

require_once __DIR__ . '/../app/controllers/ProfileController.php';
(new ProfileController())->profile();
