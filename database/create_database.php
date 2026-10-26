<?php
/**
 * MediMax Bootstrap - Database Initializer & Migration Runner
 * 
 * Safe idempotency:
 * - If the database does not exist, creates it, creates all tables, and seeds initial data.
 * - If the database already exists with tables, it does nothing and reports that everything is ready.
 * 
 * Can be run via CLI:
 *   php database/create_database.php
 * 
 * Or accessed directly via web browser:
 *   http://localhost/MediMax/database/create_database.php
 */

// Load app configuration
require_once dirname(__DIR__) . '/config/app.php';

$isCli = (php_sapi_name() === 'cli');

$host    = defined('DB_HOST') ? DB_HOST : 'localhost';
$port    = defined('DB_PORT') ? DB_PORT : '3306';
$dbName  = defined('DB_NAME') ? DB_NAME : 'medimax_db';
$user    = defined('DB_USER') ? DB_USER : 'root';
$pass    = defined('DB_PASS') ? DB_PASS : '';
$charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
];

$requiredTables = [
    'users',
    'categories',
    'products',
    'coupons',
    'user_addresses',
    'orders',
    'order_items',
    'payments',
    'coupon_redemptions',
    'cart_items',
    'wishlist',
    'contact_messages'
];

$logs = [];
$status = 'success';
$actionTaken = 'none'; // 'created', 'updated', 'none'
$errorMsg = null;

try {
    // 1. Connect to MySQL server (without selecting the database)
    $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
    $serverPdo = new PDO($serverDsn, $user, $pass, $pdoOptions);
    $logs[] = [
        'type' => 'info',
        'msg'  => "Connected to MySQL server at {$host}:{$port} as user '{$user}'."
    ];

    // 2. Check if the database already exists
    $stmt = $serverPdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbName");
    $stmt->execute(['dbName' => $dbName]);
    $dbExists = (bool)$stmt->fetchColumn();

    if (!$dbExists) {
        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
        $logs[] = [
            'type' => 'success',
            'msg'  => "Database '{$dbName}' was missing. Created new database successfully."
        ];
        $actionTaken = 'created';
    } else {
        $logs[] = [
            'type' => 'info',
            'msg'  => "Database '{$dbName}' already exists."
        ];
    }

    // 3. Connect to the database
    $dbDsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
    $dbPdo = new PDO($dbDsn, $user, $pass, $pdoOptions);

    // 4. Check existing tables
    $existingTables = $dbPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $missingTables = array_diff($requiredTables, $existingTables);

    $needsSchema = (!$dbExists || !empty($missingTables));

    // Also check if users table is populated
    if (!$needsSchema && in_array('users', $existingTables)) {
        $userCount = (int)$dbPdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
        if ($userCount === 0) {
            $needsSchema = true;
        }
    }

    if ($needsSchema) {
        $schemaFile = __DIR__ . '/medimax_schema.sql';
        if (!file_exists($schemaFile)) {
            throw new RuntimeException("Schema file not found at: {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        if (empty($sql)) {
            throw new RuntimeException("Schema file 'medimax_schema.sql' is empty.");
        }

        // Dynamically ensure the schema targets the configured $dbName
        $sqlToExecute = preg_replace('/CREATE DATABASE IF NOT EXISTS `[^`]+`[^;]*;/i', '', $sql);
        $sqlToExecute = preg_replace('/USE `[^`]+`;/i', "USE `{$dbName}`;", $sqlToExecute);

        $dbPdo->exec($sqlToExecute);
        // Clear multi-statement buffer
        while ($dbPdo->query("SELECT 1")->fetch()) { break; }

        $logs[] = [
            'type' => 'success',
            'msg'  => "Executed medimax_schema.sql: verified/created all tables and inserted default seed data."
        ];
        if ($actionTaken !== 'created') {
            $actionTaken = 'updated';
        }
    } else {
        $logs[] = [
            'type' => 'info',
            'msg'  => "All " . count($requiredTables) . " required tables and seed data already exist. No action needed (did nothing)."
        ];
    }

    // 5. Gather current metrics
    $currentTables = $dbPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $userCount     = in_array('users', $currentTables) ? (int)$dbPdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn() : 0;
    $productCount  = in_array('products', $currentTables) ? (int)$dbPdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn() : 0;
    $catCount      = in_array('categories', $currentTables) ? (int)$dbPdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn() : 0;
    $couponCount   = in_array('coupons', $currentTables) ? (int)$dbPdo->query("SELECT COUNT(*) FROM `coupons`")->fetchColumn() : 0;

} catch (Throwable $e) {
    $status = 'error';
    $errorMsg = $e->getMessage();
    $logs[] = [
        'type' => 'danger',
        'msg'  => "Database setup failed: " . $e->getMessage()
    ];
}

// -------------------------------------------------------------
// CLI Output
// -------------------------------------------------------------
if ($isCli) {
    echo "\n======================================================\n";
    echo " MediMax Database Setup & Verification Tool\n";
    echo "======================================================\n\n";

    foreach ($logs as $log) {
        $prefix = match($log['type']) {
            'success' => '[OK]     ',
            'info'    => '[INFO]   ',
            'danger'  => '[ERROR]  ',
            default   => '[LOG]    '
        };
        echo "{$prefix} {$log['msg']}\n";
    }

    if ($status === 'success') {
        echo "\n------------------------------------------------------\n";
        echo " STATUS SUMMARY:\n";
        echo " - Database:   {$dbName} (" . ($actionTaken === 'created' ? 'Newly Created' : 'Already Existed') . ")\n";
        echo " - Tables:     " . count($currentTables) . " verified\n";
        echo " - Categories: {$catCount}\n";
        echo " - Products:   {$productCount}\n";
        echo " - Coupons:    {$couponCount}\n";
        echo " - Users:      {$userCount}\n";
        echo " - Action:     " . ($actionTaken === 'none' ? 'Did nothing (database already exists)' : 'Initialized schema and seeds') . "\n";
        echo "------------------------------------------------------\n";
        echo "\n MediMax database is 100% READY!\n";
        echo " Default Admin: admin@medimax.com / Admin@123\n\n";
        return;
    } else {
        echo "\n[FATAL] Database creation / connection failed: {$errorMsg}\n\n";
        if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'create_database.php') {
            exit(1);
        }
        return;
    }
}

// -------------------------------------------------------------
// Browser HTML Output
// -------------------------------------------------------------
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediMax - Database Setup</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --primary: #2C7A80;
      --primary-dark: #1F5559;
      --bg-cream: #FAF9F5;
      --card-bg: #FFFFFF;
      --text-main: #202428;
      --text-muted: #5A626A;
      --border: #E8E6DF;
    }
    body {
      background: var(--bg-cream);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: var(--text-main);
      padding: 40px 15px;
    }
    .setup-card {
      max-width: 680px;
      margin: 0 auto;
      background: var(--card-bg);
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .setup-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: #fff;
      padding: 30px;
      text-align: center;
    }
    .setup-body {
      padding: 30px;
    }
    .metric-box {
      background: #F8F9FA;
      border: 1px solid #ECECEC;
      border-radius: 10px;
      padding: 14px 18px;
      text-align: center;
    }
    .metric-value {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--primary);
    }
    .metric-label {
      font-size: 0.78rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body>

<div class="setup-card">
  <div class="setup-header">
    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle mb-2" style="width: 56px; height: 56px;">
      <i class="fas fa-database text-primary fs-3"></i>
    </div>
    <h2 class="h4 fw-bold mb-1">MediMax Database Setup</h2>
    <p class="mb-0 opacity-75 small">Automated database initialization &amp; verification</p>
  </div>

  <div class="setup-body">
    <?php if ($status === 'success'): ?>
      <div class="alert <?= $actionTaken === 'none' ? 'alert-info' : 'alert-success' ?> d-flex align-items-center gap-3 mb-4 rounded-3">
        <i class="fas <?= $actionTaken === 'none' ? 'fa-circle-info' : 'fa-circle-check' ?> fs-3"></i>
        <div>
          <strong><?= $actionTaken === 'none' ? 'Database Already Initialized' : 'Database Ready!' ?></strong>
          <div class="small">
            <?= $actionTaken === 'none' 
              ? "Database '{$dbName}' and all required tables were already created. No changes were made (did nothing)." 
              : "Database '{$dbName}' and all tables were created successfully, and default catalogue data was seeded." ?>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
          <div class="metric-box">
            <div class="metric-value"><?= count($currentTables) ?></div>
            <div class="metric-label">Tables</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="metric-box">
            <div class="metric-value"><?= $productCount ?></div>
            <div class="metric-label">Products</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="metric-box">
            <div class="metric-value"><?= $catCount ?></div>
            <div class="metric-label">Categories</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="metric-box">
            <div class="metric-value"><?= $couponCount ?></div>
            <div class="metric-label">Coupons</div>
          </div>
        </div>
      </div>

      <div class="card bg-light border-0 rounded-3 p-3 mb-4">
        <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-key me-2 text-primary"></i>Default Login Credentials</h6>
        <div class="row g-2 small">
          <div class="col-sm-6">
            <strong>Administrator:</strong><br>
            Email: <code>admin@medimax.com</code><br>
            Password: <code>Admin@123</code>
          </div>
          <div class="col-sm-6">
            <strong>Customer:</strong><br>
            Email: <code>user01@medimax.com</code><br>
            Password: <code>User@123</code>
          </div>
        </div>
      </div>

      <h6 class="fw-bold text-muted small text-uppercase mb-2">Process Logs</h6>
      <ul class="list-group list-group-flush small mb-4">
        <?php foreach ($logs as $log): ?>
          <li class="list-group-item px-0 py-1 border-0 text-<?= $log['type'] === 'danger' ? 'danger' : ($log['type'] === 'success' ? 'success' : 'muted') ?>">
            <i class="fas <?= $log['type'] === 'success' ? 'fa-check text-success' : ($log['type'] === 'danger' ? 'fa-xmark text-danger' : 'fa-info-circle text-primary') ?> me-2"></i>
            <?= htmlspecialchars($log['msg']) ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="d-flex gap-2 justify-content-center">
        <a href="<?= BASE_URL ?>" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">
          <i class="fas fa-house me-1"></i> Open Storefront
        </a>
        <a href="<?= BASE_URL ?>?page=admin" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold">
          <i class="fas fa-lock me-1"></i> Admin Console
        </a>
      </div>

    <?php else: ?>
      <div class="alert alert-danger d-flex align-items-center gap-3 mb-4 rounded-3">
        <i class="fas fa-circle-exclamation fs-3"></i>
        <div>
          <strong>Database Setup Failed</strong>
          <div class="small"><?= htmlspecialchars($errorMsg) ?></div>
        </div>
      </div>

      <p class="small text-muted mb-4">
        Please ensure your MySQL server is running in the XAMPP Control Panel and check the database connection credentials in <code>config/app.php</code>.
      </p>

      <div class="text-center">
        <button onclick="location.reload()" class="btn btn-primary px-4 rounded-pill">
          <i class="fas fa-rotate me-1"></i> Retry Setup
        </button>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
