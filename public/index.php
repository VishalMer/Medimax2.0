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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load core classes
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';

// Class autoloader for controllers and core classes
spl_autoload_register(function ($class) {
    $controllerFile = APP_PATH . '/controllers/' . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }
    $coreFile = APP_PATH . '/core/' . $class . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
});

// Create router and load routes
$router = new Router();
require_once __DIR__ . '/../routes/routes.php';

// Dispatch the request
$router->dispatch();
