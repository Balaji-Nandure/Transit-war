<?php
// Login page (controller logic will be included here)
require_once __DIR__ . '/../app/controllers/AuthController.php';
(new AuthController())->login();
