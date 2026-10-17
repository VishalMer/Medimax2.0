<?php
/**
 * MediMax - Shopping Cart Controller
 * 
 * Manages shopping cart items, quantities, and transitions from wishlist to basket.
 */
class CartController extends Controller {

    /**
     * Display the cart page
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartItems = $this->getCartItems();
        $grandTotal = 0;
        foreach ($cartItems as $item) {
            $grandTotal += (float)$item['price'] * (int)$item['quantity'];
        }

        $this->view('pages/cart', [
            'cartItems'  => $cartItems,
            'grandTotal' => $grandTotal,
            'pageTitle'  => 'Shopping Cart - MediMax.com'
        ]);
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
                $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity, is_active FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
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

        $userId = $_SESSION['user']['id'] ?? null;
        $sessionId = session_id();

        if ($pdo) {
            try {
                if (!empty($userId)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                        VALUES (:uid, :sid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = quantity + :qty2
                    ");
                    $stmt->execute([
                        ':uid'  => (int)$userId,
                        ':sid'  => $sessionId,
                        ':pid'  => $productId,
                        ':qty'  => $quantity,
                        ':qty2' => $quantity
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, session_id, product_id, quantity)
                        VALUES (NULL, :sid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = quantity + :qty2
                    ");
                    $stmt->execute([
                        ':sid'  => $sessionId,
                        ':pid'  => $productId,
                        ':qty'  => $quantity,
                        ':qty2' => $quantity
                    ]);
                }
            } catch (PDOException $e) {
                error_log("CartController add DB error: " . $e->getMessage());
                // Fallback to session
                if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
            }
        } else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
        }

        // If moved from wishlist, remove it from wishlist
        if ($fromWishlist) {
            WishlistController::removeProduct($productId);
            $msg = 'Moved "' . $product['name'] . '" to your cart.';
        } else {
            $msg = 'Added "' . $product['name'] . '" to your cart.';
        }

        $newCount = self::getCount();

        if ($this->isAjax()) {
            $this->jsonResponse([
                'success'    => true,
                'count'      => $newCount,
                'product_id' => $productId,
                'message'    => $msg
            ]);
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

        $cartId = (int)($_POST['cart_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($cartId > 0) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $userId = $_SESSION['user']['id'] ?? null;
                    $sessionId = session_id();
                    if (!empty($userId)) {
                        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id AND user_id = :uid");
                        $stmt->execute([':qty' => $quantity, ':id' => $cartId, ':uid' => (int)$userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id AND session_id = :sid");
                        $stmt->execute([':qty' => $quantity, ':id' => $cartId, ':sid' => $sessionId]);
                    }
                } catch (PDOException $e) {
                    error_log("CartController update error: " . $e->getMessage());
                }
            }
        }

        $_SESSION['flash_success'] = 'Cart updated successfully.';
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

        $cartId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        if ($cartId > 0) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $userId = $_SESSION['user']['id'] ?? null;
                    $sessionId = session_id();
                    if (!empty($userId)) {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND user_id = :uid");
                        $stmt->execute([':id' => $cartId, ':uid' => (int)$userId]);
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND session_id = :sid");
                        $stmt->execute([':id' => $cartId, ':sid' => $sessionId]);
                    }
                } catch (PDOException $e) {
                    error_log("CartController remove error: " . $e->getMessage());
                }
            }
        }

        $_SESSION['flash_success'] = 'Item removed from your cart.';
        header('Location: ' . url('cart'));
        exit;
    }

    /**
     * Clear all items in cart
     */
    public function clear() {
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
                error_log("CartController clear error: " . $e->getMessage());
            }
        }

        $_SESSION['cart'] = [];
        $_SESSION['flash_success'] = 'Cart has been emptied.';
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
     * Sync session cart to user's database records on login
     */
    public static function syncSessionCartToDb(int $userId): void {
        $pdo = Database::getConnection();
        if (!$pdo || $userId <= 0) {
            return;
        }

        $sessionId = session_id();
        try {
            // Reassign guest cart items by session_id to user_id
            $stmt = $pdo->prepare("
                UPDATE cart_items 
                SET user_id = :uid 
                WHERE session_id = :sid AND (user_id IS NULL OR user_id = 0)
            ");
            $stmt->execute([':uid' => $userId, ':sid' => $sessionId]);
        } catch (PDOException $e) {
            error_log("CartController syncSessionCartToDb error: " . $e->getMessage());
        }
    }

    /**
     * Fetch formatted cart items
     */
    private function getCartItems(): array {
        $pdo = Database::getConnection();
        $items = [];

        if ($pdo) {
            try {
                $userId = $_SESSION['user']['id'] ?? null;
                $sessionId = session_id();

                if (!empty($userId)) {
                    $stmt = $pdo->prepare("
                        SELECT ci.id AS cart_id, ci.quantity, ci.product_id,
                               p.name, p.price, p.image, p.stock_quantity
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.id
                        WHERE ci.user_id = :uid AND p.is_active = 1
                        ORDER BY ci.id DESC
                    ");
                    $stmt->execute([':uid' => (int)$userId]);
                } else {
                    $stmt = $pdo->prepare("
                        SELECT ci.id AS cart_id, ci.quantity, ci.product_id,
                               p.name, p.price, p.image, p.stock_quantity
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.id
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
                        'price'          => (float)$row['price'],
                        'quantity'       => (int)$row['quantity'],
                        'image'          => $row['image'] ?: 'placeholder.png',
                        'stock_quantity' => (int)($row['stock_quantity'] ?? 0)
                    ];
                }
            } catch (PDOException $e) {
                error_log("CartController getCartItems error: " . $e->getMessage());
            }
        }

        return $items;
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
