<?php
/*
 * public/dashboard.php
 *
 * Purpose: Public entry that displays the authenticated user's dashboard.
 * Why: The controller enforces authentication and prepares data for the view.
 */

require_once __DIR__ . '/../app/controllers/DashboardController.php';
(new DashboardController())->show();
