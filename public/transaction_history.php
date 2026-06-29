<?php
/*
 * public/transaction_history.php
 *
 * Purpose: Public endpoint to view a user's full transaction history.
 * Delegates to the TransactionHistoryController which prepares the data
 * and enforces authentication.
 */

require_once __DIR__ . '/../app/controllers/TransactionHistoryController.php';
$controller = new TransactionHistoryController();
$controller->show();
