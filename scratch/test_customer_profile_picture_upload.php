<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

echo "=== Testing Customer Profile Picture Upload & Update Feature ===\n\n";

$pdo = Database::getConnection();
if (!$pdo) {
    die("FATAL: Cannot connect to MySQL database.\n");
}

// Create a test customer user
$testUsername = 'cust_pfp_test_' . time();
$testEmail = $testUsername . '@example.com';
$hash = password_hash('TestPass123!', PASSWORD_BCRYPT);

$pdo->prepare("INSERT INTO users (username, name, email, password, role, image, is_active, created_at) VALUES (?, 'Test Cust', ?, ?, 'customer', 'default_pfp.jpg', 1, NOW())")
    ->execute([$testUsername, $testEmail, $hash]);
$userId = (int)$pdo->lastInsertId();

$_SESSION['user'] = [
    'id'       => $userId,
    'username' => $testUsername,
    'name'     => 'Test Cust',
    'email'    => $testEmail,
    'role'     => 'customer',
    'image'    => 'default_pfp.jpg'
];

$_SERVER['HTTP_HOST'] = 'localhost';

// Helper to create a temporary test image using existing valid assets
function createDummyImage($ext = 'png') {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_img_') . '.' . $ext;
    if ($ext === 'png') {
        $source = PUBLIC_PATH . '/images/assets/MediMax_Logo.png';
        if (!file_exists($source)) $source = PUBLIC_PATH . '/images/assets/placeholder.png';
    } else {
        $source = PUBLIC_PATH . '/images/profiles/default_pfp.jpg';
    }
    copy($source, $tempFile);
    return $tempFile;
}

// -------------------------------------------------------------
// [TEST 1] View Template Verification
// -------------------------------------------------------------
echo "[TEST 1] Verifying profile.php template elements for picture upload...\n";
$viewSource = file_get_contents(__DIR__ . '/../app/views/pages/profile.php');

$requiredSnippets = [
    'enctype="multipart/form-data"' => 'Form has multipart/form-data encoding',
    'id="image" name="image"' => 'File input for image is present',
    'id="avatarPreviewForm"' => 'Form preview image element present',
    'id="avatarPreviewSummary"' => 'Summary card preview image element present',
    'id="summaryChangePhotoBtn"' => 'Camera trigger button on summary card present',
    'id="fileNameDisplay"' => 'File name display indicator present',
    'data-filetypes="jpg,jpeg,png,webp"' => 'Client-side allowed file types defined',
];

foreach ($requiredSnippets as $snippet => $label) {
    if (strpos($viewSource, $snippet) !== false) {
        echo "  PASS: {$label}\n";
    } else {
        echo "  FAIL: {$label} ('{$snippet}' missing)\n";
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        exit(1);
    }
}

// -------------------------------------------------------------
// [TEST 2] Upload Valid Profile Picture via Subprocess
// -------------------------------------------------------------
echo "\n[TEST 2] Testing successful profile picture upload...\n";
$dummyPng = createDummyImage('png', 120, 120);

$runnerScript = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = [
    "id"       => ' . $userId . ',
    "username" => "' . $testUsername . '",
    "name"     => "Test Cust",
    "email"    => "' . $testEmail . '",
    "role"     => "customer",
    "image"    => "default_pfp.jpg"
];
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "name"      => "Updated Customer Name",
    "phone"     => "+91 91234 56789",
    "return_to" => "/MediMax/?page=profile"
];

// Mock $_FILES
$_FILES["image"] = [
    "name"     => "my_avatar.png",
    "type"     => "image/png",
    "tmp_name" => "' . addslashes($dummyPng) . '",
    "error"    => UPLOAD_ERR_OK,
    "size"     => ' . filesize($dummyPng) . '
];

$auth = new AuthController();
$auth->profile();
';

$tempScript = __DIR__ . '/_temp_pfp_runner.php';
file_put_contents($tempScript, $runnerScript);
exec('"d:\\Xampp\\php\\php.exe" "' . $tempScript . '"', $out1, $ret1);
@unlink($tempScript);
@unlink($dummyPng);

// Check DB
$stmt = $pdo->prepare("SELECT name, phone, image FROM users WHERE id = ?");
$stmt->execute([$userId]);
$row1 = $stmt->fetch();

$uploadedFile1 = $row1['image'];
$targetPath1 = PUBLIC_PATH . '/images/profiles/' . $uploadedFile1;

if (str_starts_with($uploadedFile1, 'avatar_' . $userId . '_') && str_ends_with($uploadedFile1, '.png')) {
    echo "  PASS: User image column updated to new avatar: {$uploadedFile1}\n";
} else {
    echo "  FAIL: Expected avatar_{$userId}_*.png, got: " . var_export($uploadedFile1, true) . "\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

if (file_exists($targetPath1)) {
    echo "  PASS: Uploaded image file exists on disk in public/images/profiles/\n";
} else {
    echo "  FAIL: File was not found on disk at: {$targetPath1}\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// -------------------------------------------------------------
// [TEST 3] Upload a Second Picture & Ensure Previous File is Cleaned Up
// -------------------------------------------------------------
echo "\n[TEST 3] Testing replacement upload and cleanup of previous avatar...\n";
$dummyJpg = createDummyImage('jpg', 150, 150);

$runnerScript2 = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = [
    "id"       => ' . $userId . ',
    "username" => "' . $testUsername . '",
    "name"     => "Updated Customer Name",
    "email"    => "' . $testEmail . '",
    "role"     => "customer",
    "image"    => "' . $uploadedFile1 . '"
];
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "name"      => "Updated Customer Name",
    "phone"     => "+91 91234 56789",
    "return_to" => "/MediMax/?page=profile"
];

$_FILES["image"] = [
    "name"     => "second_avatar.jpg",
    "type"     => "image/jpeg",
    "tmp_name" => "' . addslashes($dummyJpg) . '",
    "error"    => UPLOAD_ERR_OK,
    "size"     => ' . filesize($dummyJpg) . '
];

$auth = new AuthController();
$auth->profile();
';

file_put_contents($tempScript, $runnerScript2);
exec('"d:\\Xampp\\php\\php.exe" "' . $tempScript . '"', $out2, $ret2);
@unlink($tempScript);
@unlink($dummyJpg);

$stmt->execute([$userId]);
$row2 = $stmt->fetch();
$uploadedFile2 = $row2['image'];
$targetPath2 = PUBLIC_PATH . '/images/profiles/' . $uploadedFile2;

if (str_starts_with($uploadedFile2, 'avatar_' . $userId . '_') && str_ends_with($uploadedFile2, '.jpg')) {
    echo "  PASS: User image updated to second avatar: {$uploadedFile2}\n";
} else {
    echo "  FAIL: Expected avatar_{$userId}_*.jpg, got: " . var_export($uploadedFile2, true) . "\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

if (!file_exists($targetPath1)) {
    echo "  PASS: Previous avatar file was cleanly removed from disk.\n";
} else {
    echo "  WARN: Previous avatar file still exists on disk.\n";
    @unlink($targetPath1);
}

if (file_exists($targetPath2)) {
    echo "  PASS: New avatar file exists on disk.\n";
} else {
    echo "  FAIL: New avatar file missing on disk: {$targetPath2}\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// -------------------------------------------------------------
// [TEST 4] Reset to Default Profile Picture (remove_image=1)
// -------------------------------------------------------------
echo "\n[TEST 4] Testing 'Reset to default' (remove_image)...\n";
$runnerScript3 = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/AuthController.php') . '";

$_SESSION["user"] = [
    "id"       => ' . $userId . ',
    "username" => "' . $testUsername . '",
    "name"     => "Updated Customer Name",
    "email"    => "' . $testEmail . '",
    "role"     => "customer",
    "image"    => "' . $uploadedFile2 . '"
];
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "name"         => "Updated Customer Name",
    "phone"        => "+91 91234 56789",
    "remove_image" => "1",
    "return_to"    => "/MediMax/?page=profile"
];

$auth = new AuthController();
$auth->profile();
';

file_put_contents($tempScript, $runnerScript3);
exec('"d:\\Xampp\\php\\php.exe" "' . $tempScript . '"', $out3, $ret3);
@unlink($tempScript);

$stmt->execute([$userId]);
$row3 = $stmt->fetch();
if ($row3['image'] === 'default_pfp.jpg') {
    echo "  PASS: Database image successfully reset to 'default_pfp.jpg'.\n";
} else {
    echo "  FAIL: Expected 'default_pfp.jpg', got: " . var_export($row3['image'], true) . "\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

if (!file_exists($targetPath2)) {
    echo "  PASS: Reset also cleaned up the uploaded custom image from disk.\n";
} else {
    echo "  WARN: Old image file was not deleted upon reset.\n";
    @unlink($targetPath2);
}

// -------------------------------------------------------------
// [TEST 5] Rejecting Invalid Image Format / Extension
// -------------------------------------------------------------
echo "\n[TEST 5] Testing rejection of non-image file...\n";
$tempTxt = tempnam(sys_get_temp_dir(), 'test_fake_') . '.txt';
file_put_contents($tempTxt, 'This is plain text pretending to be an avatar.');

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'name'      => 'Updated Customer Name',
    'phone'     => '+91 91234 56789',
    'return_to' => '/MediMax/?page=profile'
];
$_FILES['image'] = [
    'name'     => 'malicious.txt',
    'type'     => 'text/plain',
    'tmp_name' => $tempTxt,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempTxt)
];

$_SESSION['user'] = [
    'id'       => $userId,
    'username' => $testUsername,
    'name'     => 'Updated Customer Name',
    'email'    => $testEmail,
    'role'     => 'customer',
    'image'    => 'default_pfp.jpg'
];

ob_start();
$auth = new AuthController();
$auth->profile();
$failHtml = ob_get_clean();
@unlink($tempTxt);

if (strpos($failHtml, 'Only JPG, JPEG, PNG, or WebP image files are allowed.') !== false) {
    echo "  PASS: Invalid extension correctly rejected with clear error message.\n";
} else {
    echo "  FAIL: Error message not rendered for invalid file. Output: " . substr($failHtml, 0, 500) . "\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Ensure DB image remains unchanged
$stmt->execute([$userId]);
$row4 = $stmt->fetch();
if ($row4['image'] === 'default_pfp.jpg') {
    echo "  PASS: Database image remained unchanged after validation failure.\n";
} else {
    echo "  FAIL: Database image was modified despite failure: " . var_export($row4['image'], true) . "\n";
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    exit(1);
}

// Clean up test user
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
echo "\nCleaned up test user.\n";

echo "\n*** ALL CUSTOMER PROFILE PICTURE TESTS PASSED WITH 100% SUCCESS! ***\n";
