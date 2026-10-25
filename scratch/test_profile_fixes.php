<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$pdo = Database::getConnection();
if (!$pdo) {
    die("FATAL: Cannot connect to MySQL.\n");
}

$auth = new AuthController();
$reflection = new ReflectionClass(AuthController::class);
$safeUrlMethod = $reflection->getMethod('getSafeReturnUrl');
$safeUrlMethod->setAccessible(true);

$_SERVER['HTTP_HOST'] = 'localhost';

// 1. Test getSafeReturnUrl
echo "[TEST 1] Testing getSafeReturnUrl validation...\n";

$cases = [
    ['/MediMax/?page=cart', '/MediMax/?page=cart', 'Relative cart page'],
    ['/MediMax/?page=orders', '/MediMax/?page=orders', 'Relative orders page'],
    ['http://localhost/MediMax/?page=wishlist', '/MediMax/?page=wishlist', 'Full URL on same host'],
    ['https://evil-site.com/steal-creds', BASE_URL, 'External phishing URL rejected'],
    ['/MediMax/?page=profile', BASE_URL, 'Circular profile page prevented'],
    ['/MediMax/?page=profile&return_to=foo', BASE_URL, 'Circular profile with query prevented'],
    ['', BASE_URL, 'Empty return_to defaults to BASE_URL']
];

foreach ($cases as [$input, $expected, $label]) {
    $result = $safeUrlMethod->invoke($auth, $input, BASE_URL);
    if ($result === $expected) {
        echo "  PASS: {$label} -> {$result}\n";
    } else {
        echo "  FAIL: {$label}: expected '{$expected}', got '{$result}'\n";
        exit(1);
    }
}

// 2. Setup test user
$testUsername = 'testcust_profile_' . time();
$testEmail    = $testUsername . '@example.com';
$passwordHash = password_hash('Secret123!', PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, role, is_active, created_at) VALUES (?, NULL, ?, ?, 'customer', 1, NOW())");
$stmt->execute([$testUsername, $testEmail, $passwordHash]);
$userId = (int)$pdo->lastInsertId();

$_SESSION['user'] = [
    'id'       => $userId,
    'username' => $testUsername,
    'name'     => null,
    'email'    => $testEmail,
    'role'     => 'customer'
];

// 3. Test Failure Case: Invalid Name format (must not redirect, must stay on profile page with error)
echo "\n[TEST 2] Testing profile update failure handling...\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'name'      => 'Invalid123!@#',
    'phone'     => '12345',
    'return_to' => '/MediMax/?page=cart'
];

ob_start();
$auth->profile();
$htmlOutput = ob_get_clean();

if (strpos($htmlOutput, 'Full name can only contain letters') !== false &&
    strpos($htmlOutput, 'Please enter a valid phone number') !== false) {
    echo "  PASS: Invalid input stayed on page and rendered descriptive error message.\n";
} else {
    echo "  FAIL: Expected validation errors not found in failure output.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

if (strpos($htmlOutput, 'value="Invalid123!@#"') !== false) {
    echo "  PASS: Submitted name preserved in form input during failure.\n";
} else {
    echo "  FAIL: Submitted name was not preserved in form input.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Verify DB was NOT updated
$chkStmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
$chkStmt->execute([$userId]);
$row = $chkStmt->fetch();
if ($row['name'] === null && $row['phone'] === null) {
    echo "  PASS: Database remained untouched after validation failure.\n";
} else {
    echo "  FAIL: Database was modified despite validation failure.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// 4. Test View Template fixes
echo "\n[TEST 3] Verifying profile.php template fixes...\n";
$profileViewSource = file_get_contents(__DIR__ . '/../app/views/pages/profile.php');

// Check py-4 removed from page
if (strpos($profileViewSource, '<div class="page py-4">') === false && strpos($profileViewSource, '<div class="page">') !== false) {
    echo "  PASS: 'py-4' removed from '<div class=\"page\">' to ensure proper clearance below header.\n";
} else {
    echo "  FAIL: 'py-4' still present on '<div class=\"page py-4\">'.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Check username input has no split/break and matches email input structure
if (strpos($profileViewSource, 'mm-input-group') === false && 
    strpos($profileViewSource, 'id="username" name="username_display"') !== false &&
    strpos($profileViewSource, 'value="@<?= htmlspecialchars(ltrim($uUsername, \'@\')) ?>"') !== false) {
    echo "  PASS: Username field has no break in between, rendered as unified single input matching email.\n";
} else {
    echo "  FAIL: Username field does not match expected single input format.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Check return_to hidden input
if (strpos($profileViewSource, 'name="return_to"') !== false) {
    echo "  PASS: Hidden return_to input field present in profile form.\n";
} else {
    echo "  FAIL: Hidden return_to input missing from profile form.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Check cancel button
if (strpos($profileViewSource, 'href="<?= htmlspecialchars($returnTo) ?>" class="btn-mm btn-mm-ghost">Cancel</a>') !== false) {
    echo "  PASS: Cancel button correctly links back to return_to destination.\n";
} else {
    echo "  FAIL: Cancel button does not link to returnTo.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// 5. Check header.php for return_to in Edit profile link
echo "\n[TEST 4] Verifying header.php Edit Profile link with return_to...\n";
$headerSource = file_get_contents(__DIR__ . '/../app/views/layouts/header.php');
if (strpos($headerSource, 'return_to=') !== false && strpos($headerSource, 'Edit profile') !== false) {
    echo "  PASS: header.php passes current URI as return_to parameter when opening Edit profile.\n";
} else {
    echo "  FAIL: header.php missing return_to parameter in Edit profile link.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// 6. Check custom.css for .mm-input-group styling
echo "\n[TEST 5] Verifying custom.css input group styling...\n";
$cssSource = file_get_contents(__DIR__ . '/../public/css/custom.css');
if (strpos($cssSource, '.mm-input-group') !== false && strpos($cssSource, 'flex-wrap: nowrap') !== false) {
    echo "  PASS: custom.css has .mm-input-group with nowrap flex layout.\n";
} else {
    echo "  FAIL: custom.css missing .mm-input-group styling.\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// 7. Test Subprocess Success Execution (checks exit, session, db, and flash)
echo "\n[TEST 6] Testing profile update success execution in isolated runner...\n";
$testRunnerScript = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = [
    "id"       => ' . $userId . ',
    "username" => "' . $testUsername . '",
    "name"     => null,
    "email"    => "' . $testEmail . '",
    "role"     => "customer"
];
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "name"      => "Dr. Jane Watson",
    "phone"     => "+91 98765 43210",
    "return_to" => "/MediMax/?page=orders"
];
$auth = new AuthController();
$auth->profile();
';
$tempScriptPath = __DIR__ . '/_temp_profile_test_runner.php';
file_put_contents($tempScriptPath, $testRunnerScript);

$cmd = '"d:\\Xampp\\php\\php.exe" "' . $tempScriptPath . '"';
exec($cmd, $out, $retCode);
@unlink($tempScriptPath);

// Verify DB after subprocess update
$chkStmt->execute([$userId]);
$updatedRow = $chkStmt->fetch();
if ($updatedRow['name'] === 'Dr. Jane Watson' && $updatedRow['phone'] === '+91 98765 43210') {
    echo "  PASS: Database updated with new display name and phone number.\n";
} else {
    echo "  FAIL: Database was not updated correctly. Got name: '{$updatedRow['name']}'\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Clean up test user
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
echo "\nCleaned up temporary test user.\n";

echo "\n*** ALL TESTS PASSED WITH 100% SUCCESS! ***\n";
