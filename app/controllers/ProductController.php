<?php
class ProductController extends Controller {
    public function index() {
        $pdo = Database::getConnection();
        $products = [];
        $categories = [];
        if ($pdo) {
            try {
                $pStmt = $pdo->query("
                    SELECT p.id, p.category_id, p.sku, p.name, p.slug, p.description, 
                           p.price, p.original_price, p.stock_quantity, p.image, p.is_active, 
                           c.name AS category 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 
                    ORDER BY p.id ASC
                ");
                $products = $pStmt->fetchAll();

                $cStmt = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY id ASC");
                $categories = $cStmt->fetchAll();
            } catch (PDOException $e) {
                error_log("ProductController error: " . $e->getMessage());
            }
        }

        $this->view('pages/products', [
            'products'   => $products,
            'categories' => $categories,
            'pageTitle'  => 'Products - MediMax.com'
        ]);
    }

    public function detail() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int)($_GET['id'] ?? 0);
        $slug = trim($_GET['slug'] ?? '');

        $pdo = Database::getConnection();
        $product = null;
        $relatedProducts = [];
        $categories = [];

        if ($pdo) {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        SELECT p.*, c.name AS category, c.slug AS category_slug 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = :id AND p.is_active = 1 
                        LIMIT 1
                    ");
                    $stmt->execute([':id' => $id]);
                    $product = $stmt->fetch();
                } elseif (!empty($slug)) {
                    $stmt = $pdo->prepare("
                        SELECT p.*, c.name AS category, c.slug AS category_slug 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.slug = :slug AND p.is_active = 1 
                        LIMIT 1
                    ");
                    $stmt->execute([':slug' => $slug]);
                    $product = $stmt->fetch();
                }

                if ($product) {
                    $productId = (int)$product['id'];
                    $catId = (int)($product['category_id'] ?? 0);

                    // Fetch up to 4 related products from same category or active products
                    $relStmt = $pdo->prepare("
                        SELECT p.*, c.name AS category 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.is_active = 1 AND p.id != :cur_id 
                        ORDER BY (p.category_id = :cat_id) DESC, p.id ASC 
                        LIMIT 4
                    ");
                    $relStmt->execute([
                        ':cur_id' => $productId,
                        ':cat_id' => $catId
                    ]);
                    $relatedProducts = $relStmt->fetchAll();

                    $cStmt = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY id ASC");
                    $categories = $cStmt->fetchAll();
                }
            } catch (PDOException $e) {
                error_log("Product detail error: " . $e->getMessage());
            }
        }

        if (!$product) {
            $_SESSION['flash_error'] = 'The requested medicine or wellness product could not be found.';
            header('Location: ' . url('products'));
            exit;
        }

        $this->view('pages/product_detail', [
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
            'categories'      => $categories,
            'pageTitle'       => htmlspecialchars($product['name']) . ' - MediMax.com'
        ]);
    }
}

