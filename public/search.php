<?php
/*
 * public/search.php
 *
 * Purpose: Minimal public endpoint that delegates to the application controller.
 * Why: Keeping controllers separate from public entry points helps testing
 * and reuse. This script simply includes the controller and executes the
 * `search()` action which handles authentication, input processing, and view rendering.
 */

// Load the controller implementation from the application layer.
require_once __DIR__ . '/../app/controllers/SearchController.php';

// Instantiate and run the controller action. The controller itself will
// start secure sessions and include the appropriate view.
(new SearchController())->search();

