<?php
class HomeController extends Controller {
    public function index() {
        $pdo = Database::getConnection();
        $products = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("
                    SELECT p.id, p.category_id, p.sku, p.name, p.slug, p.description, 
                           p.price, p.original_price, p.stock_quantity, p.image, p.is_active, 
                           c.name AS category 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 AND p.is_featured = 1 
                    ORDER BY p.id ASC LIMIT 8
                ");
                $products = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("HomeController error: " . $e->getMessage());
            }
        }
        if (empty($products)) {
            $products = [
                ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'original_price' => 349, 'image' => 'vitamin-c.jpeg', 'category' => 'Vitamins'],
                ['id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'original_price' => null, 'image' => 'Fish Oil.jpg', 'category' => 'Supplements'],
                ['id' => 3, 'name' => 'Creatine', 'price' => 1250, 'original_price' => null, 'image' => 'creatine.jpeg', 'category' => 'Supplements'],
                ['id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'original_price' => null, 'image' => 'protein-shake.jpeg', 'category' => 'Supplements'],
                ['id' => 6, 'name' => 'Hair Vitamins', 'price' => 599, 'original_price' => null, 'image' => 'hair-vitamins.jpg', 'category' => 'Hair Care'],
            ];
        }
        $this->view('pages/home', ['products' => $products, 'pageTitle' => 'Home - MediMax.com']);
    }
}

