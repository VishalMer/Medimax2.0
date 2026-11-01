<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';

echo "=== Verifying Default Profile Picture Setup ===\n";

$pdo = Database::getConnection();
if (!$pdo) {
    die("FATAL: Cannot connect to MySQL.\n");
}

// 1. Verify DB Column Default
echo "\n[TEST 1] Checking DB column default for users.image...\n";
$colStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'image'");
$col = $colStmt->fetch(PDO::FETCH_ASSOC);
if ($col['Default'] === 'default_pfp.jpg') {
    echo "  PASS: Column default is 'default_pfp.jpg'.\n";
} else {
    echo "  FAIL: Expected column default 'default_pfp.jpg', got: " . var_export($col['Default'], true) . "\n";
    exit(1);
}

// 2. Test inserting user without image column specified
echo "\n[TEST 2] Testing user creation default image...\n";
$testUser = 'testpfp_' . time();
$testEmail = $testUser . '@example.com';
$hash = password_hash('Pass123!', PASSWORD_BCRYPT);
$pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)")->execute([$testUser, $testEmail, $hash]);
$newId = (int)$pdo->lastInsertId();

$checkStmt = $pdo->prepare("SELECT image FROM users WHERE id = ?");
$checkStmt->execute([$newId]);
$savedImage = $checkStmt->fetchColumn();

if ($savedImage === 'default_pfp.jpg') {
    echo "  PASS: New user automatically assigned image 'default_pfp.jpg'.\n";
} else {
    echo "  FAIL: Expected 'default_pfp.jpg', got: " . var_export($savedImage, true) . "\n";
    exit(1);
}
// Clean up
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$newId]);

// 3. Test profileImage() helper
echo "\n[TEST 3] Testing profileImage() helper...\n";
$defaultResult = profileImage('default_pfp.jpg');
$customResult = profileImage('custom_avatar.png');

if (str_ends_with($defaultResult, 'images/profiles/default_pfp.jpg')) {
    echo "  PASS: profileImage('default_pfp.jpg') directly returns default_pfp.jpg path ($defaultResult)\n";
} else {
    echo "  FAIL: profileImage('default_pfp.jpg') = $defaultResult\n";
    exit(1);
}

if (str_ends_with($customResult, 'images/profiles/custom_avatar.png')) {
    echo "  PASS: profileImage('custom_avatar.png') directly returns custom image path ($customResult)\n";
} else {
    echo "  FAIL: profileImage('custom_avatar.png') = $customResult\n";
    exit(1);
}

// 4. Verify no first-letter code in codebase
echo "\n[TEST 4] Scanning codebase for any first-letter avatar code...\n";
$files = [
    'app/views/layouts/header.php',
    'app/views/layouts/admin_layout.php',
    'app/views/layouts/admin_sidebar.php',
    'app/views/pages/profile.php',
    'app/views/pages/change_password.php',
    'app/views/pages/admin/edit_user.php',
    'app/views/pages/admin/users.php',
    'app/views/pages/admin/update_profile.php',
];

foreach ($files as $file) {
    $content = file_get_contents(__DIR__ . '/../' . $file);
    if (preg_match('/substr\s*\(\s*(\$adminUser|\$user|\$uName|\$displayName)[^)]*, 0, 1\)/i', $content, $m)) {
        echo "  FAIL: Found first-letter extraction in {$file}: {$m[0]}\n";
        exit(1);
    }
    if (strpos($content, 'data-avatar-tint') !== false) {
        echo "  FAIL: Found data-avatar-tint in {$file}\n";
        exit(1);
    }
    echo "  PASS: {$file} has no first-letter or avatar-tint logic.\n";
}

// 5. Test guest / logged-out user header avatar
echo "\n[TEST 5] Testing guest user header avatar rendering...\n";
$_SESSION = []; // Simulate logged-out user
ob_start();
include __DIR__ . '/../app/views/layouts/header.php';
$headerOutput = ob_get_clean();

// Check the account-btn element specifically
if (preg_match('/<button class="account-btn"[^>]*>(.*?)<\/button>/s', $headerOutput, $btnMatch)) {
    $btnHtml = $btnMatch[1];
    if (strpos($btnHtml, 'default_pfp.jpg') !== false && strpos($btnHtml, 'fa-user') === false) {
        echo "  PASS: Guest/logged-out user displays default_pfp.jpg in header account button.\n";
    } else {
        echo "  FAIL: Guest account button did not render default_pfp.jpg: " . htmlspecialchars($btnHtml) . "\n";
        exit(1);
    }
} else {
    echo "  FAIL: Could not locate account-btn in header.\n";
    exit(1);
}

echo "\n*** ALL TESTS PASSED SUCCESSFULLY! ***\n";
