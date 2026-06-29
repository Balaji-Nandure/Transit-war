<?php
// Dashboard page (requires authentication)
require_once __DIR__ . '/../app/controllers/DashboardController.php';
(new DashboardController())->show();
