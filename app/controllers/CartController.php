<?php
/**
 * MediMax - Shopping Cart Controller
 * 
 * Manages shopping cart items, quantities, coupon discounts,
 * transitions between cart and wishlist, and live checkout synchronization.
 */
class CartController extends Controller {

    // Threshold for free chemist delivery in INR
    public const FREE_DELIVERY_THRESHOLD = 499.00;
    // Standard flat delivery fee when below threshold
    public const STANDARD_DELIVERY_FEE = 49.00;
    // Maximum allowable quantity per medicine item per order
    public const MAX_ITEM_QUANTITY = 10;

    /**
     * Display the shopping cart page
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);

        $this->view('pages/cart', array_merge($summary, [
            'cartItems' => $cartItems,
            'pageTitle' => 'Shopping Cart - MediMax.com'
        ]));
    }

    /**
     * Add product to cart (or increment quantity if already in cart)
     */
    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1));
        $fromWishlist = !empty($_POST['from_wishlist']) || !empty($_GET['from_wishlist']);

        $pdo = Database::getConnection();
        $product = null;
        if ($productId > 0 && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, price, original_price, image, stock_quantity, is_active FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
                $stmt->execute([':id' => $productId]);
                $product = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("CartController add product query error: " . $e->getMessage());
            }
        }

        if (!$product) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Product not found or unavailable.'], 404);
            }
            $_SESSION['flash_error'] = 'Product not found or unavailable.';
            $this->redirectBack();
        }

        $stock = (int)($product['stock_quantity'] ?? 0);
        if ($stock <= 0) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Sorry, "' . $product['name'] . '" is currently out of stock.'], 400);
            }
            $_SESSION['flash_error'] = 'Sorry, "' . $product['name'] . '" is currently out of stock.';
            $this->redirectBack();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $sessionId = session_id();

        // Check existing quantity of this product in cart
        $currentCartQty = self::getProductQuantityInCart($productId);
        $allowedMax = min(self::MAX_ITEM_QUANTITY, $stock);

        if ($currentCartQty >= $allowedMax) {
            $msg = "You already have the maximum allowed limit ({$allowedMax} units) of this product in your cart.";
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $msg], 400);
            }
            $_SESSION['flash_error'] = $msg;
            $this->redirectBack();
        }

        // Adjust addition if it exceeds allowed maximum
        if (($currentCartQty + $quantity) > $allowedMax) {
            $quantity = $allowedMax - $currentCartQty;
        }

        if ($pdo) {
            try {
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                        VALUES (:uid, :sid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = LEAST(:max_qty, quantity + :qty2)
                    ");
                    $stmt->execute([
                        ':uid'      => (int)$userId,
                        ':sid'      => $sessionId,
                        ':pid'      => $productId,
                        ':qty'      => $quantity,
                        ':max_qty'  => $allowedMax,
                        ':qty2'     => $quantity
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                        VALUES (NULL, :sid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = LEAST(:max_qty, quantity + :qty2)
                    ");
                    $stmt->execute([
                        ':sid'      => $sessionId,
                        ':pid'      => $productId,
                        ':qty'      => $quantity,
                        ':max_qty'  => $allowedMax,
                        ':qty2'     => $quantity
                    ]);
                }
            } catch (PDOException $e) {
                error_log("CartController add DB error: " . $e->getMessage());
                // Fallback to session
                if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                $_SESSION['cart'][$productId] = min($allowedMax, ($_SESSION['cart'][$productId] ?? 0) + $quantity);
            }
        } else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $_SESSION['cart'][$productId] = min($allowedMax, ($_SESSION['cart'][$productId] ?? 0) + $quantity);
        }

        // If moved from wishlist, remove from wishlist
        if ($fromWishlist) {
            WishlistController::removeProduct($productId);
            $msg = 'Moved "' . $product['name'] . '" to your cart.';
        } else {
            $msg = 'Added "' . $product['name'] . '" to your cart.';
        }

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success'      => true,
                'action'       => 'added',
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'message'      => $msg
            ]));
        }

        $_SESSION['flash_success'] = $msg;
        if ($fromWishlist) {
            header('Location: ' . url('wishlist'));
        } else {
            $this->redirectBack();
        }
        exit;
    }

    /**
     * Update quantity of a line item
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartId = (int)($_POST['cart_id'] ?? $_GET['cart_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);

        $pdo = Database::getConnection();
        $userId = $_SESSION['user']['id'] ?? null;
        $sessionId = session_id();

        // If quantity is zero or negative, remove the item
        if ($quantity <= 0) {
            return $this->remove();
        }

        // Find the product to verify stock limits
        $stock = self::MAX_ITEM_QUANTITY;
        if ($pdo) {
            try {
                if ($cartId > 0) {
                    $qStmt = $pdo->prepare("
                        SELECT ci.id, ci.product_id, p.stock_quantity, p.name 
                        FROM cart_items ci 
                        JOIN products p ON ci.product_id = p.id 
                        WHERE ci.id = :id LIMIT 1
                    ");
                    $qStmt->execute([':id' => $cartId]);
                    $row = $qStmt->fetch();
                    if ($row) {
                        $productId = (int)$row['product_id'];
                        $stock = (int)$row['stock_quantity'];
                    }
                } elseif ($productId > 0) {
                    $pStmt = $pdo->prepare("SELECT stock_quantity, name FROM products WHERE id = :pid LIMIT 1");
                    $pStmt->execute([':pid' => $productId]);
                    $row = $pStmt->fetch();
                    if ($row) {
                        $stock = (int)$row['stock_quantity'];
                    }
                }
            } catch (PDOException $e) {
                error_log("CartController update stock lookup error: " . $e->getMessage());
            }
        }

        $cappedQty = max(1, min($quantity, min(self::MAX_ITEM_QUANTITY, $stock)));

        if ($pdo && $cartId > 0) {
            try {
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id AND user_id = :uid");
                    $stmt->execute([':qty' => $cappedQty, ':id' => $cartId, ':uid' => (int)$userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id AND session_id = :sid");
                    $stmt->execute([':qty' => $cappedQty, ':id' => $cartId, ':sid' => $sessionId]);
                }
            } catch (PDOException $e) {
                error_log("CartController update error: " . $e->getMessage());
            }
        } elseif ($productId > 0 && isset($_SESSION['cart'])) {
            $_SESSION['cart'][$productId] = $cappedQty;
        }

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);

        // Find the line subtotal for the updated item
        $lineSubtotal = 0.0;
        foreach ($cartItems as $it) {
            if (($cartId > 0 && (int)$it['id'] === $cartId) || ($productId > 0 && (int)$it['product_id'] === $productId)) {
                $lineSubtotal = (float)$it['price'] * (int)$it['quantity'];
                break;
            }
        }

        $msg = ($quantity > $cappedQty)
            ? "Quantity adjusted to maximum available stock ({$cappedQty} units)."
            : "Cart updated successfully.";

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success'       => true,
                'action'        => 'updated',
                'cart_id'       => $cartId,
                'product_id'    => $productId,
                'quantity'      => $cappedQty,
                'line_subtotal' => $lineSubtotal,
                'message'       => $msg
            ]));
        }

        $_SESSION['flash_success'] = $msg;
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Remove item from cart
     */
    public function remove() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartId = (int)($_POST['cart_id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();
                if ($cartId > 0) {
                    if (!empty($userId)) {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND user_id = :uid");
                        $stmt->execute([':id' => $cartId, ':uid' => (int)$userId]);
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND session_id = :sid");
                        $stmt->execute([':id' => $cartId, ':sid' => $sessionId]);
                    }
                } elseif ($productId > 0) {
                    if (!empty($userId)) {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE product_id = :pid AND user_id = :uid");
                        $stmt->execute([':pid' => $productId, ':uid' => (int)$userId]);
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE product_id = :pid AND session_id = :sid");
                        $stmt->execute([':pid' => $productId, ':sid' => $sessionId]);
                    }
                }
            } catch (PDOException $e) {
                error_log("CartController remove error: " . $e->getMessage());
            }
        }

        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success'    => true,
                'action'     => 'removed',
                'cart_id'    => $cartId,
                'product_id' => $productId,
                'is_empty'   => empty($cartItems),
                'message'    => 'Item removed from your cart.'
            ]));
        }

        $_SESSION['flash_success'] = 'Item removed from your cart.';
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Move product from cart to wishlist
     */
    public function moveToWishlist() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartId = (int)($_POST['cart_id'] ?? $_GET['cart_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        $pdo = Database::getConnection();
        $productName = 'Product';

        // Resolve product_id from cart_id if needed
        if ($productId <= 0 && $cartId > 0 && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT ci.product_id, p.name FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = :id LIMIT 1");
                $stmt->execute([':id' => $cartId]);
                $row = $stmt->fetch();
                if ($row) {
                    $productId = (int)$row['product_id'];
                    $productName = $row['name'];
                }
            } catch (PDOException $e) {
                error_log("CartController moveToWishlist lookup error: " . $e->getMessage());
            }
        }

        if ($productId > 0) {
            // 1. Add to wishlist
            $userId = $_SESSION['user']['id'] ?? null;
            if (!empty($userId) && $pdo) {
                try {
                    $wStmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:uid, :pid)");
                    $wStmt->execute([':uid' => (int)$userId, ':pid' => $productId]);
                } catch (PDOException $e) {
                    error_log("CartController moveToWishlist add error: " . $e->getMessage());
                }
            } else {
                if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
                    $_SESSION['wishlist'] = [];
                }
                if (!in_array($productId, $_SESSION['wishlist'])) {
                    $_SESSION['wishlist'][] = $productId;
                }
            }
            WishlistController::clearCache();

            // 2. Remove from cart
            if ($cartId > 0 && $pdo) {
                try {
                    $sessionId = session_id();
                    if (!empty($userId)) {
                        $dStmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND user_id = :uid");
                        $dStmt->execute([':id' => $cartId, ':uid' => (int)$userId]);
                    } else {
                        $dStmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND session_id = :sid");
                        $dStmt->execute([':id' => $cartId, ':sid' => $sessionId]);
                    }
                } catch (PDOException $e) {
                    error_log("CartController moveToWishlist delete error: " . $e->getMessage());
                }
            } elseif ($productId > 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);
        $wishlistCount = WishlistController::getCount();

        $msg = 'Saved "' . $productName . '" to your wishlist.';

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success'        => true,
                'action'         => 'moved_to_wishlist',
                'cart_id'        => $cartId,
                'product_id'     => $productId,
                'wishlist_count' => $wishlistCount,
                'is_empty'       => empty($cartItems),
                'message'        => $msg
            ]));
        }

        $_SESSION['flash_success'] = $msg;
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Programmatically empty cart items and reset coupon session
     */
    public static function emptyCart(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :uid");
                    $stmt->execute([':uid' => (int)$userId]);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = :sid");
                    $stmt->execute([':sid' => $sessionId]);
                }
            } catch (PDOException $e) {
                error_log("CartController emptyCart error: " . $e->getMessage());
            }
        }

        $_SESSION['cart'] = [];
        unset($_SESSION['applied_coupon']);
    }

    /**
     * Clear all items in cart (web endpoint)
     */
    public function clear() {
        self::emptyCart();

        $summary = self::getCartSummary([]);

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success'  => true,
                'action'   => 'cleared',
                'is_empty' => true,
                'message'  => 'Cart has been emptied.'
            ]));
        }

        $_SESSION['flash_success'] = 'Cart has been emptied.';
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Apply coupon discount code
     */
    public function applyCoupon() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $code = strtoupper(trim($_POST['coupon_code'] ?? $_POST['code'] ?? $_GET['coupon_code'] ?? $_GET['code'] ?? ''));

        if (empty($code)) {
            $this->handleCouponError('Please enter a coupon code.');
        }

        $cartItems = self::getCartItems();
        if (empty($cartItems)) {
            $this->handleCouponError('Your cart is empty. Add products before applying a coupon.');
        }

        $subtotal = 0.0;
        foreach ($cartItems as $it) {
            $subtotal += (float)$it['price'] * (int)$it['quantity'];
        }

        $pdo = Database::getConnection();
        $coupon = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    SELECT id, code, title, description, discount_type, discount_value, 
                           minimum_order_value, maximum_discount, expiry_date, is_active 
                    FROM coupons 
                    WHERE UPPER(code) = :code AND is_active = 1 AND expiry_date >= CURDATE() 
                    LIMIT 1
                ");
                $stmt->execute([':code' => $code]);
                $coupon = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("CartController applyCoupon error: " . $e->getMessage());
            }
        }

        if (!$coupon) {
            $this->handleCouponError('Coupon code "' . htmlspecialchars($code) . '" is invalid or expired.');
        }

        $minOrder = (float)($coupon['minimum_order_value'] ?? 0);
        if ($subtotal < $minOrder) {
            $needed = $minOrder - $subtotal;
            $this->handleCouponError('Coupon requires a minimum order value of ₹' . number_format($minOrder, 2) . '. Add ₹' . number_format($needed, 2) . ' more to apply.');
        }

        // Calculate discount
        $discount = 0.0;
        $discType = $coupon['discount_type'];
        $discVal = (float)$coupon['discount_value'];

        if ($discType === 'percentage') {
            $discount = ($subtotal * $discVal) / 100;
            if (!empty($coupon['maximum_discount'])) {
                $discount = min($discount, (float)$coupon['maximum_discount']);
            }
        } else {
            $discount = min($subtotal, $discVal);
        }

        $_SESSION['applied_coupon'] = [
            'id'             => (int)$coupon['id'],
            'code'           => $coupon['code'],
            'title'          => $coupon['title'],
            'discount_type'  => $discType,
            'discount_value' => $discVal,
            'min_order'      => $minOrder,
            'max_discount'   => (float)($coupon['maximum_discount'] ?? 0),
            'discount_saved' => round($discount, 2)
        ];

        $summary = self::getCartSummary($cartItems);
        $msg = 'Coupon "' . $coupon['code'] . '" applied successfully! You saved ₹' . number_format($discount, 2) . '.';

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success' => true,
                'action'  => 'coupon_applied',
                'coupon'  => $_SESSION['applied_coupon'],
                'message' => $msg
            ]));
        }

        $_SESSION['flash_success'] = $msg;
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Remove currently applied coupon
     */
    public function removeCoupon() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['applied_coupon']);

        $cartItems = self::getCartItems();
        $summary = self::getCartSummary($cartItems);
        $msg = 'Coupon has been removed.';

        if ($this->isAjax()) {
            $this->jsonResponse(array_merge($summary, [
                'success' => true,
                'action'  => 'coupon_removed',
                'message' => $msg
            ]));
        }

        $_SESSION['flash_success'] = $msg;
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Get count of units in cart for header badge
     */
    public static function getCount(): int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = :uid");
                    $stmt->execute([':uid' => (int)$userId]);
                    return (int)$stmt->fetchColumn();
                } else {
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE session_id = :sid");
                    $stmt->execute([':sid' => $sessionId]);
                    return (int)$stmt->fetchColumn();
                }
            } catch (PDOException $e) {
                error_log("CartController getCount error: " . $e->getMessage());
            }
        }

        $sessionCart = $_SESSION['cart'] ?? [];
        return array_sum($sessionCart);
    }

    /**
     * Get count of distinct items/lines in cart
     */
    public static function getItemsCount(): int {
        return count(self::getCartItems());
    }

    /**
     * Sync session cart to user's database records on login
     */
    public static function syncSessionCartToDb(int $userId): void {
        $pdo = Database::getConnection();
        if (!$pdo || $userId <= 0) {
            return;
        }

        $sessionId = session_id();
        try {
            // Reassign guest cart items by session_id to user_id (handling duplicates)
            $stmt = $pdo->prepare("SELECT id, product_id, quantity FROM cart_items WHERE session_id = :sid AND (user_id IS NULL OR user_id = 0)");
            $stmt->execute([':sid' => $sessionId]);
            $guestItems = $stmt->fetchAll();

            foreach ($guestItems as $gi) {
                $upd = $pdo->prepare("
                    INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                    VALUES (:uid, :sid, :pid, :qty)
                    ON DUPLICATE KEY UPDATE quantity = LEAST(" . self::MAX_ITEM_QUANTITY . ", quantity + :qty2)
                ");
                $upd->execute([
                    ':uid'  => $userId,
                    ':sid'  => $sessionId,
                    ':pid'  => (int)$gi['product_id'],
                    ':qty'  => (int)$gi['quantity'],
                    ':qty2' => (int)$gi['quantity']
                ]);
                $del = $pdo->prepare("DELETE FROM cart_items WHERE id = :id");
                $del->execute([':id' => (int)$gi['id']]);
            }

            // Also migrate array in $_SESSION['cart'] if any
            if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $pid => $qty) {
                    $ins = $pdo->prepare("
                        INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                        VALUES (:uid, :sid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = LEAST(" . self::MAX_ITEM_QUANTITY . ", quantity + :qty2)
                    ");
                    $ins->execute([
                        ':uid'  => $userId,
                        ':sid'  => $sessionId,
                        ':pid'  => (int)$pid,
                        ':qty'  => (int)$qty,
                        ':qty2' => (int)$qty
                    ]);
                }
                $_SESSION['cart'] = [];
            }
        } catch (PDOException $e) {
            error_log("CartController syncSessionCartToDb error: " . $e->getMessage());
        }
    }

    /**
     * Get current quantity of a product in the user's cart
     */
    public static function getProductQuantityInCart(int $productId): int {
        if ($productId <= 0) {
            return 0;
        }

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = :uid AND product_id = :pid LIMIT 1");
                    $stmt->execute([':uid' => (int)$userId, ':pid' => $productId]);
                    $q = $stmt->fetchColumn();
                    return $q !== false ? (int)$q : 0;
                } else {
                    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE session_id = :sid AND product_id = :pid LIMIT 1");
                    $stmt->execute([':sid' => $sessionId, ':pid' => $productId]);
                    $q = $stmt->fetchColumn();
                    return $q !== false ? (int)$q : 0;
                }
            } catch (PDOException $e) {
                error_log("CartController getProductQuantityInCart error: " . $e->getMessage());
            }
        }

        return (int)($_SESSION['cart'][$productId] ?? 0);
    }

    /**
     * Fetch formatted cart items with live product catalog data
     */
    public static function getCartItems(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = Database::getConnection();
        $items = [];

        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();

                if (!empty($userId)) {
                    $stmt = $pdo->prepare("
                        SELECT ci.id AS cart_id, ci.quantity, ci.product_id,
                               p.name, p.slug, p.price, p.original_price, p.image, p.stock_quantity, p.is_active,
                               c.name AS category
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE ci.user_id = :uid AND p.is_active = 1
                        ORDER BY ci.id DESC
                    ");
                    $stmt->execute([':uid' => (int)$userId]);
                } else {
                    $stmt = $pdo->prepare("
                        SELECT ci.id AS cart_id, ci.quantity, ci.product_id,
                               p.name, p.slug, p.price, p.original_price, p.image, p.stock_quantity, p.is_active,
                               c.name AS category
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE ci.session_id = :sid AND p.is_active = 1
                        ORDER BY ci.id DESC
                    ");
                    $stmt->execute([':sid' => $sessionId]);
                }

                $rows = $stmt->fetchAll();
                foreach ($rows as $row) {
                    $items[] = [
                        'id'             => (int)$row['cart_id'],
                        'product_id'     => (int)$row['product_id'],
                        'name'           => $row['name'],
                        'slug'           => $row['slug'] ?? '',
                        'price'          => (float)$row['price'],
                        'original_price' => $row['original_price'] !== null ? (float)$row['original_price'] : null,
                        'quantity'       => (int)$row['quantity'],
                        'image'          => $row['image'] ?: 'placeholder.png',
                        'stock_quantity' => (int)($row['stock_quantity'] ?? 0),
                        'category'       => $row['category'] ?: 'Pharmacy'
                    ];
                }
            } catch (PDOException $e) {
                error_log("CartController getCartItems error: " . $e->getMessage());
            }
        }

        // If no DB rows but session cart has items, fallback to session array
        if (empty($items) && !empty($_SESSION['cart']) && is_array($_SESSION['cart']) && $pdo) {
            $productIds = array_keys($_SESSION['cart']);
            if (!empty($productIds)) {
                try {
                    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                    $stmt = $pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price, p.original_price, p.image, p.stock_quantity,
                               c.name AS category
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id IN ({$placeholders}) AND p.is_active = 1
                    ");
                    $stmt->execute($productIds);
                    $rows = $stmt->fetchAll();
                    foreach ($rows as $r) {
                        $pid = (int)$r['id'];
                        $items[] = [
                            'id'             => $pid,
                            'product_id'     => $pid,
                            'name'           => $r['name'],
                            'slug'           => $r['slug'] ?? '',
                            'price'          => (float)$r['price'],
                            'original_price' => $r['original_price'] !== null ? (float)$r['original_price'] : null,
                            'quantity'       => (int)($_SESSION['cart'][$pid] ?? 1),
                            'image'          => $r['image'] ?: 'placeholder.png',
                            'stock_quantity' => (int)($r['stock_quantity'] ?? 0),
                            'category'       => $r['category'] ?: 'Pharmacy'
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("CartController session items fallback error: " . $e->getMessage());
                }
            }
        }

        return $items;
    }

    /**
     * Compute full financial breakdown for the given cart items
     */
    public static function getCartSummary(?array $cartItems = null): array {
        if ($cartItems === null) {
            $cartItems = self::getCartItems();
        }

        $count = count($cartItems);
        $units = 0;
        $subtotal = 0.0;
        $originalTotal = 0.0;

        foreach ($cartItems as $it) {
            $qty = (int)($it['quantity'] ?? 1);
            $price = (float)($it['price'] ?? 0);
            $orig = (float)($it['original_price'] ?? $price);

            $units += $qty;
            $subtotal += $price * $qty;
            $originalTotal += $orig * $qty;
        }

        // Delivery fee calculation
        $deliveryFee = 0.00;
        $freeDeliveryRemaining = 0.00;
        $freeDeliveryPercent = 100;

        if ($subtotal > 0) {
            if ($subtotal >= self::FREE_DELIVERY_THRESHOLD) {
                $deliveryFee = 0.00;
                $freeDeliveryRemaining = 0.00;
                $freeDeliveryPercent = 100;
            } else {
                $deliveryFee = self::STANDARD_DELIVERY_FEE;
                $freeDeliveryRemaining = round(self::FREE_DELIVERY_THRESHOLD - $subtotal, 2);
                $freeDeliveryPercent = max(5, min(99, (int)round(($subtotal / self::FREE_DELIVERY_THRESHOLD) * 100)));
            }
        }

        // Coupon calculation
        $discountAmount = 0.00;
        $appliedCoupon = $_SESSION['applied_coupon'] ?? null;

        if ($appliedCoupon && $subtotal > 0) {
            $minOrder = (float)($appliedCoupon['min_order'] ?? 0);
            if ($subtotal >= $minOrder) {
                $discType = $appliedCoupon['discount_type'] ?? 'percentage';
                $discVal = (float)($appliedCoupon['discount_value'] ?? 0);
                if ($discType === 'percentage') {
                    $discountAmount = ($subtotal * $discVal) / 100;
                    if (!empty($appliedCoupon['max_discount'])) {
                        $discountAmount = min($discountAmount, (float)$appliedCoupon['max_discount']);
                    }
                } else {
                    $discountAmount = min($subtotal, $discVal);
                }
                $discountAmount = round($discountAmount, 2);
                $_SESSION['applied_coupon']['discount_saved'] = $discountAmount;
            } else {
                // Minimum order not satisfied anymore due to quantity changes
                unset($_SESSION['applied_coupon']);
                $appliedCoupon = null;
            }
        }

        $grandTotal = max(0.00, round($subtotal - $discountAmount + $deliveryFee, 2));
        $catalogSavings = max(0.00, round($originalTotal - $subtotal, 2));

        return [
            'count'                  => $count,
            'units'                  => $units,
            'subtotal'               => round($subtotal, 2),
            'originalTotal'          => round($originalTotal, 2),
            'catalogSavings'         => $catalogSavings,
            'deliveryFee'            => $deliveryFee,
            'freeDeliveryThreshold'  => self::FREE_DELIVERY_THRESHOLD,
            'freeDeliveryRemaining'  => $freeDeliveryRemaining,
            'freeDeliveryPercent'    => $freeDeliveryPercent,
            'appliedCoupon'          => $appliedCoupon,
            'discountAmount'         => $discountAmount,
            'grandTotal'             => $grandTotal,
            'isFreeDelivery'         => ($deliveryFee === 0.00 && $subtotal > 0)
        ];
    }

    private function handleCouponError(string $message): void {
        if ($this->isAjax()) {
            $this->jsonResponse(['success' => false, 'message' => $message], 400);
        }
        $_SESSION['flash_error'] = $message;
        header('Location: ' . url('cart'));
        exit;
    }

    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || isset($_POST['ajax'])
            || isset($_GET['ajax']);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }

    private function redirectBack(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer) && str_starts_with($referer, 'http')) {
            header('Location: ' . $referer);
        } else {
            header('Location: ' . url('cart'));
        }
        exit;
    }
}
