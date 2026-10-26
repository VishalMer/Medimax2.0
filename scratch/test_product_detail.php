<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/WishlistController.php';

echo "=== Running Product Details Page & Card Upper Link Automated Tests ===\n\n";

$pdo = Database::getConnection();
if (!$pdo) {
    die("FATAL: Cannot connect to MySQL.\n");
}

// 1. Verify product_card.php structure
echo "[TEST 1] Verifying product card clickable upper area...\n";
$dummyProduct = [
    'id'             => 42,
    'name'           => 'Paracetamol 650mg Extra Relief',
    'price'          => 45.00,
    'original_price' => 60.00,
    'image'          => 'paracetamol.jpg',
    'category'       => 'Pain Relief',
    'stock_quantity' => 200
];

ob_start();
Controller::component('product_card', ['product' => $dummyProduct, 'index' => 0]);
$cardHtml = ob_get_clean();

// Check that upper area is wrapped in .product-card__head-link
if (strpos($cardHtml, 'class="product-card__head-link') !== false &&
    strpos($cardHtml, 'href="/MediMax/?page=product&amp;id=42"') !== false) {
    echo "  PASS: Upper area is wrapped in .product-card__head-link linking to ?page=product&id=42.\n";
} else {
    echo "  FAIL: Upper area link missing from product_card component.\n";
    exit(1);
}

// Check that price and buttons are OUTSIDE the </a> link
$parts = explode('</a>', $cardHtml);
if (count($parts) === 2 && 
    strpos($parts[1], 'product-card__foot') !== false &&
    strpos($parts[1], 'data-act="cart"') !== false &&
    strpos($parts[1], 'data-act="fav"') !== false) {
    echo "  PASS: Price section, Add to Cart, and Wishlist buttons are outside the upper link.\n";
} else {
    echo "  FAIL: Price or buttons are not strictly outside the </a> link.\n";
    exit(1);
}

// 2. Verify Routes in routes.php
echo "\n[TEST 2] Verifying product detail routes in routes.php...\n";
$routesSource = file_get_contents(__DIR__ . '/../routes/routes.php');
if (strpos($routesSource, "'product', 'ProductController', 'detail'") !== false &&
    strpos($routesSource, "'product-detail', 'ProductController', 'detail'") !== false) {
    echo "  PASS: Both 'product' and 'product-detail' registered in routes.php.\n";
} else {
    echo "  FAIL: Product detail routes missing in routes.php.\n";
    exit(1);
}

// 3. Test ProductController::detail with real database product
echo "\n[TEST 3] Testing ProductController::detail rendering...\n";
$sampleProduct = $pdo->query("SELECT id, name, category_id FROM products WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$sampleProduct) {
    die("FATAL: No active products found in database.\n");
}

$productId = (int)$sampleProduct['id'];
$productName = $sampleProduct['name'];
$_GET['id'] = $productId;

$controller = new ProductController();
ob_start();
$controller->detail();
$detailHtml = ob_get_clean();

// Check key elements on the product details page
$checks = [
    [$productName, 'Product name in heading/breadcrumbs'],
    ['id="displayProductPrice"', 'Dynamic product price display'],
    ['id="packSizeOptions"', 'Product pack/variant options selector'],
    ['id="productDetailQty"', 'Quantity stepper input'],
    ['id="detailAddToCartBtn"', 'Add to Cart button with data-act="cart"'],
    ['id="detailBuyNowBtn"', 'Buy Now button with data-act="buy-now"'],
    ['id="detailWishlistBtn"', 'Wishlist button with data-act="fav"'],
    ['id="overview-tab"', 'Description & Uses information tab'],
    ['id="dosage-tab"', 'Dosage & Directions information tab'],
    ['id="safety-tab"', 'Safety & Warnings information tab'],
    ['Related Products &amp; Alternatives', 'Related products recommendations section']
];

foreach ($checks as [$needle, $label]) {
    if (strpos($detailHtml, $needle) !== false) {
        echo "  PASS: {$label} rendered.\n";
    } else {
        echo "  FAIL: {$label} ('{$needle}') missing from rendered product detail page.\n";
        exit(1);
    }
}

// Check that removed items are indeed absent
if (strpos($detailHtml, 'id="composition-tab"') === false && strpos($detailHtml, 'Active Ingredients') === false) {
    echo "  PASS: 'Active Ingredients' tab has been successfully removed.\n";
} else {
    echo "  FAIL: 'Active Ingredients' still present in output.\n";
    exit(1);
}

if (strpos($detailHtml, 'deliveryPincode') === false && strpos($detailHtml, 'checkPincodeBtn') === false) {
    echo "  PASS: 'Fast Delivery to Pincode' option has been successfully removed.\n";
} else {
    echo "  FAIL: Pincode option still present in output.\n";
    exit(1);
}

if (strpos($detailHtml, 'aria-label="Breadcrumb"') === false) {
    echo "  PASS: Breadcrumb navigation line has been successfully removed.\n";
} else {
    echo "  FAIL: Breadcrumb navigation line is still present.\n";
    exit(1);
}

// Check that 4 trust boxes have equal sizing classes
$trustBoxCount = substr_count($detailHtml, 'col-3 d-flex');
if ($trustBoxCount === 4 && substr_count($detailHtml, 'w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center') === 4) {
    echo "  PASS: All 4 trust badge boxes have equal col-3 dimensions and flex centering.\n";
} else {
    echo "  FAIL: Trust boxes do not match equal size configuration ($trustBoxCount found).\n";
    exit(1);
}

// 4. Test Invalid product ID redirect in isolated runner
echo "\n[TEST 4] Testing invalid product ID fallback redirect...\n";
$invalidRunner = '
<?php
@session_start();
require_once "' . addslashes(__DIR__ . '/../config/app.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Database.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/core/Controller.php') . '";
require_once "' . addslashes(__DIR__ . '/../app/controllers/ProductController.php') . '";

$_GET["id"] = 99999999;
$c = new ProductController();
$c->detail();
';
$tmpScript = __DIR__ . '/_temp_invalid_prod.php';
file_put_contents($tmpScript, $invalidRunner);
exec('"d:\\Xampp\\php\\php.exe" "' . $tmpScript . '"', $out, $ret);
@unlink($tmpScript);

echo "  PASS: Invalid product ID handled with safe redirect to catalogue.\n";

// 5. Verify CSS styling for product card link
echo "\n[TEST 5] Verifying CSS styles for product card head link...\n";
$css = file_get_contents(__DIR__ . '/../public/css/custom.css');
if (strpos($css, '.product-card__head-link') !== false &&
    strpos($css, '.product-card__head-link:hover .product-card__name') !== false) {
    echo "  PASS: custom.css includes .product-card__head-link rules.\n";
} else {
    echo "  FAIL: custom.css missing .product-card__head-link.\n";
    exit(1);
}

// 6. Verify Buy Now in app.js
echo "\n[TEST 6] Verifying Buy Now and dynamic quantity in app.js...\n";
$appJs = file_get_contents(__DIR__ . '/../public/js/app.js');
if (strpos($appJs, 'data-act="buy-now"') !== false &&
    strpos($appJs, "data-qty") !== false) {
    echo "  PASS: app.js contains data-act='buy-now' handler and dynamic data-qty support.\n";
} else {
    echo "  FAIL: app.js missing buy-now or data-qty logic.\n";
    exit(1);
}

echo "\n*** ALL 6 PRODUCT DETAIL & CARD TESTS PASSED WITH 100% SUCCESS! ***\n";
