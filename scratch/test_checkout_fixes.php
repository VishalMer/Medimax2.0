<?php
@session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/CheckoutController.php';

echo "=== Running Checkout Page Fixes Automated Test Suite ===\n\n";

// Mock user session and cart items
$_SESSION['cart'] = [
    1 => 2,
    2 => 1
];
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'name' => 'Admin',
    'email' => 'admin@medimax.com'
];

$controller = new CheckoutController();
ob_start();
$controller->index();
$html = ob_get_clean();

// 1. Verify heading overlap fix (no pt-4 override on .page)
echo "[TEST 1] Verifying heading overlap fix...\n";
if (strpos($html, 'class="page bg-light pb-5"') !== false &&
    strpos($html, 'pt-4 pb-5') === false) {
    echo "  PASS: pt-4 override removed from .page, preserving header offset padding.\n";
} else {
    echo "  FAIL: pt-4 override still present on .page.\n";
    exit(1);
}

// 2. Verify white text on 1. Delivery Information
echo "\n[TEST 2] Verifying text color of '1. Delivery Information'...\n";
if (strpos($html, '1. Delivery Information') !== false &&
    strpos($html, 'color: #ffffff !important;">1. Delivery Information</h4>') !== false) {
    echo "  PASS: '1. Delivery Information' has explicit white text.\n";
} else {
    echo "  FAIL: '1. Delivery Information' missing white text style.\n";
    exit(1);
}

// 3. Verify white text on 2. Payment Method
echo "\n[TEST 3] Verifying text color of '2. Payment Method'...\n";
if (strpos($html, '2. Payment Method') !== false &&
    strpos($html, 'color: #ffffff !important;">2. Payment Method</h4>') !== false) {
    echo "  PASS: '2. Payment Method' has explicit white text.\n";
} else {
    echo "  FAIL: '2. Payment Method' missing white text style.\n";
    exit(1);
}

// 4. Verify white text on Order Summary
echo "\n[TEST 4] Verifying text color of 'Order Summary'...\n";
if (strpos($html, 'Order Summary') !== false &&
    strpos($html, 'color: #ffffff !important;">Order Summary</h4>') !== false) {
    echo "  PASS: 'Order Summary' has explicit white text.\n";
} else {
    echo "  FAIL: 'Order Summary' missing white text style.\n";
    exit(1);
}

// 5. Verify Place Order button styling
echo "\n[TEST 5] Verifying Place Order button styling...\n";
if (strpos($html, 'id="placeOrderBtn"') !== false &&
    strpos($html, 'btn-mm btn-mm-amber btn-mm-block btn-mm-lg') !== false &&
    strpos($html, 'background-color: var(--amber, #E67834)') !== false) {
    echo "  PASS: Place Order button is styled with MediMax amber pill button classes.\n";
} else {
    echo "  FAIL: Place Order button missing required btn-mm classes.\n";
    exit(1);
}

// 6. Verify form binding
echo "\n[TEST 6] Verifying form attribute binding and validation script...\n";
if (strpos($html, 'form="checkoutForm"') !== false &&
    strpos($html, 'action="/MediMax/?page=checkout/place-order"') !== false) {
    echo "  PASS: Place Order button correctly targets checkoutForm.\n";
} else {
    echo "  FAIL: Button missing form attribute binding.\n";
    exit(1);
}

// 7. Verify Saved Address Cards styling & hover rules
echo "\n[TEST 7] Verifying Saved Address Cards styling & hover rules...\n";
$css = file_get_contents(__DIR__ . '/../public/css/custom.css');
if (strpos($html, 'class="saved-addr-card saved-addr-btn"') !== false &&
    strpos($html, 'saved-addr-btn btn-outline-secondary') === false &&
    strpos($css, '.saved-addr-card:hover') !== false &&
    strpos($css, '.saved-addr-card.is-selected') !== false) {
    echo "  PASS: Saved address buttons upgraded to .saved-addr-card with dedicated hover and selection styling.\n";
} else {
    echo "  FAIL: Saved address buttons missing .saved-addr-card or CSS hover rules.\n";
    exit(1);
}

echo "\n*** ALL 7 CHECKOUT TESTS PASSED WITH 100% SUCCESS! ***\n";
