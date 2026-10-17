<?php
class AdminController extends Controller {
    
    private $adminUser;
    
    public function __construct() {
        $this->adminUser = [
            'id' => 1,
            'name' => 'Vishal Mer',
            'email' => 'vishal@medimax.com',
            'role' => 'owner',
            'image' => 'Rahul.jpg'
        ];
    }
    
    public function dashboard() {
        $this->view('pages/admin/dashboard', [
            'stats' => [
                'total_orders' => 8,
                'total_customers' => 4,
                'total_turnover' => 24500.00,
            ],
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Admin Dashboard'
        ]);
    }

    public function productList() {
        $pdo = Database::getConnection();
        $products = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("
                    SELECT p.id, p.category_id, p.sku, p.name, p.slug, p.price, p.original_price, 
                           p.stock_quantity, p.image, p.is_active, c.name AS category 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY p.id ASC
                ");
                $products = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Product list query error: " . $e->getMessage());
            }
        }
        $this->view('pages/admin/product_list', [
            'products' => $products,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Products'
        ]);
    }

    public function addProduct() {
        $this->view('pages/admin/add_product', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add New Product'
        ]);
    }

    public function editProduct() {
        $id = (int)($_GET['id'] ?? 1);
        $product = null;
        $pdo = Database::getConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name AS category 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch();
        }
        if (!$product) {
            $product = [
                'id' => $id,
                'name' => 'Vitamin C Tablets',
                'price' => 299,
                'image' => 'vitamin-c.jpeg',
                'category' => 'Vitamins',
                'is_active' => 1
            ];
        }
        $this->view('pages/admin/edit_product', [
            'product' => $product,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Product'
        ]);
    }

    public function users() {
        $pdo = Database::getConnection();
        $users = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT id, name, email, role, image, status, is_active FROM users ORDER BY id ASC");
                $users = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Users query error: " . $e->getMessage());
            }
        }
        $this->view('pages/admin/users', [
            'users' => $users,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Users'
        ]);
    }

    public function addUser() {
        $this->view('pages/admin/add_user', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add New User'
        ]);
    }

    public function editUser() {
        $id = $_GET['id'] ?? 2;
        $user = [
            'id' => $id,
            'name' => 'Rahul Sharma',
            'email' => 'rahul@gmail.com',
            'role' => 'customer',
            'image' => ''
        ];
        $this->view('pages/admin/edit_user', [
            'user' => $user,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit User'
        ]);
    }

    public function allOrders() {
        $orders = [
            ['id' => 1, 'user_id' => 2, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-10 14:30:00'],
            ['id' => 2, 'user_id' => 3, 'name' => 'Creatine', 'price' => 1250, 'quantity' => 1, 'image' => 'Creatine.jpeg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-12 09:15:00'],
            ['id' => 3, 'user_id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 3, 'image' => 'Fish Oil.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-14 16:45:00'],
            ['id' => 4, 'user_id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'quantity' => 1, 'image' => 'Protein Shake.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-15 11:20:00'],
            ['id' => 5, 'user_id' => 5, 'name' => 'Dove Shampoo', 'price' => 375, 'quantity' => 2, 'image' => 'dove-shampoo.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-18 08:00:00'],
            ['id' => 6, 'user_id' => 3, 'name' => 'Bandage Roll', 'price' => 120, 'quantity' => 5, 'image' => 'Bandage Roll.jpg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-20 13:10:00'],
            ['id' => 7, 'user_id' => 2, 'name' => 'Nescafe Coffee', 'price' => 450, 'quantity' => 1, 'image' => 'nescafe-cofee.jpg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-22 10:30:00'],
            ['id' => 8, 'user_id' => 4, 'name' => 'Acne Control Gel', 'price' => 399, 'quantity' => 1, 'image' => 'Acne Control Gel.jpeg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-25 17:00:00'],
        ];
        $this->view('pages/admin/all_orders', [
            'orders' => $orders,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Orders'
        ]);
    }

    public function updateProfile() {
        $this->view('pages/admin/update_profile', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Update Profile'
        ]);
    }

    public function updatePassword() {
        $this->view('pages/admin/update_password', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Update Password'
        ]);
    }

    public function discounts() {
        $pdo = Database::getConnection();
        $coupons = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("
                    SELECT id, code, title, description, discount_type as discountType, discount_value as discountValue, 
                           minimum_order_value as minimumOrderValue, maximum_discount as maximumDiscount, 
                           expiry_date as expiryDate, is_active as isActive 
                    FROM coupons ORDER BY id ASC
                ");
                $coupons = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Discounts query error: " . $e->getMessage());
            }
        }
        if (empty($coupons)) {
            $coupons = [
                ['id' => 1, 'code' => 'MEDI10', 'title' => '10% OFF', 'description' => 'Get 10% off on your order', 'discountType' => 'percentage', 'discountValue' => 10, 'minimumOrderValue' => 499, 'maximumDiscount' => 100, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 100, 'isActive' => 1],
                ['id' => 2, 'code' => 'MEDI20', 'title' => '20% OFF', 'description' => 'Get 20% off on your order', 'discountType' => 'percentage', 'discountValue' => 20, 'minimumOrderValue' => 999, 'maximumDiscount' => 300, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 50, 'isActive' => 1],
                ['id' => 3, 'code' => 'SAVE50', 'title' => '₹50 OFF', 'description' => 'Flat ₹50 off', 'discountType' => 'fixed', 'discountValue' => 50, 'minimumOrderValue' => 499, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 200, 'isActive' => 1],
                ['id' => 4, 'code' => 'HEALTH15', 'title' => '15% OFF', 'description' => '15% off on healthcare', 'discountType' => 'percentage', 'discountValue' => 15, 'minimumOrderValue' => 799, 'maximumDiscount' => 200, 'applicableCategory' => 'First Aid', 'expiryDate' => '2027-12-31', 'usageLimit' => 100, 'isActive' => 1],
                ['id' => 5, 'code' => 'WELCOME100', 'title' => '₹100 OFF', 'description' => 'Welcome discount', 'discountType' => 'fixed', 'discountValue' => 100, 'minimumOrderValue' => 999, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 10, 'isActive' => 1],
            ];
        }
        $this->view('pages/admin/discounts', [
            'coupons' => $coupons,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Discounts'
        ]);
    }

    public function addDiscount() {
        $this->view('pages/admin/add_discount', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add Discount'
        ]);
    }

    public function editDiscount() {
        $id = (int)($_GET['id'] ?? 1);
        $coupon = [
            'id' => $id,
            'code' => 'MEDI10',
            'title' => '10% OFF',
            'description' => 'Get 10% off on your order',
            'discountType' => 'percentage',
            'discountValue' => 10,
            'minimumOrderValue' => 499,
            'maximumDiscount' => 100,
            'applicableCategory' => 'All',
            'expiryDate' => '2027-12-31',
            'usageLimit' => 100,
            'isActive' => 1
        ];
        $this->view('pages/admin/edit_discount', [
            'coupon' => $coupon,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Discount'
        ]);
    }

    /**
     * Universal status toggler & soft-delete handler for Users, Products, and Coupons
     */
    public function toggleStatus() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $type   = $_REQUEST['type'] ?? '';
        $id     = (int)($_REQUEST['id'] ?? 0);
        $action = $_REQUEST['action'] ?? 'toggle'; // toggle, deactivate, activate
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

        $pdo = Database::getConnection();
        if (!$pdo || $id <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request or database connection error.']);
                exit;
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('admin')));
            exit;
        }

        $newStatus = 1;
        $message = '';

        if ($type === 'user') {
            $stmt = $pdo->prepare("SELECT is_active, status, name FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            if ($user) {
                if ($action === 'deactivate') {
                    $newStatus = 0;
                } elseif ($action === 'activate') {
                    $newStatus = 1;
                } else {
                    $newStatus = ((int)$user['is_active'] === 1) ? 0 : 1;
                }
                $statusEnum = $newStatus ? 'active' : 'inactive';
                $upd = $pdo->prepare("UPDATE users SET is_active = :active, status = :status, updated_at = NOW() WHERE id = :id");
                $upd->execute([':active' => $newStatus, ':status' => $statusEnum, ':id' => $id]);
                $message = "User '{$user['name']}' is now " . ($newStatus ? 'Active' : 'Inactive') . '.';
            }
        } elseif ($type === 'product') {
            $stmt = $pdo->prepare("SELECT is_active, name FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $prod = $stmt->fetch();
            if ($prod) {
                if ($action === 'deactivate') {
                    $newStatus = 0;
                } elseif ($action === 'activate') {
                    $newStatus = 1;
                } else {
                    $newStatus = ((int)$prod['is_active'] === 1) ? 0 : 1;
                }
                $upd = $pdo->prepare("UPDATE products SET is_active = :active, updated_at = NOW() WHERE id = :id");
                $upd->execute([':active' => $newStatus, ':id' => $id]);
                $message = "Product '{$prod['name']}' is now " . ($newStatus ? 'Active' : 'Inactive') . '.';
            }
        } elseif ($type === 'discount' || $type === 'coupon') {
            $stmt = $pdo->prepare("SELECT is_active, code FROM coupons WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $coup = $stmt->fetch();
            if ($coup) {
                if ($action === 'deactivate') {
                    $newStatus = 0;
                } elseif ($action === 'activate') {
                    $newStatus = 1;
                } else {
                    $newStatus = ((int)$coup['is_active'] === 1) ? 0 : 1;
                }
                $upd = $pdo->prepare("UPDATE coupons SET is_active = :active, updated_at = NOW() WHERE id = :id");
                $upd->execute([':active' => $newStatus, ':id' => $id]);
                $message = "Discount '{$coup['code']}' is now " . ($newStatus ? 'Active' : 'Inactive') . '.';
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => true, 
                'is_active' => $newStatus, 
                'status'    => $newStatus ? 'active' : 'inactive',
                'message'   => $message
            ]);
            exit;
        }

        $_SESSION['flash_success'] = $message;
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('admin')));
        exit;
    }
}
