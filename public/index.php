<?php
/**
 * MediMax Bootstrap - Front Controller (Entry Point)
 * 
 * All requests go through this file.
 * URL: http://localhost/MediMax/
 */

// Error reporting - suppress notices in production
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Load configuration
require_once __DIR__ . '/../config/app.php';

// Load core classes
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';

// Load model
require_once APP_PATH . '/models/DummyData.php';

// Create router and load routes
$router = new Router();
require_once __DIR__ . '/../routes/routes.php';

// Dispatch the request
$router->dispatch();
