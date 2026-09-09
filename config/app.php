<?php
/**
 * MediMax Bootstrap - Application Configuration
 */

// Base URL - adjust if project is in a different folder
define('BASE_URL', '/MediMax/');

// Application name
define('APP_NAME', 'MediMax');

// Application paths
define('APP_PATH', dirname(__DIR__) . '/app');
define('PUBLIC_PATH', dirname(__DIR__) . '/public');
define('CONFIG_PATH', dirname(__DIR__) . '/config');

// Asset helper function
function asset($path) {
    return BASE_URL . 'public/' . ltrim($path, '/');
}

// URL helper function
function url($page = '') {
    if (empty($page)) {
        return BASE_URL;
    }
    return BASE_URL . '?page=' . $page;
}

// Active page check helper
function isActive($page, $current) {
    return $page === $current ? 'active' : '';
}
