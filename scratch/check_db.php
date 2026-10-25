<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';

$pdo = Database::getConnection();
$cols = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in products:\n";
foreach ($cols as $c) {
    echo "  {$c['Field']} ({$c['Type']})\n";
}

$sample = $pdo->query("SELECT * FROM products LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
echo "\nSample product:\n";
print_r($sample);
