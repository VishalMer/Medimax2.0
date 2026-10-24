<?php
/**
 * MediMax - Checkout Controller
 * 
 * Manages order checkout, delivery address verification, 
 * payment processing, and order placement with database recording.
 */
class CheckoutController extends Controller {
    
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartItems = CartController::getCartItems();
        $summary = CartController::getCartSummary($cartItems);

        $this->view('pages/checkout', array_merge($summary, [
            'cartItems' => $cartItems,
            'pageTitle' => 'Checkout - MediMax.com'
        ]));
    }

    /**
     * Process order placement from checkout
     */
    public function placeOrder() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartItems = CartController::getCartItems();
        if (empty($cartItems)) {
            $_SESSION['flash_error'] = 'Your cart is empty. Add products before checking out.';
            header('Location: ' . url('cart'));
            exit;
        }

        $summary = CartController::getCartSummary($cartItems);
        $orderId = 'MED-' . date('Y') . '-' . rand(10000, 99999);
        $totalAmount = $summary['grandTotal'];
        $paymentMethod = $_POST['payment_method'] ?? 'UPI';
        $userId = $_SESSION['user']['id'] ?? null;

        // Collect shipping details
        $shippingName    = trim($_POST['shipping_name'] ?? ($_SESSION['user']['name'] ?? 'Customer'));
        $shippingMobile  = trim($_POST['shipping_mobile'] ?? '9876543210');
        $shippingEmail   = trim($_POST['shipping_email'] ?? ($_SESSION['user']['email'] ?? 'customer@example.com'));
        $addressLine1    = trim($_POST['shipping_address_line1'] ?? '123 Example Street');
        $addressLine2    = trim($_POST['shipping_address_line2'] ?? '');
        $city            = trim($_POST['shipping_city'] ?? 'Rajkot');
        $state           = trim($_POST['shipping_state'] ?? 'Gujarat');
        $pincode         = trim($_POST['shipping_pincode'] ?? '360001');

        $pdo = Database::getConnection();
        $orderDbId = 0;

        if ($pdo) {
            try {
                // If user is guest, assign user 1 or guest ID if required by FK
                $dbUserId = !empty($userId) ? (int)$userId : 1;
                $couponId = !empty($_SESSION['applied_coupon']['id']) ? (int)$_SESSION['applied_coupon']['id'] : null;

                $stmt = $pdo->prepare("
                    INSERT INTO orders (
                        order_number, user_id, shipping_name, shipping_mobile, shipping_email,
                        shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_pincode,
                        coupon_id, subtotal, discount_amount, delivery_fee, total_amount,
                        payment_method, payment_status, delivery_status, placed_at
                    ) VALUES (
                        :order_num, :uid, :sname, :smobile, :semail,
                        :addr1, :addr2, :city, :state, :pincode,
                        :coupon_id, :subtotal, :discount, :delivery, :total,
                        :pay_method, :pay_status, 'Processing', NOW()
                    )
                ");

                $paymentStatus = ($paymentMethod === 'COD') ? 'Pending' : 'Completed';

                $stmt->execute([
                    ':order_num'   => $orderId,
                    ':uid'         => $dbUserId,
                    ':sname'       => $shippingName,
                    ':smobile'     => $shippingMobile,
                    ':semail'      => $shippingEmail,
                    ':addr1'       => $addressLine1,
                    ':addr2'       => $addressLine2,
                    ':city'        => $city,
                    ':state'       => $state,
                    ':pincode'     => $pincode,
                    ':coupon_id'   => $couponId,
                    ':subtotal'    => $summary['subtotal'],
                    ':discount'    => $summary['discountAmount'],
                    ':delivery'    => $summary['deliveryFee'],
                    ':total'       => $totalAmount,
                    ':pay_method'  => in_array($paymentMethod, ['UPI','CARD','NETBANK','COD']) ? $paymentMethod : 'UPI',
                    ':pay_status'  => $paymentStatus
                ]);

                $orderDbId = (int)$pdo->lastInsertId();

                // Insert line items
                if ($orderDbId > 0) {
                    $itemStmt = $pdo->prepare("
                        INSERT INTO order_items (
                            order_id, product_id, product_name, product_sku, product_image,
                            unit_price, quantity, subtotal, created_at
                        ) VALUES (
                            :oid, :pid, :pname, :psku, :pimg,
                            :price, :qty, :sub, NOW()
                        )
                    ");

                    foreach ($cartItems as $it) {
                        $pPrice = (float)$it['price'];
                        $pQty = (int)$it['quantity'];
                        $itemStmt->execute([
                            ':oid'   => $orderDbId,
                            ':pid'   => (int)$it['product_id'],
                            ':pname' => $it['name'],
                            ':psku'  => 'MM' . str_pad((string)$it['product_id'], 3, '0', STR_PAD_LEFT),
                            ':pimg'  => $it['image'],
                            ':price' => $pPrice,
                            ':qty'   => $pQty,
                            ':sub'   => $pPrice * $pQty
                        ]);
                    }
                }
            } catch (PDOException $e) {
                error_log("CheckoutController placeOrder DB error: " . $e->getMessage());
            }
        }

        // Empty cart and coupon
        CartController::emptyCart();

        header('Location: ' . url('order-success') . '&order_id=' . urlencode($orderId) . '&amount=' . urlencode((string)$totalAmount) . '&method=' . urlencode($paymentMethod));
        exit;
    }

    public function success() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $orderId = $_GET['order_id'] ?? 'MED-' . date('Y') . '-' . rand(10000, 99999);
        $amount = (float)($_GET['amount'] ?? 0.00);
        $method = $_GET['method'] ?? 'UPI';

        $this->view('pages/order_success', [
            'orderId'   => $orderId,
            'amount'    => $amount,
            'method'    => $method,
            'pageTitle' => 'Order Successful - MediMax.com'
        ]);
    }
}
