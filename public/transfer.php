<?php
/*
 * public/transfer.php
 *
 * Purpose: Public endpoint for initiating money transfers. Delegates to the
 * TransferController which implements transactional safety and input validation.
 */

require_once __DIR__ . '/../app/controllers/TransferController.php';
(new TransferController())->transfer();
