<?php
/**
 * MediMax - Wishlist Controller
 * 
 * Manages customer saved items/wishlist.
 * Supports persistent storage in MySQL database for authenticated users
 * and session-based storage with auto-sync for guest users.
 */
class WishlistController extends Controller {

    private static ?array $cachedWishlistIds = null;

    /**
     * Clear cached wishlist IDs (useful after session changes or tests)
     */
    public static function clearCache(): void {
        self::$cachedWishlistIds = null;
    }

    /**
     * Get all product IDs currently in the user's wishlist
     */
    public static function getWishlistProductIds(): array {
        if (self::$cachedWishlistIds !== null) {
            return self::$cachedWishlistIds;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!empty($userId)) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    // Sync any pending items from guest session
                    self::syncSessionWishlistToDb((int)$userId);

                    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = :uid");
                    $stmt->execute([':uid' => (int)$userId]);
                    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    self::$cachedWishlistIds = array_values(array_unique(array_map('intval', $ids)));
                    return self::$cachedWishlistIds;
                } catch (PDOException $e) {
                    error_log("WishlistController getWishlistProductIds error: " . $e->getMessage());
                }
            }
        }

        // Guest user or DB fallback
        $sessionIds = $_SESSION['wishlist'] ?? [];
        if (!is_array($sessionIds)) {
            $sessionIds = [];
        }
        self::$cachedWishlistIds = array_values(array_unique(array_filter(array_map('intval', $sessionIds))));
        return self::$cachedWishlistIds;
    }

    /**
     * Check if a product ID exists in the current wishlist
     */
    public static function has($productId): bool {
        $id = (int)$productId;
        if ($id <= 0) {
            return false;
        }
        return in_array($id, self::getWishlistProductIds(), true);
    }

    /**
     * Get the count of items in the current wishlist
     */
    public static function getCount(): int {
        return count(self::getWishlistProductIds());
    }

    /**
     * Sync guest session wishlist into user's database records on login/view
     */
    public static function syncSessionWishlistToDb(int $userId): void {
        if (empty($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
            return;
        }

        $pdo = Database::getConnection();
        if (!$pdo) {
            return;
        }

        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:uid, :pid)");
            foreach ($_SESSION['wishlist'] as $pid) {
                $pid = (int)$pid;
                if ($pid > 0) {
                    $stmt->execute([':uid' => $userId, ':pid' => $pid]);
                }
            }
            $_SESSION['wishlist'] = [];
            self::$cachedWishlistIds = null;
        } catch (PDOException $e) {
            error_log("WishlistController syncSessionWishlistToDb error: " . $e->getMessage());
        }
    }

    /**
     * Remove a product from the wishlist by product ID
     */
    public static function removeProduct(int $productId): bool {
        if ($productId <= 0) {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        if (!empty($userId)) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid");
                    $stmt->execute([':uid' => (int)$userId, ':pid' => $productId]);
                } catch (PDOException $e) {
                    error_log("WishlistController removeProduct error: " . $e->getMessage());
                }
            }
        }

        if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], function ($id) use ($productId) {
                return (int)$id !== $productId;
            }));
        }

        self::$cachedWishlistIds = null;
        return true;
    }

    /**
     * Render the Wishlist page
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $wishlistItems = [];
        $pdo = Database::getConnection();

        if (!empty($userId)) {
            // Authenticated user: load from DB
            if ($pdo) {
                try {
                    self::syncSessionWishlistToDb((int)$userId);

                    $stmt = $pdo->prepare("
                        SELECT w.id AS wishlist_id, w.product_id, w.created_at,
                               p.id, p.name, p.slug, p.price, p.original_price, p.image, p.stock_quantity, p.is_active,
                               c.name AS category
                        FROM wishlist w
                        JOIN products p ON w.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE w.user_id = :uid AND p.is_active = 1
                        ORDER BY w.id DESC
                    ");
                    $stmt->execute([':uid' => (int)$userId]);
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row) {
                        $wishlistItems[] = [
                            'id'             => (int)$row['wishlist_id'],
                            'product_id'     => (int)$row['product_id'],
                            'name'           => $row['name'],
                            'slug'           => $row['slug'],
                            'price'          => (float)$row['price'],
                            'original_price' => $row['original_price'] !== null ? (float)$row['original_price'] : null,
                            'image'          => $row['image'] ?: 'placeholder.png',
                            'stock_quantity' => (int)($row['stock_quantity'] ?? 0),
                            'category'       => $row['category'] ?: 'Pharmacy',
                            'created_at'     => $row['created_at']
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("WishlistController index query error: " . $e->getMessage());
                }
            }
        } else {
            // Guest user: load product details for IDs stored in session
            $productIds = self::getWishlistProductIds();
            if (!empty($productIds) && $pdo) {
                try {
                    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                    $stmt = $pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price, p.original_price, p.image, p.stock_quantity, p.is_active,
                               c.name AS category
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id IN ({$placeholders}) AND p.is_active = 1
                    ");
                    $stmt->execute($productIds);
                    $products = $stmt->fetchAll();

                    // Maintain reverse order of addition
                    $productMap = [];
                    foreach ($products as $p) {
                        $productMap[(int)$p['id']] = $p;
                    }

                    $reversedIds = array_reverse($productIds);
                    foreach ($reversedIds as $pid) {
                        if (isset($productMap[$pid])) {
                            $row = $productMap[$pid];
                            $wishlistItems[] = [
                                'id'             => (int)$row['id'],
                                'product_id'     => (int)$row['id'],
                                'name'           => $row['name'],
                                'slug'           => $row['slug'],
                                'price'          => (float)$row['price'],
                                'original_price' => $row['original_price'] !== null ? (float)$row['original_price'] : null,
                                'image'          => $row['image'] ?: 'placeholder.png',
                                'stock_quantity' => (int)($row['stock_quantity'] ?? 0),
                                'category'       => $row['category'] ?: 'Pharmacy'
                            ];
                        }
                    }
                } catch (PDOException $e) {
                    error_log("WishlistController index guest query error: " . $e->getMessage());
                }
            }
        }

        $this->view('pages/wishlist', [
            'wishlistItems' => $wishlistItems,
            'pageTitle'     => 'My Wishlist - MediMax.com'
        ]);
    }

    /**
     * Add a product to the wishlist
     */
    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? $_GET['id'] ?? 0);
        $product = $this->findProduct($productId);

        if (!$product) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Product not found or unavailable.'], 404);
            }
            $_SESSION['flash_error'] = 'Product not found or unavailable.';
            $this->redirectBack();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $added = false;

        if (!empty($userId)) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:uid, :pid)");
                    $stmt->execute([':uid' => (int)$userId, ':pid' => $productId]);
                    $added = true;
                } catch (PDOException $e) {
                    error_log("WishlistController add error: " . $e->getMessage());
                }
            }
        }

        if (empty($userId) || !$added) {
            if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
                $_SESSION['wishlist'] = [];
            }
            if (!in_array($productId, $_SESSION['wishlist'])) {
                $_SESSION['wishlist'][] = $productId;
            }
        }

        self::$cachedWishlistIds = null;
        $newCount = self::getCount();

        if ($this->isAjax()) {
            $this->jsonResponse([
                'success'     => true,
                'action'      => 'added',
                'in_wishlist' => true,
                'product_id'  => $productId,
                'count'       => $newCount,
                'message'     => 'Saved "' . $product['name'] . '" to your wishlist.'
            ]);
        }

        $_SESSION['flash_success'] = 'Saved "' . $product['name'] . '" to your wishlist.';
        $this->redirectBack();
    }

    /**
     * Remove an item from the wishlist
     */
    public function remove() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $targetId = (int)($_POST['id'] ?? $_GET['id'] ?? $_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $userId = $_SESSION['user']['id'] ?? null;

        if ($targetId > 0) {
            if (!empty($userId)) {
                $pdo = Database::getConnection();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = :uid AND (id = :id OR product_id = :id)");
                        $stmt->execute([':uid' => (int)$userId, ':id' => $targetId]);
                    } catch (PDOException $e) {
                        error_log("WishlistController remove error: " . $e->getMessage());
                    }
                }
            }

            // Also check session wishlist array
            if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
                $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], function ($pid) use ($targetId) {
                    return (int)$pid !== $targetId;
                }));
            }
        }

        self::$cachedWishlistIds = null;
        $newCount = self::getCount();

        if ($this->isAjax()) {
            $this->jsonResponse([
                'success'     => true,
                'action'      => 'removed',
                'in_wishlist' => false,
                'count'       => $newCount,
                'message'     => 'Item removed from your wishlist.'
            ]);
        }

        $_SESSION['flash_success'] = 'Item removed from your wishlist.';
        header('Location: ' . url('wishlist'));
        exit;
    }

    /**
     * Toggle wishlist status for a product (AJAX heart icon click)
     */
    public function toggle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? $_GET['id'] ?? 0);
        $product = $this->findProduct($productId);

        if (!$product) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
            }
            $_SESSION['flash_error'] = 'Product not found.';
            $this->redirectBack();
        }

        $alreadyInWishlist = self::has($productId);

        if ($alreadyInWishlist) {
            self::removeProduct($productId);
            $action = 'removed';
            $inWishlist = false;
            $msg = 'Removed "' . $product['name'] . '" from wishlist.';
        } else {
            $userId = $_SESSION['user']['id'] ?? null;
            if (!empty($userId)) {
                $pdo = Database::getConnection();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:uid, :pid)");
                        $stmt->execute([':uid' => (int)$userId, ':pid' => $productId]);
                    } catch (PDOException $e) {
                        error_log("WishlistController toggle add error: " . $e->getMessage());
                    }
                }
            } else {
                if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
                    $_SESSION['wishlist'] = [];
                }
                if (!in_array($productId, $_SESSION['wishlist'])) {
                    $_SESSION['wishlist'][] = $productId;
                }
            }
            $action = 'added';
            $inWishlist = true;
            $msg = 'Saved "' . $product['name'] . '" to your wishlist.';
        }

        self::$cachedWishlistIds = null;
        $newCount = self::getCount();

        if ($this->isAjax()) {
            $this->jsonResponse([
                'success'     => true,
                'action'      => $action,
                'in_wishlist' => $inWishlist,
                'product_id'  => $productId,
                'count'       => $newCount,
                'message'     => $msg
            ]);
        }

        $_SESSION['flash_success'] = $msg;
        $this->redirectBack();
    }

    /**
     * Clear the whole wishlist
     */
    public function clear() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if (!empty($userId)) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = :uid");
                    $stmt->execute([':uid' => (int)$userId]);
                } catch (PDOException $e) {
                    error_log("WishlistController clear error: " . $e->getMessage());
                }
            }
        }

        $_SESSION['wishlist'] = [];
        self::$cachedWishlistIds = null;

        $_SESSION['flash_success'] = 'Your wishlist has been cleared.';
        header('Location: ' . url('wishlist'));
        exit;
    }

    /**
     * Lookup active product in database
     */
    private function findProduct(int $productId): ?array {
        if ($productId <= 0) {
            return null;
        }

        $pdo = Database::getConnection();
        if (!$pdo) {
            return null;
        }

        try {
            $stmt = $pdo->prepare("SELECT id, name, price, original_price, image, stock_quantity FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
            $stmt->execute([':id' => $productId]);
            $product = $stmt->fetch();
            return $product ?: null;
        } catch (PDOException $e) {
            error_log("WishlistController findProduct error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if current request is AJAX
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || isset($_POST['ajax'])
            || isset($_GET['ajax']);
    }

    /**
     * Send JSON response and terminate
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect back to referer or wishlist
     */
    private function redirectBack(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer) && str_starts_with($referer, 'http')) {
            header('Location: ' . $referer);
        } else {
            header('Location: ' . url('wishlist'));
        }
        exit;
    }
}
