<?php
class AdminController extends Controller {
    
    private array $adminUser;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;

        // 1. Enforce authentication: redirect guests to login with alert
        if (empty($user)) {
            $_SESSION['flash_error'] = 'Administrator authentication required. Please sign in to access the management portal.';
            header('Location: ' . url('login'));
            exit;
        }

        // 2. Enforce authorization: forbid customers, allow admin/owner only
        $role = strtolower(trim((string)($user['role'] ?? '')));
        if (!in_array($role, ['admin', 'owner'], true)) {
            $_SESSION['flash_error'] = 'Access restricted: You do not have permission to access the management portal.';
            header('Location: ' . url());
            exit;
        }

        // 3. Load latest record from database to verify active status
        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, role, image, status, is_active FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => (int)$user['id']]);
                $dbUser = $stmt->fetch();
                if ($dbUser && (int)$dbUser['is_active'] === 1 && in_array(strtolower($dbUser['role']), ['admin', 'owner'], true)) {
                    $this->adminUser = [
                        'id'    => (int)$dbUser['id'],
                        'name'  => $dbUser['name'],
                        'email' => $dbUser['email'],
                        'role'  => $dbUser['role'],
                        'image' => $dbUser['image'] ?: 'Rahul.jpg'
                    ];
                    $_SESSION['user'] = array_merge($_SESSION['user'], $this->adminUser);
                } else {
                    $_SESSION['flash_error'] = 'Access denied: Your administrative privileges have been revoked or deactivated.';
                    unset($_SESSION['user']);
                    header('Location: ' . url('login'));
                    exit;
                }
            } catch (PDOException $e) {
                error_log("AdminController user verification error: " . $e->getMessage());
                $this->adminUser = $user;
            }
        } else {
            $this->adminUser = $user;
        }
    }
    
    public function dashboard() {
        $pdo = Database::getConnection();
        $totalOrders = 0;
        $totalCustomers = 0;
        $totalTurnover = 0.0;
        $pendingPayments = 0;
        $inTransitCount = 0;
        $dashboardOrders = [];

        if ($pdo) {
            try {
                $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
                $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
                $totalTurnover = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Completed'")->fetchColumn();
                $pendingPayments = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status != 'Completed'")->fetchColumn();
                $inTransitCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE delivery_status NOT IN ('Delivered', 'Cancelled')")->fetchColumn();

                $stmt = $pdo->query("
                    SELECT o.*,
                           u.name AS customer_name,
                           u.email AS customer_email,
                           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                           (SELECT product_name FROM order_items oi WHERE oi.order_id = o.id ORDER BY id ASC LIMIT 1) AS primary_product_name,
                           (SELECT product_image FROM order_items oi WHERE oi.order_id = o.id ORDER BY id ASC LIMIT 1) AS primary_product_image
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.id DESC LIMIT 8
                ");
                $rawOrders = $stmt->fetchAll();

                foreach ($rawOrders as $ro) {
                    $dashboardOrders[] = [
                        'id'          => $ro['id'],
                        'order_number'=> $ro['order_number'],
                        'user_id'     => $ro['user_id'],
                        'name'        => $ro['primary_product_name'] ?: 'Order #' . $ro['order_number'],
                        'price'       => (float)$ro['total_amount'],
                        'quantity'    => max(1, (int)$ro['item_count']),
                        'image'       => $ro['primary_product_image'] ?: 'placeholder.png',
                        'payment'     => $ro['payment_status'],
                        'delivery'    => $ro['delivery_status'],
                        'placed_on'   => $ro['placed_at'],
                        'customer'    => $ro['customer_name'] ?: $ro['shipping_name']
                    ];
                }
            } catch (PDOException $e) {
                error_log("AdminController dashboard DB error: " . $e->getMessage());
            }
        }

        $this->view('pages/admin/dashboard', [
            'stats' => [
                'total_orders'    => $totalOrders,
                'total_customers' => $totalCustomers,
                'total_turnover'  => $totalTurnover,
            ],
            'orders'    => $dashboardOrders,
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
                $stmt = $pdo->query("SELECT id, username, name, email, role, image, status, is_active FROM users ORDER BY id ASC");
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
        $pdo = Database::getConnection();
        $orders = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("
                    SELECT o.*, 
                           u.name AS customer_name, 
                           u.email AS customer_email,
                           c.code AS coupon_code,
                           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                           (SELECT product_name FROM order_items oi WHERE oi.order_id = o.id ORDER BY id ASC LIMIT 1) AS primary_product_name,
                           (SELECT product_image FROM order_items oi WHERE oi.order_id = o.id ORDER BY id ASC LIMIT 1) AS primary_product_image
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    LEFT JOIN coupons c ON o.coupon_id = c.id
                    ORDER BY o.id DESC
                ");
                $raw = $stmt->fetchAll();

                foreach ($raw as $ro) {
                    $orders[] = [
                        'id'             => (int)$ro['id'],
                        'order_number'   => $ro['order_number'],
                        'user_id'        => (int)$ro['user_id'],
                        'name'           => $ro['primary_product_name'] ?: 'Order #' . $ro['order_number'],
                        'price'          => (float)$ro['total_amount'],
                        'total_amount'   => (float)$ro['total_amount'],
                        'quantity'       => max(1, (int)$ro['item_count']),
                        'image'          => $ro['primary_product_image'] ?: 'placeholder.png',
                        'payment'        => $ro['payment_status'],
                        'payment_status' => $ro['payment_status'],
                        'payment_method' => $ro['payment_method'],
                        'delivery'       => $ro['delivery_status'],
                        'delivery_status'=> $ro['delivery_status'],
                        'placed_on'      => $ro['placed_at'],
                        'placed_at'      => $ro['placed_at'],
                        'customer'       => $ro['customer_name'] ?: $ro['shipping_name']
                    ];
                }
            } catch (PDOException $e) {
                error_log("AdminController allOrders DB error: " . $e->getMessage());
            }
        }

        $this->view('pages/admin/all_orders', [
            'orders'    => $orders,
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
        $errorMessage = '';
        $successMessage = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldPassword     = $_POST['old_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $pdo = Database::getConnection();
            $user = null;
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $this->adminUser['id']]);
                    $user = $stmt->fetch();
                } catch (PDOException $e) {
                    error_log("Admin password fetch error: " . $e->getMessage());
                }
            }

            $errors = [];
            if (empty($oldPassword)) {
                $errors[] = 'Current password is required.';
            } elseif (!$user || !password_verify($oldPassword, $user['password'])) {
                $errors[] = 'Current password is incorrect. Please verify and try again.';
            }

            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < 8 || strlen($newPassword) > 25) {
                $errors[] = 'New password must be between 8 and 25 characters long.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $newPassword)) {
                $errors[] = 'New password must include uppercase, lowercase, number, and special character (@$!%*?&).';
            } elseif (empty($errors) && $user && password_verify($newPassword, $user['password'])) {
                $errors[] = 'New password cannot be the same as your current password.';
            }

            if (empty($confirmPassword)) {
                $errors[] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation password do not match.';
            }

            if (!empty($errors)) {
                $errorMessage = implode('<br>', $errors);
            } else {
                if ($pdo) {
                    try {
                        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                        $upd = $pdo->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
                        $upd->execute([
                            ':password' => $hashed,
                            ':id'       => $this->adminUser['id']
                        ]);
                        $_SESSION['flash_success'] = 'Administrator password has been successfully updated.';
                        header('Location: ' . url('admin-update-password'));
                        exit;
                    } catch (PDOException $e) {
                        $errorMessage = 'Database error updating password: ' . htmlspecialchars($e->getMessage());
                    }
                } else {
                    $errorMessage = 'Database connection error. Please try again.';
                }
            }
        }

        $this->view('pages/admin/update_password', [
            'adminUser'      => $this->adminUser,
            'errorMessage'   => $errorMessage,
            'successMessage' => $successMessage,
            'pageTitle'      => 'Update Password'
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
