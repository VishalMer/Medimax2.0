<?php
/**
 * MediMax - Order Controller
 * 
 * Fetches real customer orders, line items, and tracking status from MySQL database.
 */
class OrderController extends Controller {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Require customer to be logged in to access order history
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['flash_error'] = 'Please sign in to view your order history and track deliveries.';
            header('Location: ' . url('login'));
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $pdo = Database::getConnection();
        $orders = [];

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    SELECT o.*,
                           c.code AS coupon_code,
                           c.title AS coupon_title
                    FROM orders o
                    LEFT JOIN coupons c ON o.coupon_id = c.id
                    WHERE o.user_id = :uid
                    ORDER BY o.id DESC
                ");
                $stmt->execute([':uid' => $userId]);
                $orders = $stmt->fetchAll();

                if (!empty($orders)) {
                    $orderIds = array_column($orders, 'id');
                    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                    $itemStmt = $pdo->prepare("
                        SELECT * FROM order_items 
                        WHERE order_id IN ($placeholders) 
                        ORDER BY id ASC
                    ");
                    $itemStmt->execute($orderIds);
                    $allItems = $itemStmt->fetchAll();

                    $itemsByOrder = [];
                    foreach ($allItems as $item) {
                        $itemsByOrder[$item['order_id']][] = $item;
                    }

                    foreach ($orders as &$ord) {
                        $ord['items'] = $itemsByOrder[$ord['id']] ?? [];
                        $ord['item_count'] = count($ord['items']);
                        $ord['units_count'] = array_sum(array_column($ord['items'], 'quantity'));
                    }
                    unset($ord);
                }
            } catch (PDOException $e) {
                error_log("OrderController index DB error: " . $e->getMessage());
            }
        }

        $this->view('pages/orders', [
            'orders'    => $orders,
            'pageTitle' => 'My Orders - MediMax.com'
        ]);
    }
}
