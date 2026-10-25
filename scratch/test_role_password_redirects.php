<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';

echo "=== Verifying Role-Independent Password Redirection on Site vs Admin Panel ===\n\n";

// 1. Customer on Storefront Header
echo "[TEST 1] Testing customer in storefront header...\n";
$_SESSION['user'] = [
    'id'       => 101,
    'username' => 'testcust',
    'name'     => 'Customer Test',
    'email'    => 'cust@medimax.com',
    'role'     => 'customer'
];
$_SERVER['REQUEST_URI'] = '/MediMax/?page=home';
ob_start();
include __DIR__ . '/../app/views/layouts/header.php';
$customerHeader = ob_get_clean();

// Customer should have change-password link to site
if (strpos($customerHeader, 'href="/MediMax/?page=change-password&amp;return_to=%2FMediMax%2F%3Fpage%3Dhome"') !== false ||
    strpos($customerHeader, 'page=change-password') !== false) {
    echo "  PASS: Customer dropdown links to site's change-password page.\n";
} else {
    echo "  FAIL: Customer dropdown missing site change-password link.\n";
    exit(1);
}

// Customer should NOT have admin panel link or admin-update-password
if (strpos($customerHeader, 'admin-update-password') === false && strpos($customerHeader, 'Admin panel') === false) {
    echo "  PASS: Customer does not see admin panel links.\n";
} else {
    echo "  FAIL: Customer unexpectedly sees admin links.\n";
    exit(1);
}

// 2. Admin on Storefront Header
echo "\n[TEST 2] Testing admin/owner in storefront header...\n";
$_SESSION['user'] = [
    'id'       => 1,
    'username' => 'admin',
    'name'     => 'Admin User',
    'email'    => 'admin@medimax.com',
    'role'     => 'admin'
];
$_SERVER['REQUEST_URI'] = '/MediMax/?page=products';
ob_start();
include __DIR__ . '/../app/views/layouts/header.php';
$adminStorefrontHeader = ob_get_clean();

// Admin on site MUST link to site's change-password page
if (strpos($adminStorefrontHeader, 'page=change-password') !== false) {
    echo "  PASS: Admin on storefront dropdown links to site's change-password page.\n";
} else {
    echo "  FAIL: Admin on storefront missing site change-password link.\n";
    exit(1);
}

// Admin on site MUST NOT have admin-update-password or admin-update-profile in storefront header
if (strpos($adminStorefrontHeader, 'admin-update-password') === false && strpos($adminStorefrontHeader, 'admin-update-profile') === false) {
    echo "  PASS: Admin on storefront does not have duplicate admin-update-password/profile links.\n";
} else {
    echo "  FAIL: Admin on storefront still has admin-update-password or admin-update-profile.\n";
    exit(1);
}

// Admin on site DOES have "Admin panel" link
if (strpos($adminStorefrontHeader, 'Admin panel') !== false && strpos($adminStorefrontHeader, 'href="/MediMax/?page=admin"') !== false) {
    echo "  PASS: Admin on storefront has 'Admin panel' link to enter admin console.\n";
} else {
    echo "  FAIL: Admin on storefront missing 'Admin panel' link.\n";
    exit(1);
}

// 3. Admin inside Admin Panel Sidebar
echo "\n[TEST 3] Testing admin inside Admin Panel sidebar...\n";
$adminUser = $_SESSION['user'];
$currentPage = 'admin';
ob_start();
include __DIR__ . '/../app/views/layouts/admin_sidebar.php';
$adminSidebar = ob_get_clean();

// Inside admin panel, Change password link points to admin-update-password
if (strpos($adminSidebar, 'href="/MediMax/?page=admin-update-password"') !== false) {
    echo "  PASS: Admin inside admin panel sidebar links to admin-update-password.\n";
} else {
    echo "  FAIL: Admin panel sidebar does not link to admin-update-password.\n";
    exit(1);
}

echo "\n*** ALL ROLE PASSWORD REDIRECTION TESTS PASSED WITH 100% SUCCESS! ***\n";
