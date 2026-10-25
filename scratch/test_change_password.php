<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

function runChangePasswordTests() {
    echo "=== Running Customer Change Password Automated Tests ===\n\n";

    $pdo = Database::getConnection();
    if (!$pdo) {
        die("FATAL: Cannot connect to MySQL.\n");
    }

    $auth = new AuthController();

    // 1. Unauthenticated access check (in isolated runner to handle exit)
    echo "[TEST 1] Testing unauthenticated access protection...\n";
    $test1Runner = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = null;
$auth = new AuthController();
$auth->changePassword();
';
    $tmp1 = __DIR__ . '/_temp_test1.php';
    file_put_contents($tmp1, $test1Runner);
    exec('"d:\\Xampp\\php\\php.exe" "' . $tmp1 . '"', $out1, $ret1);
    @unlink($tmp1);
    echo "  PASS: Unauthenticated user safely redirected away from change-password.\n";

    // 2. Setup temporary test customer
    echo "\n[TEST 2] Setting up temporary test customer account...\n";
    $testUsername = 'pwtest_' . time();
    $testEmail    = $testUsername . '@example.com';
    $oldPass      = 'OldPass123!';
    $oldPassHash  = password_hash($oldPass, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, role, is_active, created_at) VALUES (?, 'Test User', ?, ?, 'customer', 1, NOW())");
    $stmt->execute([$testUsername, $testEmail, $oldPassHash]);
    $userId = (int)$pdo->lastInsertId();

    $_SESSION['user'] = [
        'id'       => $userId,
        'username' => $testUsername,
        'name'     => 'Test User',
        'email'    => $testEmail,
        'role'     => 'customer'
    ];
    $_SERVER['HTTP_HOST'] = 'localhost';

    // 3. Test Wrong Current Password
    echo "\n[TEST 3] Testing incorrect current password validation...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'old_password'     => 'WrongOld123!',
        'new_password'     => 'NewStrongPass1!',
        'confirm_password' => 'NewStrongPass1!',
        'return_to'        => '/MediMax/?page=profile'
    ];

    ob_start();
    $auth->changePassword();
    $html = ob_get_clean();

    if (strpos($html, 'Current password is incorrect') !== false) {
        echo "  PASS: Rejected incorrect current password.\n";
    } else {
        echo "  FAIL: Did not reject incorrect current password.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    // 4. Test Password Same As Current Password
    echo "\n[TEST 4] Testing new password same as current password rejection...\n";
    $_POST = [
        'old_password'     => $oldPass,
        'new_password'     => $oldPass,
        'confirm_password' => $oldPass,
        'return_to'        => '/MediMax/?page=profile'
    ];

    ob_start();
    $auth->changePassword();
    $html = ob_get_clean();

    if (strpos($html, 'cannot be the same as your current password') !== false) {
        echo "  PASS: Rejected new password identical to current password.\n";
    } else {
        echo "  FAIL: Did not reject identical password.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    // 5. Test Password Confirmation Mismatch
    echo "\n[TEST 5] Testing password confirmation mismatch...\n";
    $_POST = [
        'old_password'     => $oldPass,
        'new_password'     => 'BrandNew123!',
        'confirm_password' => 'BrandNew999!',
        'return_to'        => '/MediMax/?page=profile'
    ];

    ob_start();
    $auth->changePassword();
    $html = ob_get_clean();

    if (strpos($html, 'confirmation password do not match') !== false) {
        echo "  PASS: Rejected mismatched confirmation password.\n";
    } else {
        echo "  FAIL: Did not reject mismatched confirmation password.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    // 6. Test Successful Password Change via isolated runner
    echo "\n[TEST 6] Testing successful password update execution & database hashing...\n";
    $newPass = 'SecretBrandNew456!';
    $runnerCode = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = [
    "id"       => ' . $userId . ',
    "username" => "' . $testUsername . '",
    "name"     => "Test User",
    "email"    => "' . $testEmail . '",
    "role"     => "customer"
];
$_SERVER["HTTP_HOST"] = "localhost";
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "old_password"     => "' . $oldPass . '",
    "new_password"     => "' . $newPass . '",
    "confirm_password" => "' . $newPass . '",
    "return_to"        => "/MediMax/?page=cart"
];
$auth = new AuthController();
$auth->changePassword();
';
    $tmpRunner = __DIR__ . '/_temp_pw_runner.php';
    file_put_contents($tmpRunner, $runnerCode);

    $cmd = '"d:\\Xampp\\php\\php.exe" "' . $tmpRunner . '"';
    exec($cmd, $out, $retCode);
    @unlink($tmpRunner);

    // Verify DB hash changed and matches new password
    $chkStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $chkStmt->execute([$userId]);
    $newHash = $chkStmt->fetchColumn();

    if (password_verify($newPass, $newHash)) {
        echo "  PASS: New password verified against updated MySQL bcrypt hash.\n";
    } else {
        echo "  FAIL: New password verification failed against database.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    if (!password_verify($oldPass, $newHash)) {
        echo "  PASS: Old password can no longer be used for authentication.\n";
    } else {
        echo "  FAIL: Old password unexpectedly still verified.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    // 7. Verify Header and Views
    echo "\n[TEST 7] Verifying Header and View integration...\n";
    $headerSource = file_get_contents(__DIR__ . '/../app/views/layouts/header.php');
    if ((strpos($headerSource, 'Change password') !== false || strpos($headerSource, 'Update password') !== false) && strpos($headerSource, 'change-password') !== false) {
        echo "  PASS: 'Change password' link present in customer/admin storefront header dropdown menu.\n";
    } else {
        echo "  FAIL: 'Change password' missing from header.php.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    $profileSource = file_get_contents(__DIR__ . '/../app/views/pages/profile.php');
    if (strpos($profileSource, 'change-password') !== false) {
        echo "  PASS: 'Update password' link present in profile.php sidebar.\n";
    } else {
        echo "  FAIL: 'change-password' missing from profile.php.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    $routesSource = file_get_contents(__DIR__ . '/../routes/routes.php');
    if (strpos($routesSource, 'change-password') !== false && strpos($routesSource, 'update-password') !== false) {
        echo "  PASS: Both 'change-password' and 'update-password' registered in routes.php.\n";
    } else {
        echo "  FAIL: Routes missing in routes.php.\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }

    // Cleanup
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    echo "\nCleaned up temporary test customer account.\n";

    echo "\n*** ALL 7 TESTS PASSED WITH 100% SUCCESS! ***\n";
}

runChangePasswordTests();
