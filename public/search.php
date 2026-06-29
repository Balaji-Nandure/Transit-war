<?php
// User search page
require_once __DIR__ . '/../app/controllers/SearchController.php';
(new SearchController())->search();
