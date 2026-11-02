<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

echo "=== Running Admin Toast Notifications Automated Test Suite ===\n\n";

$pdo = Database::getConnection();
if (!$pdo) {
    die("FATAL: Cannot connect to MySQL.\n");
}

// 1. Verify admin user session
$adminUser = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'owner') AND is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$adminUser) {
    die("FATAL: No active admin/owner user found.\n");
}
$_SESSION['user'] = $adminUser;

// [TEST 1] Simulate successful user creation flash message
echo "[TEST 1] Verifying flash_success renders #mmSessionToast and NO top alert banners on admin-users...\n";
$_SESSION['flash_success'] = "User 'Vishal' (vishal@medimax.com) has been created successfully with role 'Owner'.";

ob_start();
$adminCtrl = new AdminController();
$adminCtrl->users();
$usersPageHtml = ob_get_clean();

// Must contain mmSessionToast with the exact message
if (strpos($usersPageHtml, 'id="mmSessionToast"') !== false &&
    strpos($usersPageHtml, 'data-msg="User &#039;Vishal&#039; (vishal@medimax.com) has been created successfully with role &#039;Owner&#039;."') !== false &&
    strpos($usersPageHtml, 'data-icon="fa-circle-check"') !== false) {
    echo "  PASS: admin users page outputs #mmSessionToast with correct message and icon.\n";
} else {
    echo "  FAIL: #mmSessionToast missing or incorrect in admin users output.\n";
    exit(1);
}

// Must NOT contain alert-success banner
if (strpos($usersPageHtml, 'alert alert-success') === false &&
    strpos($usersPageHtml, 'alert-dismissible') === false) {
    echo "  PASS: Top alert-success banner was completely removed from admin users page.\n";
} else {
    echo "  FAIL: Top alert banner still rendered on admin users page.\n";
    exit(1);
}

// [TEST 2] Simulate flash_error on admin-add-user
echo "\n[TEST 2] Verifying flash_error renders #mmSessionToast and NO top alert banner on admin-add-user...\n";
$_SESSION['flash_error'] = "A user with this email address already exists.";

ob_start();
$adminCtrl->addUser();
$addUserHtml = ob_get_clean();

if (strpos($addUserHtml, 'id="mmSessionToast"') !== false &&
    strpos($addUserHtml, 'data-msg="A user with this email address already exists."') !== false &&
    strpos($addUserHtml, 'data-icon="fa-circle-exclamation"') !== false) {
    echo "  PASS: admin add user page outputs #mmSessionToast with error icon.\n";
} else {
    echo "  FAIL: #mmSessionToast missing or incorrect for error on add user page.\n";
    exit(1);
}

if (strpos($addUserHtml, 'alert alert-danger') === false) {
    echo "  PASS: Top alert-danger banner was completely removed from add user page.\n";
} else {
    echo "  FAIL: Top alert banner still rendered on add user page.\n";
    exit(1);
}

// [TEST 3] Verify other admin pages have no alert-success / alert-dismissible banners
echo "\n[TEST 3] Scanning all admin view templates for any remaining flash alert banners...\n";
$adminViews = [
    'app/views/pages/admin/users.php',
    'app/views/pages/admin/product_list.php',
    'app/views/pages/admin/all_orders.php',
    'app/views/pages/admin/discounts.php',
    'app/views/pages/admin/update_profile.php',
    'app/views/pages/admin/add_user.php',
    'app/views/pages/admin/edit_user.php',
    'app/views/pages/admin/add_product.php',
    'app/views/pages/admin/edit_product.php'
];

foreach ($adminViews as $file) {
    $content = file_get_contents(__DIR__ . '/../' . $file);
    if (strpos($content, '$_SESSION[\'flash_success\']') !== false ||
        strpos($content, '$_SESSION[\'flash_error\']') !== false ||
        strpos($content, 'alert alert-success') !== false) {
        echo "  FAIL: Found flash alert banner code in {$file}\n";
        exit(1);
    }
    echo "  PASS: {$file} has no top alert banners.\n";
}

echo "\n*** ALL ADMIN TOAST NOTIFICATION TESTS PASSED WITH 100% SUCCESS! ***\n";
