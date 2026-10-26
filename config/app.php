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

// Database configuration (Default XAMPP MySQL)
if (!defined('DB_HOST'))    define('DB_HOST', 'localhost');
if (!defined('DB_PORT'))    define('DB_PORT', '3306');
if (!defined('DB_NAME'))    define('DB_NAME', 'medimax_db');
if (!defined('DB_USER'))    define('DB_USER', 'root');
if (!defined('DB_PASS'))    define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Asset helper function with automatic subfolder resolution for images
function asset($path) {
    $cleanPath = ltrim($path, '/');
    
    // Automatically map legacy images/filename if not already in a specific subfolder
    if (str_starts_with($cleanPath, 'images/') && substr_count($cleanPath, '/') === 1) {
        $file = substr($cleanPath, 7);
        if (!empty($file)) {
            if (file_exists(PUBLIC_PATH . '/images/assets/' . $file)) {
                return BASE_URL . 'public/images/assets/' . $file;
            }
            if (file_exists(PUBLIC_PATH . '/images/profiles/' . $file)) {
                return BASE_URL . 'public/images/profiles/' . $file;
            }
            if (file_exists(PUBLIC_PATH . '/images/products/' . $file)) {
                return BASE_URL . 'public/images/products/' . $file;
            }
        }
    }
    
    return BASE_URL . 'public/' . $cleanPath;
}

// Helpers for specific image categories
function productImage($filename) {
    if (empty($filename)) {
        return asset('images/assets/placeholder.png');
    }
    return asset('images/products/' . ltrim($filename, '/'));
}

function profileImage($filename) {
    if (empty($filename)) {
        return asset('images/profiles/Rahul.jpg');
    }
    return asset('images/profiles/' . ltrim($filename, '/'));
}

function brandAsset($filename) {
    return asset('images/assets/' . ltrim($filename, '/'));
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
