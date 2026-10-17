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
}

