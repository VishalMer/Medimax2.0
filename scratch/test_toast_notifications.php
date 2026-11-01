<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

echo "=== Running Profile & Password Toast Notifications Automated Test Suite ===\n\n";

// [TEST 1] Verify CSS has .toast-msg__icon.is-success rule
echo "[TEST 1] Verifying CSS .is-success style in custom.css...\n";
$css = file_get_contents(__DIR__ . '/../public/css/custom.css');
if (strpos($css, '.toast-msg__icon.is-success') !== false) {
    echo "  PASS: custom.css includes .toast-msg__icon.is-success style.\n";
} else {
    echo "  FAIL: custom.css missing .toast-msg__icon.is-success style.\n";
    exit(1);
}

// [TEST 2] Verify app.js has is-success handling and checkSessionToast trigger
echo "\n[TEST 2] Verifying app.js toast trigger and icon class logic...\n";
$appJs = file_get_contents(__DIR__ . '/../public/js/app.js');
if (strpos($appJs, "iconClass += ' is-success'") !== false &&
    strpos($appJs, "checkSessionToast") !== false &&
    strpos($appJs, "mmSessionToast") !== false) {
    echo "  PASS: app.js contains is-success contextual class and auto-trigger for #mmSessionToast.\n";
} else {
    echo "  FAIL: app.js missing session toast listener or is-success logic.\n";
    exit(1);
}

// [TEST 3] Verify header.php outputs #mmSessionToast when flash_toast is present
echo "\n[TEST 3] Verifying header.php renders #mmSessionToast on flash_toast...\n";
$_SESSION['flash_toast'] = [
    'message' => 'Profile updated successfully!',
    'icon'    => 'fa-circle-check',
    'type'    => 'success'
];
ob_start();
Controller::partial('layouts/header', ['pageTitle' => 'Test Page']);
$headerHtml = ob_get_clean();

if (strpos($headerHtml, 'id="mmSessionToast"') !== false &&
    strpos($headerHtml, 'data-msg="Profile updated successfully!"') !== false &&
    strpos($headerHtml, 'data-icon="fa-circle-check"') !== false) {
    echo "  PASS: header.php outputs #mmSessionToast with correct data attributes.\n";
} else {
    echo "  FAIL: header.php failed to output #mmSessionToast element.\n";
    exit(1);
}

// Verify no top line alert banners are rendered
if (strpos($headerHtml, 'alert alert-success') === false &&
    strpos($headerHtml, 'alert alert-danger') === false) {
    echo "  PASS: header.php does NOT render top alert banners.\n";
} else {
    echo "  FAIL: header.php still rendered top alert banners.\n";
    exit(1);
}

// Verify flash_toast was consumed (unset)
if (empty($_SESSION['flash_toast'])) {
    echo "  PASS: flash_toast consumed and unset from session.\n";
} else {
    echo "  FAIL: flash_toast was not unset.\n";
    exit(1);
}

// [TEST 4] Verify header.php fallback to flash_success
echo "\n[TEST 4] Verifying header.php fallback for standard flash_success...\n";
$_SESSION['flash_success'] = 'Operation completed.';
ob_start();
Controller::partial('layouts/header', ['pageTitle' => 'Test Page 2']);
$headerHtml2 = ob_get_clean();

if (strpos($headerHtml2, 'id="mmSessionToast"') !== false &&
    strpos($headerHtml2, 'data-msg="Operation completed."') !== false) {
    echo "  PASS: header.php automatically creates toast from flash_success.\n";
} else {
    echo "  FAIL: header.php missing fallback toast for flash_success.\n";
    exit(1);
}

if (strpos($headerHtml2, 'alert alert-success') === false &&
    strpos($headerHtml2, 'alert alert-danger') === false) {
    echo "  PASS: header.php does NOT render top alert banners on flash_success.\n";
} else {
    echo "  FAIL: header.php rendered top alert banner on flash_success.\n";
    exit(1);
}

// [TEST 5] Test Profile Update sets flash_toast in AuthController
echo "\n[TEST 5] Testing Profile Update sets flash_toast in AuthController...\n";
$pdo = Database::getConnection();
$testUser = $pdo->query("SELECT * FROM users WHERE role = 'customer' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$testUser) {
    die("FATAL: No customer user found.\n");
}

$_SESSION['user'] = $testUser;
$uniqueName = 'Tester John';
$returnTarget = url('products');

$runnerCode = '<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

register_shutdown_function(function() {
    echo "\nSHUTDOWN_SESSION_TOAST:" . json_encode($_SESSION["flash_toast"] ?? null) . "\n";
    echo "SHUTDOWN_SESSION_SUCCESS:" . ($_SESSION["flash_success"] ?? "") . "\n";
});

$_SESSION["user"] = ' . var_export($testUser, true) . ';
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST["name"] = "' . $uniqueName . '";
$_POST["phone"] = "9876543210";
$_POST["return_to"] = "' . $returnTarget . '";

$c = new AuthController();
$c->profile();
';

$tmpRunner = __DIR__ . '/_temp_profile_runner.php';
file_put_contents($tmpRunner, $runnerCode);
exec('"d:\\Xampp\\php\\php.exe" "' . $tmpRunner . '"', $out5, $ret5);
@unlink($tmpRunner);
$outText5 = implode("\n", $out5);

if (strpos($outText5, 'Profile updated successfully!') !== false) {
    echo "  PASS: AuthController::profile() sets 'Profile updated successfully!' toast message.\n";
} else {
    echo "  FAIL: Profile update failed to set flash_toast:\n$outText5\n";
    exit(1);
}

// [TEST 6] Test Password Update sets flash_toast in AuthController
echo "\n[TEST 6] Testing Password Update sets flash_toast in AuthController...\n";
$rawPassword = 'Password@123';
$newPassword = 'Password@456';
// Set password on user for test
$hashed = password_hash($rawPassword, PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password = :p WHERE id = :id")->execute([':p' => $hashed, ':id' => $testUser['id']]);

$pwRunnerCode = '<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

register_shutdown_function(function() {
    echo "\nSHUTDOWN_SESSION_TOAST:" . json_encode($_SESSION["flash_toast"] ?? null) . "\n";
    echo "SHUTDOWN_SESSION_SUCCESS:" . ($_SESSION["flash_success"] ?? "") . "\n";
});

$_SESSION["user"] = ' . var_export($testUser, true) . ';
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST["old_password"] = "' . $rawPassword . '";
$_POST["new_password"] = "' . $newPassword . '";
$_POST["confirm_password"] = "' . $newPassword . '";
$_POST["return_to"] = "' . $returnTarget . '";

$c = new AuthController();
$c->changePassword();
';

$tmpPwRunner = __DIR__ . '/_temp_pw_runner.php';
file_put_contents($tmpPwRunner, $pwRunnerCode);
exec('"d:\\Xampp\\php\\php.exe" "' . $tmpPwRunner . '"', $out6, $ret6);
@unlink($tmpPwRunner);
$outText6 = implode("\n", $out6);

// Restore original password
$pdo->prepare("UPDATE users SET password = :p WHERE id = :id")->execute([':p' => $testUser['password'], ':id' => $testUser['id']]);

if (strpos($outText6, 'Password updated successfully!') !== false) {
    echo "  PASS: AuthController::changePassword() sets 'Password updated successfully!' toast message.\n";
} else {
    echo "  FAIL: Password update failed to set flash_toast:\n$outText6\n";
    exit(1);
}

echo "\n*** ALL 6 TOAST NOTIFICATION TESTS PASSED WITH 100% SUCCESS! ***\n";
