<?php
// Profile management page
require_once __DIR__ . '/../app/controllers/ProfileController.php';
(new ProfileController())->profile();
