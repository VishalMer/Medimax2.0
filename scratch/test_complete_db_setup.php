<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';

echo "=== Running Full MediMax Database Creation & Idempotency Test Suite ===\n\n";

$serverPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// -------------------------------------------------------------
// [TEST 1] CLI Run of create_database.php on Existing Database
// -------------------------------------------------------------
echo "[TEST 1] Running database/create_database.php on existing database...\n";
exec('"d:\\Xampp\\php\\php.exe" "' . addslashes(dirname(__DIR__) . '/database/create_database.php') . '"', $cliOut, $cliRet);
$cliText = implode("\n", $cliOut);

if ($cliRet === 0 && strpos($cliText, 'Did nothing (database already exists)') !== false) {
    echo "  PASS: CLI execution succeeded with zero exit code and recognized existing database.\n";
} else {
    echo "  FAIL: CLI run failed or did not recognize existing database:\n$cliText\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 2] Verify Database Schema file exists and is valid
// -------------------------------------------------------------
echo "\n[TEST 2] Verifying database/medimax_schema.sql integrity...\n";
$schemaFile = dirname(__DIR__) . '/database/medimax_schema.sql';
if (file_exists($schemaFile) && filesize($schemaFile) > 10000) {
    $schemaContent = file_get_contents($schemaFile);
    if (strpos($schemaContent, 'CREATE TABLE IF NOT EXISTS `users`') !== false &&
        strpos($schemaContent, 'CREATE TABLE IF NOT EXISTS `products`') !== false &&
        strpos($schemaContent, 'CREATE TABLE IF NOT EXISTS `categories`') !== false &&
        strpos($schemaContent, 'CREATE TABLE IF NOT EXISTS `coupons`') !== false &&
        strpos($schemaContent, 'CREATE TABLE IF NOT EXISTS `orders`') !== false) {
        echo "  PASS: medimax_schema.sql contains all necessary table definitions (" . strlen($schemaContent) . " bytes).\n";
    } else {
        echo "  FAIL: medimax_schema.sql missing core table DDLs.\n";
        exit(1);
    }
} else {
    echo "  FAIL: medimax_schema.sql missing or too small.\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 3] Simulation of Fresh Clone on New PC (No Database Exists)
// -------------------------------------------------------------
echo "\n[TEST 3] Simulating completely fresh PC with brand new database...\n";
$freshDbName = 'medimax_fresh_pc_' . time();
$serverPdo->exec("DROP DATABASE IF EXISTS `{$freshDbName}`");

$freshRunnerCode = '<?php
define("DB_HOST", "' . DB_HOST . '");
define("DB_PORT", "' . DB_PORT . '");
define("DB_NAME", "' . $freshDbName . '");
define("DB_USER", "' . DB_USER . '");
define("DB_PASS", "' . DB_PASS . '");
define("DB_CHARSET", "' . DB_CHARSET . '");
require_once "' . addslashes(dirname(__DIR__) . '/database/create_database.php') . '";
';
$tempScript = __DIR__ . '/_temp_fresh_pc_run.php';
file_put_contents($tempScript, $freshRunnerCode);

exec('"d:\\Xampp\\php\\php.exe" "' . $tempScript . '"', $freshOut, $freshRet);
@unlink($tempScript);
$freshText = implode("\n", $freshOut);

if ($freshRet !== 0 || strpos($freshText, 'Initialized schema and seeds') === false) {
    echo "  FAIL: Fresh database creation failed:\n$freshText\n";
    $serverPdo->exec("DROP DATABASE IF EXISTS `{$freshDbName}`");
    exit(1);
}

// Inspect created database
$freshConn = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname={$freshDbName}", DB_USER, DB_PASS);
$createdTables = $freshConn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$freshProdCount = (int)$freshConn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$freshUserCount = (int)$freshConn->query("SELECT COUNT(*) FROM users")->fetchColumn();

if (count($createdTables) === 12 && $freshProdCount === 39 && $freshUserCount >= 3) {
    echo "  PASS: Brand new database created with 12 tables, 39 products, and default users.\n";
} else {
    echo "  FAIL: Missing tables or records in fresh database (tables=" . count($createdTables) . ", prods=$freshProdCount, users=$freshUserCount)\n";
    $serverPdo->exec("DROP DATABASE IF EXISTS `{$freshDbName}`");
    exit(1);
}

// -------------------------------------------------------------
// [TEST 4] Test Automatic Creation via Database::getConnection() on blank database
// -------------------------------------------------------------
echo "\n[TEST 4] Testing automatic creation via Database::getConnection() (First page visit)...\n";
$autoDbName = 'medimax_auto_visit_' . time();
$serverPdo->exec("DROP DATABASE IF EXISTS `{$autoDbName}`");

$autoRunnerCode = '<?php
define("DB_HOST", "' . DB_HOST . '");
define("DB_PORT", "' . DB_PORT . '");
define("DB_NAME", "' . $autoDbName . '");
define("DB_USER", "' . DB_USER . '");
define("DB_PASS", "' . DB_PASS . '");
define("DB_CHARSET", "' . DB_CHARSET . '");
require_once "' . addslashes(dirname(__DIR__) . '/app/core/Database.php') . '";

$pdo = Database::getConnection();
if ($pdo) {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES:" . count($tables) . "\n";
    echo "USERS:" . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
    echo "PRODUCTS:" . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
} else {
    echo "FAIL: No connection\n";
}
';
$autoScript = __DIR__ . '/_temp_auto_run.php';
file_put_contents($autoScript, $autoRunnerCode);

exec('"d:\\Xampp\\php\\php.exe" "' . $autoScript . '"', $autoOut, $autoRet);
@unlink($autoScript);
$autoText = implode("\n", $autoOut);

if (strpos($autoText, 'TABLES:12') !== false && strpos($autoText, 'PRODUCTS:39') !== false) {
    echo "  PASS: Database::getConnection() automatically created and seeded the database seamlessly.\n";
} else {
    echo "  FAIL: Database::getConnection() automatic creation failed:\n$autoText\n";
    $serverPdo->exec("DROP DATABASE IF EXISTS `{$freshDbName}`");
    $serverPdo->exec("DROP DATABASE IF EXISTS `{$autoDbName}`");
    exit(1);
}

// Cleanup temporary test databases
$serverPdo->exec("DROP DATABASE IF EXISTS `{$freshDbName}`");
$serverPdo->exec("DROP DATABASE IF EXISTS `{$autoDbName}`");
echo "  PASS: Test databases cleaned up safely.\n";

// -------------------------------------------------------------
// [TEST 5] Test Router integration for ?page=setup-db
// -------------------------------------------------------------
echo "\n[TEST 5] Testing router integration for ?page=setup-db...\n";
$router = new Router();
require dirname(__DIR__) . '/routes/routes.php';

$_GET['page'] = 'setup-db';
ob_start();
$router->dispatch();
$routeOutput = ob_get_clean();

if (strpos($routeOutput, 'MediMax Database Setup') !== false ||
    strpos($routeOutput, 'STATUS SUMMARY') !== false) {
    echo "  PASS: ?page=setup-db dispatches to the database setup interface.\n";
} else {
    echo "  FAIL: ?page=setup-db failed to render setup view.\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 6] Verify Default User Credentials in medimax_db
// -------------------------------------------------------------
echo "\n[TEST 6] Verifying default credentials in medimax_db...\n";
$livePdo = Database::getConnection();
$adminUser = $livePdo->query("SELECT * FROM users WHERE email = 'admin@medimax.com'")->fetch(PDO::FETCH_ASSOC);
if ($adminUser && password_verify('Admin@123', $adminUser['password'])) {
    echo "  PASS: Default admin account exists with verified password 'Admin@123' (role: {$adminUser['role']}).\n";
} else {
    echo "  FAIL: Default admin user missing or invalid password.\n";
    exit(1);
}

$custUser = $livePdo->query("SELECT * FROM users WHERE email = 'user01@medimax.com'")->fetch(PDO::FETCH_ASSOC);
if ($custUser && password_verify('User@123', $custUser['password'])) {
    echo "  PASS: Default customer account exists with verified password 'User@123'.\n";
} else {
    echo "  FAIL: Default customer user missing or invalid password.\n";
    exit(1);
}

echo "\n*** ALL 6 DATABASE CREATION & SETUP TESTS PASSED WITH 100% SUCCESS! ***\n";
