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
                        'image' => $dbUser['image']
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
        $discountStats = [
            'active_coupons'    => 0,
            'total_redemptions' => 0,
            'discount_given'    => 0.0,
            'scheduled_offers'  => 0,
        ];

        if ($pdo) {
            try {
                $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
                $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
                $totalTurnover = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Completed'")->fetchColumn();
                $pendingPayments = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status != 'Completed'")->fetchColumn();
                $inTransitCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE delivery_status NOT IN ('Delivered', 'Cancelled')")->fetchColumn();

                // Live discount / coupon metrics
                $activeCoupons = (int)$pdo->query("
                    SELECT COUNT(*) FROM coupons 
                    WHERE is_active = 1 AND (expiry_date >= CURDATE() OR expiry_date IS NULL)
                ")->fetchColumn();

                $redemptionsCount = (int)$pdo->query("SELECT COUNT(*) FROM coupon_redemptions")->fetchColumn();
                $ordersCouponCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE coupon_id IS NOT NULL OR discount_amount > 0")->fetchColumn();
                $usedSum = (int)$pdo->query("SELECT COALESCE(SUM(used_count), 0) FROM coupons")->fetchColumn();
                $totalRedemptions = max($redemptionsCount, $ordersCouponCount, $usedSum);

                $totalDiscountGiven = (float)$pdo->query("
                    SELECT COALESCE(SUM(discount_amount), 0) FROM orders WHERE discount_amount > 0
                ")->fetchColumn();
                if ($totalDiscountGiven <= 0 && $redemptionsCount > 0) {
                    $totalDiscountGiven = (float)$pdo->query("
                        SELECT COALESCE(SUM(discount_amount), 0) FROM coupon_redemptions
                    ")->fetchColumn();
                }

                $scheduledOffers = (int)$pdo->query("
                    SELECT COUNT(*) FROM coupons 
                    WHERE is_active = 0 OR (expiry_date IS NOT NULL AND expiry_date < CURDATE())
                ")->fetchColumn();

                $discountStats = [
                    'active_coupons'    => $activeCoupons,
                    'total_redemptions' => $totalRedemptions,
                    'discount_given'    => $totalDiscountGiven,
                    'scheduled_offers'  => $scheduledOffers,
                ];

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
            'discountStats' => $discountStats,
            'orders'        => $dashboardOrders,
            'adminUser'     => $this->adminUser,
            'pageTitle'     => 'Admin Dashboard'
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name          = trim($_POST['name'] ?? '');
            $price         = (float)($_POST['price'] ?? 0);
            $categoryId    = (int)($_POST['category_id'] ?? 1);
            $stockQuantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 50;
            $description   = trim($_POST['description'] ?? '');

            if (empty($description)) {
                $description = $name;
            }

            if (empty($name) || strlen($name) < 2) {
                $_SESSION['flash_error'] = 'Please provide a valid product name (at least 2 characters).';
                header('Location: ' . url('admin-add-product'));
                exit;
            }

            if ($price <= 0) {
                $_SESSION['flash_error'] = 'Please enter a valid selling price greater than 0.';
                header('Location: ' . url('admin-add-product'));
                exit;
            }

            // Verify category exists or fallback
            if ($pdo) {
                $catCheck = $pdo->prepare("SELECT id FROM categories WHERE id = :cid LIMIT 1");
                $catCheck->execute([':cid' => $categoryId]);
                if (!$catCheck->fetch()) {
                    $firstCat = (int)$pdo->query("SELECT id FROM categories ORDER BY id ASC LIMIT 1")->fetchColumn();
                    $categoryId = $firstCat ?: 1;
                }
            }

            // Handle image upload
            $imageName = 'placeholder.png';
            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed, true)) {
                    $cleanBase = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($file['name'], PATHINFO_FILENAME)));
                    $targetFile = $cleanBase . '-' . time() . '.' . $ext;
                    $targetDir = PUBLIC_PATH . '/images/products/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    if (move_uploaded_file($file['tmp_name'], $targetDir . $targetFile)) {
                        $imageName = $targetFile;
                    }
                }
            }

            if ($pdo) {
                try {
                    // Generate unique SKU
                    $maxId = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM products")->fetchColumn();
                    $sku = 'MM' . str_pad((string)($maxId + 1), 3, '0', STR_PAD_LEFT);

                    // Generate unique slug
                    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    if (empty($baseSlug)) {
                        $baseSlug = 'product-' . ($maxId + 1);
                    }
                    $slug = $baseSlug;
                    $slugCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = :slug");
                    $slugCheck->execute([':slug' => $slug]);
                    if ((int)$slugCheck->fetchColumn() > 0) {
                        $slug = $baseSlug . '-' . ($maxId + 1);
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO products (
                            category_id, sku, name, slug, description, price, original_price,
                            stock_quantity, image, requires_prescription, is_featured, is_active, created_at
                        ) VALUES (
                            :cat_id, :sku, :name, :slug, :desc, :price, :orig_price,
                            :stock, :img, 0, 0, 1, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':cat_id'     => $categoryId,
                        ':sku'        => $sku,
                        ':name'       => $name,
                        ':slug'       => $slug,
                        ':desc'       => $description,
                        ':price'      => $price,
                        ':orig_price' => $price,
                        ':stock'      => max(0, $stockQuantity),
                        ':img'        => $imageName
                    ]);

                    $_SESSION['flash_success'] = "Product '{$name}' ({$sku}) has been added successfully to the catalog.";
                    header('Location: ' . url('admin-products'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Add product DB error: " . $e->getMessage());
                    $_SESSION['flash_error'] = 'Database error creating product: ' . $e->getMessage();
                    header('Location: ' . url('admin-add-product'));
                    exit;
                }
            }
        }

        $categories = [];
        if ($pdo) {
            try {
                $categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
            } catch (PDOException $e) {
                error_log("Categories query error: " . $e->getMessage());
            }
        }

        $this->view('pages/admin/add_product', [
            'categories' => $categories,
            'adminUser'  => $this->adminUser,
            'pageTitle'  => 'Add New Product'
        ]);
    }

    public function editProduct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = Database::getConnection();
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
            $name          = trim($_POST['name'] ?? '');
            $price         = (float)($_POST['price'] ?? 0);
            $categoryId    = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $stockQuantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : null;

            if (empty($name) || strlen($name) < 2) {
                $_SESSION['flash_error'] = 'Product name must be at least 2 characters long.';
                header('Location: ' . url('admin-edit-product') . '&id=' . $id);
                exit;
            }

            if ($price <= 0) {
                $_SESSION['flash_error'] = 'Please enter a valid price greater than 0.';
                header('Location: ' . url('admin-edit-product') . '&id=' . $id);
                exit;
            }

            if ($pdo) {
                try {
                    $stmtCurr = $pdo->prepare("SELECT * FROM products WHERE id = :id");
                    $stmtCurr->execute([':id' => $id]);
                    $curr = $stmtCurr->fetch();

                    if (!$curr) {
                        $_SESSION['flash_error'] = 'Product not found.';
                        header('Location: ' . url('admin-products'));
                        exit;
                    }

                    $imageName = $curr['image'];
                    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['image'];
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($ext, $allowed, true)) {
                            $cleanBase = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($file['name'], PATHINFO_FILENAME)));
                            $targetFile = $cleanBase . '-' . time() . '.' . $ext;
                            $targetDir = PUBLIC_PATH . '/images/products/';
                            if (!is_dir($targetDir)) {
                                mkdir($targetDir, 0755, true);
                            }
                            if (move_uploaded_file($file['tmp_name'], $targetDir . $targetFile)) {
                                $imageName = $targetFile;
                            }
                        }
                    }

                    // Generate slug
                    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    if (empty($baseSlug)) {
                        $baseSlug = 'product-' . $id;
                    }
                    $slug = $baseSlug;
                    $slugCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = :slug AND id != :id");
                    $slugCheck->execute([':slug' => $slug, ':id' => $id]);
                    if ((int)$slugCheck->fetchColumn() > 0) {
                        $slug = $baseSlug . '-' . $id;
                    }

                    $finalCatId = ($categoryId !== null && $categoryId > 0) ? $categoryId : (int)$curr['category_id'];
                    $finalStock = ($stockQuantity !== null) ? max(0, $stockQuantity) : (int)$curr['stock_quantity'];

                    $upd = $pdo->prepare("
                        UPDATE products SET
                            name = :name,
                            slug = :slug,
                            price = :price,
                            category_id = :cat_id,
                            stock_quantity = :stock,
                            image = :img,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $upd->execute([
                        ':name'   => $name,
                        ':slug'   => $slug,
                        ':price'  => $price,
                        ':cat_id' => $finalCatId,
                        ':stock'  => $finalStock,
                        ':img'    => $imageName,
                        ':id'     => $id
                    ]);

                    $_SESSION['flash_success'] = "Product '{$name}' has been updated successfully.";
                    header('Location: ' . url('admin-products'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Edit product DB error: " . $e->getMessage());
                    $_SESSION['flash_error'] = 'Database error updating product: ' . $e->getMessage();
                    header('Location: ' . url('admin-edit-product') . '&id=' . $id);
                    exit;
                }
            }
        }

        $product = null;
        $categories = [];
        if ($pdo) {
            try {
                $categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        SELECT p.*, c.name AS category 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = :id
                    ");
                    $stmt->execute([':id' => $id]);
                    $product = $stmt->fetch();
                }
            } catch (PDOException $e) {
                error_log("Product fetch error: " . $e->getMessage());
            }
        }

        if (!$product) {
            $_SESSION['flash_error'] = 'Product not found.';
            header('Location: ' . url('admin-products'));
            exit;
        }

        $this->view('pages/admin/edit_product', [
            'product'    => $product,
            'categories' => $categories,
            'adminUser'  => $this->adminUser,
            'pageTitle'  => 'Edit Product'
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $role     = strtolower(trim($_POST['role'] ?? 'customer'));
            $password = $_POST['password'] ?? '';

            if (!in_array($role, ['customer', 'admin', 'owner'], true)) {
                $role = 'customer';
            }

            if (empty($name) || strlen($name) < 2) {
                $_SESSION['flash_error'] = 'Please enter a valid full name (at least 2 characters).';
                header('Location: ' . url('admin-add-user'));
                exit;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash_error'] = 'Please enter a valid email address.';
                header('Location: ' . url('admin-add-user'));
                exit;
            }

            if (empty($password) || strlen($password) < 8) {
                $_SESSION['flash_error'] = 'Temporary password must be at least 8 characters long.';
                header('Location: ' . url('admin-add-user'));
                exit;
            }

            if ($pdo) {
                try {
                    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                    $check->execute([':email' => $email]);
                    if ($check->fetch()) {
                        $_SESSION['flash_error'] = "A user with the email address '{$email}' already exists.";
                        header('Location: ' . url('admin-add-user'));
                        exit;
                    }

                    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
                    if (empty($baseUsername)) {
                        $baseUsername = 'user';
                    }
                    $username = $baseUsername;
                    $counter = 1;
                    while (true) {
                        $uCheck = $pdo->prepare("SELECT id FROM users WHERE username = :uname LIMIT 1");
                        $uCheck->execute([':uname' => $username]);
                        if (!$uCheck->fetch()) {
                            break;
                        }
                        $username = $baseUsername . $counter;
                        $counter++;
                    }

                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (
                            username, name, email, password, role, image, status, is_active, created_at
                        ) VALUES (
                            :uname, :name, :email, :pw, :role, 'default_pfp.jpg', 'active', 1, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':uname' => $username,
                        ':name'  => $name,
                        ':email' => $email,
                        ':pw'    => $hashedPassword,
                        ':role'  => $role
                    ]);

                    $_SESSION['flash_success'] = "User '{$name}' ({$email}) has been created successfully with role '" . ucfirst($role) . "'.";
                    header('Location: ' . url('admin-users'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Add user error: " . $e->getMessage());
                    $_SESSION['flash_error'] = 'Database error creating user: ' . $e->getMessage();
                    header('Location: ' . url('admin-add-user'));
                    exit;
                }
            }
        }

        $this->view('pages/admin/add_user', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add New User'
        ]);
    }

    public function editUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = Database::getConnection();
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
            $name  = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $role  = strtolower(trim($_POST['role'] ?? 'customer'));

            if (!in_array($role, ['customer', 'admin', 'owner'], true)) {
                $role = 'customer';
            }

            if (empty($name) || strlen($name) < 2) {
                $_SESSION['flash_error'] = 'Please enter a valid full name.';
                header('Location: ' . url('admin-edit-user') . '&id=' . $id);
                exit;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash_error'] = 'Please enter a valid email address.';
                header('Location: ' . url('admin-edit-user') . '&id=' . $id);
                exit;
            }

            if ($pdo) {
                try {
                    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
                    $check->execute([':email' => $email, ':id' => $id]);
                    if ($check->fetch()) {
                        $_SESSION['flash_error'] = "The email address '{$email}' is already in use by another account.";
                        header('Location: ' . url('admin-edit-user') . '&id=' . $id);
                        exit;
                    }

                    $upd = $pdo->prepare("
                        UPDATE users SET
                            name = :name,
                            email = :email,
                            role = :role,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $upd->execute([
                        ':name'  => $name,
                        ':email' => $email,
                        ':role'  => $role,
                        ':id'    => $id
                    ]);

                    if ((int)$this->adminUser['id'] === $id) {
                        $_SESSION['user']['name'] = $name;
                        $_SESSION['user']['email'] = $email;
                        $_SESSION['user']['role'] = $role;
                        $this->adminUser['name'] = $name;
                        $this->adminUser['email'] = $email;
                        $this->adminUser['role'] = $role;
                    }

                    $_SESSION['flash_success'] = "User '{$name}' has been updated successfully.";
                    header('Location: ' . url('admin-users'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Edit user error: " . $e->getMessage());
                    $_SESSION['flash_error'] = 'Database error updating user: ' . $e->getMessage();
                    header('Location: ' . url('admin-edit-user') . '&id=' . $id);
                    exit;
                }
            }
        }

        $user = null;
        if ($pdo && $id > 0) {
            try {
                $stmt = $pdo->prepare("SELECT id, username, name, email, role, image, status, is_active FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Fetch user error: " . $e->getMessage());
            }
        }

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            header('Location: ' . url('admin-users'));
            exit;
        }

        $this->view('pages/admin/edit_user', [
            'user'      => $user,
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

    public function updateOrderStatus() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $orderId = (int)($_REQUEST['order_id'] ?? $_REQUEST['id'] ?? 0);
        $action  = $_REQUEST['action'] ?? ''; // mark_payment, mark_delivery, remove_order
        $isAjax  = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

        $pdo = Database::getConnection();
        if ($pdo && $orderId > 0) {
            try {
                $msg = 'Order updated successfully.';
                if ($action === 'mark_payment' || $action === 'payment') {
                    $upd = $pdo->prepare("UPDATE orders SET payment_status = 'Completed' WHERE id = :id");
                    $upd->execute([':id' => $orderId]);
                    $msg = "Order #MX-" . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT) . " marked as Paid.";
                } elseif ($action === 'mark_delivery' || $action === 'delivery') {
                    $upd = $pdo->prepare("UPDATE orders SET delivery_status = 'Delivered' WHERE id = :id");
                    $upd->execute([':id' => $orderId]);
                    $msg = "Order #MX-" . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT) . " marked as Delivered.";
                } elseif ($action === 'remove_order' || $action === 'remove' || $action === 'cancel') {
                    $upd = $pdo->prepare("UPDATE orders SET delivery_status = 'Cancelled' WHERE id = :id");
                    $upd->execute([':id' => $orderId]);
                    $msg = "Order #MX-" . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT) . " has been cancelled.";
                }

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $msg]);
                    exit;
                }
                $_SESSION['flash_success'] = $msg;
            } catch (PDOException $e) {
                error_log("Update order error: " . $e->getMessage());
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
        }
        header('Location: ' . url('admin-orders'));
        exit;
    }

    public function updateProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = Database::getConnection();
        $currentId = (int)$this->adminUser['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));

            if (empty($name) || strlen($name) < 2) {
                $_SESSION['flash_error'] = 'Please enter a valid full name.';
                header('Location: ' . url('admin-update-profile'));
                exit;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash_error'] = 'Please enter a valid email address.';
                header('Location: ' . url('admin-update-profile'));
                exit;
            }

            if ($pdo) {
                try {
                    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
                    $check->execute([':email' => $email, ':id' => $currentId]);
                    if ($check->fetch()) {
                        $_SESSION['flash_error'] = "The email address '{$email}' is already in use by another account.";
                        header('Location: ' . url('admin-update-profile'));
                        exit;
                    }

                    $imageName = $this->adminUser['image'] ?? 'default_pfp.jpg';
                    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['image'];
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($ext, $allowed, true)) {
                            $targetFile = 'avatar_' . $currentId . '_' . time() . '.' . $ext;
                            $targetDir = PUBLIC_PATH . '/images/profiles/';
                            if (!is_dir($targetDir)) {
                                mkdir($targetDir, 0755, true);
                            }
                            if (move_uploaded_file($file['tmp_name'], $targetDir . $targetFile)) {
                                $imageName = $targetFile;
                            }
                        }
                    }

                    $upd = $pdo->prepare("
                        UPDATE users SET
                            name = :name,
                            email = :email,
                            image = :image,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $upd->execute([
                        ':name'  => $name,
                        ':email' => $email,
                        ':image' => $imageName,
                        ':id'    => $currentId
                    ]);

                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $_SESSION['user']['image'] = $imageName;
                    $this->adminUser['name'] = $name;
                    $this->adminUser['email'] = $email;
                    $this->adminUser['image'] = $imageName;

                    $_SESSION['flash_success'] = 'Your profile details have been saved successfully.';
                    $_SESSION['flash_toast'] = [
                        'message' => 'Profile updated successfully!',
                        'icon'    => 'fa-user-check',
                        'type'    => 'success'
                    ];
                    header('Location: ' . url('admin-update-profile'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Update profile error: " . $e->getMessage());
                    $_SESSION['flash_error'] = 'Database error updating profile: ' . $e->getMessage();
                    header('Location: ' . url('admin-update-profile'));
                    exit;
                }
            }
        }

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
                        $_SESSION['flash_toast'] = [
                            'message' => 'Password updated successfully!',
                            'icon'    => 'fa-key',
                            'type'    => 'success'
                        ];
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
                           expiry_date as expiryDate, is_active as isActive, used_count as usedCount,
                           usage_limit as usageLimit
                    FROM coupons ORDER BY id ASC
                ");
                $coupons = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Discounts query error: " . $e->getMessage());
            }
        }
        if (empty($coupons)) {
            $coupons = [
                ['id' => 1, 'code' => 'MEDI10', 'title' => '10% OFF', 'description' => 'Get 10% off on your order', 'discountType' => 'percentage', 'discountValue' => 10, 'minimumOrderValue' => 499, 'maximumDiscount' => 100, 'applicableCategory' => 'All', 'expiryDate' => '2028-12-31', 'usageLimit' => 100, 'usedCount' => 0, 'isActive' => 1],
                ['id' => 2, 'code' => 'MEDI20', 'title' => '20% OFF', 'description' => 'Get 20% off on your order', 'discountType' => 'percentage', 'discountValue' => 20, 'minimumOrderValue' => 999, 'maximumDiscount' => 300, 'applicableCategory' => 'All', 'expiryDate' => '2028-12-31', 'usageLimit' => 50, 'usedCount' => 0, 'isActive' => 1],
                ['id' => 3, 'code' => 'SAVE50', 'title' => '₹50 OFF', 'description' => 'Flat ₹50 off', 'discountType' => 'fixed', 'discountValue' => 50, 'minimumOrderValue' => 499, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2028-12-31', 'usageLimit' => 200, 'usedCount' => 0, 'isActive' => 1],
                ['id' => 4, 'code' => 'HEALTH15', 'title' => '15% OFF', 'description' => '15% off on healthcare', 'discountType' => 'percentage', 'discountValue' => 15, 'minimumOrderValue' => 799, 'maximumDiscount' => 200, 'applicableCategory' => 'First Aid', 'expiryDate' => '2028-12-31', 'usageLimit' => 100, 'usedCount' => 0, 'isActive' => 1],
                ['id' => 5, 'code' => 'WELCOME100', 'title' => '₹100 OFF', 'description' => 'Welcome discount', 'discountType' => 'fixed', 'discountValue' => 100, 'minimumOrderValue' => 999, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2028-12-31', 'usageLimit' => 10, 'usedCount' => 0, 'isActive' => 1],
            ];
        }
        $this->view('pages/admin/discounts', [
            'coupons' => $coupons,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Discounts'
        ]);
    }

    public function addDiscount() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $title = trim($_POST['title'] ?? '');
            $type = $_POST['discountType'] ?? 'percentage';
            $val = (float)($_POST['discountValue'] ?? 0);
            $minOrder = (float)($_POST['minimumOrderValue'] ?? 0);
            $maxDisc = !empty($_POST['maximumDiscount']) ? (float)$_POST['maximumDiscount'] : null;
            $expiry = !empty($_POST['expiryDate']) ? $_POST['expiryDate'] : date('Y-m-d', strtotime('+30 days'));
            $isActive = isset($_POST['isActive']) ? 1 : 0;

            if (!empty($code) && !empty($title) && $val > 0) {
                $pdo = Database::getConnection();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO coupons (code, title, description, discount_type, discount_value, minimum_order_value, maximum_discount, expiry_date, is_active)
                            VALUES (:code, :title, :desc, :dtype, :dval, :minord, :maxdisc, :expiry, :active)
                        ");
                        $stmt->execute([
                            ':code'    => $code,
                            ':title'   => $title,
                            ':desc'    => $title . ' - ' . ($type === 'percentage' ? $val . '% off' : '₹' . $val . ' off'),
                            ':dtype'   => in_array($type, ['percentage', 'fixed']) ? $type : 'percentage',
                            ':dval'    => $val,
                            ':minord'  => $minOrder,
                            ':maxdisc' => $maxDisc,
                            ':expiry'  => $expiry,
                            ':active'  => $isActive,
                        ]);
                        $_SESSION['flash_success'] = "Discount '{$code}' created successfully!";
                    } catch (PDOException $e) {
                        $_SESSION['flash_error'] = "Could not create discount: " . $e->getMessage();
                    }
                }
            }
            header('Location: ' . url('admin-discounts'));
            exit;
        }

        $this->view('pages/admin/add_discount', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add Discount'
        ]);
    }

    public function editDiscount() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $title = trim($_POST['title'] ?? '');
            $type = $_POST['discountType'] ?? 'percentage';
            $val = (float)($_POST['discountValue'] ?? 0);
            $minOrder = (float)($_POST['minimumOrderValue'] ?? 0);
            $maxDisc = !empty($_POST['maximumDiscount']) ? (float)$_POST['maximumDiscount'] : null;
            $expiry = !empty($_POST['expiryDate']) ? $_POST['expiryDate'] : date('Y-m-d', strtotime('+30 days'));
            $isActive = isset($_POST['isActive']) ? 1 : 0;

            if (!empty($code) && !empty($title) && $val > 0 && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE coupons SET
                            code = :code,
                            title = :title,
                            discount_type = :dtype,
                            discount_value = :dval,
                            minimum_order_value = :minord,
                            maximum_discount = :maxdisc,
                            expiry_date = :expiry,
                            is_active = :active,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':code'    => $code,
                        ':title'   => $title,
                        ':dtype'   => in_array($type, ['percentage', 'fixed']) ? $type : 'percentage',
                        ':dval'    => $val,
                        ':minord'  => $minOrder,
                        ':maxdisc' => $maxDisc,
                        ':expiry'  => $expiry,
                        ':active'  => $isActive,
                        ':id'      => $id,
                    ]);
                    $_SESSION['flash_success'] = "Discount '{$code}' updated successfully!";
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Could not update discount: " . $e->getMessage();
                }
            }
            header('Location: ' . url('admin-discounts'));
            exit;
        }

        $coupon = null;
        if ($pdo && $id > 0) {
            $stmt = $pdo->prepare("
                SELECT id, code, title, description, discount_type as discountType, discount_value as discountValue, 
                       minimum_order_value as minimumOrderValue, maximum_discount as maximumDiscount, 
                       expiry_date as expiryDate, is_active as isActive, used_count as usedCount,
                       usage_limit as usageLimit
                FROM coupons WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            $coupon = $stmt->fetch();
        }

        if (!$coupon) {
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
                'expiryDate' => '2028-12-31',
                'usageLimit' => 100,
                'isActive' => 1
            ];
        }
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
