<?php
// Money transfer page
require_once __DIR__ . '/../app/controllers/TransferController.php';
(new TransferController())->transfer();
