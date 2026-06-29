<?php
// Registration page (controller logic ncluded )
require_once __DIR__ . '/../app/controllers/AuthController.php';
(new AuthController())->register();
