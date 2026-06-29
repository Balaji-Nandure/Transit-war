<?php
/*
 * public/view_profile.php
 *
 * Purpose: Public endpoint to view other users' profiles. Delegates to
 * `ProfileController::viewOther()` which handles fetching the profile and
 * validating access.
 */

require_once __DIR__ . '/../app/controllers/ProfileController.php';
$controller = new ProfileController();
$controller->viewOther();
