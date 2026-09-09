<?php
/**
 * MediMax Bootstrap - Dummy Data Model
 * Provides static data for all pages (no database required)
 */
class DummyData {

    /**
     * Get all products
     */
    public static function getProducts() {
        return [
            ['id' => 1, 'name' => 'Vitamin C', 'price' => 450, 'image' => 'Vitamin C.jpeg', 'category' => 'Vitamins'],
            ['id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'image' => 'Fish Oil.jpg', 'category' => 'Supplements'],
            ['id' => 3, 'name' => 'Creatine', 'price' => 1250, 'image' => 'Creatine.jpeg', 'category' => 'Supplements'],
            ['id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'image' => 'Protein Shake.jpeg', 'category' => 'Supplements'],
            ['id' => 5, 'name' => 'Multisource Protein', 'price' => 2100, 'image' => 'Multisource Protein.jpeg', 'category' => 'Supplements'],
            ['id' => 6, 'name' => 'Hair Vitamins', 'price' => 599, 'image' => 'Hair Vitamins.jpg', 'category' => 'Hair Care'],
            ['id' => 7, 'name' => 'Calcium Syrup', 'price' => 320, 'image' => 'Calcium Syrup.jpeg', 'category' => 'Vitamins'],
            ['id' => 8, 'name' => 'Turmeric Supplement', 'price' => 275, 'image' => 'Turmeric.jpg', 'category' => 'Vitamins'],
            ['id' => 9, 'name' => 'Acne Control Gel', 'price' => 399, 'image' => 'Acne Control Gel.jpeg', 'category' => 'Skincare'],
            ['id' => 10, 'name' => 'Argan Oil', 'price' => 550, 'image' => 'Argan oil.jpeg', 'category' => 'Hair Care'],
            ['id' => 11, 'name' => 'Nourishing Cream', 'price' => 485, 'image' => 'Nourishing cream.jpeg', 'category' => 'Skincare'],
            ['id' => 12, 'name' => 'Floslek Suncare', 'price' => 780, 'image' => 'Floslek sun care.jpeg', 'category' => 'Skincare'],
            ['id' => 13, 'name' => 'Bandage Roll', 'price' => 120, 'image' => 'Bandage Roll.jpg', 'category' => 'First Aid'],
            ['id' => 14, 'name' => 'Bandage', 'price' => 85, 'image' => 'Bandage.jpg', 'category' => 'First Aid'],
            ['id' => 15, 'name' => 'Dettol', 'price' => 195, 'image' => 'dettol.jpg', 'category' => 'First Aid'],
            ['id' => 16, 'name' => 'Diaper Care Cream', 'price' => 350, 'image' => 'Diaper Care Cream.jpg', 'category' => 'Baby Care'],
            ['id' => 17, 'name' => 'Conditioner', 'price' => 420, 'image' => 'Conditioner.jpg', 'category' => 'Hair Care'],
            ['id' => 18, 'name' => 'Dove Shampoo', 'price' => 375, 'image' => 'dove-shampoo.jpg', 'category' => 'Hair Care'],
            ['id' => 19, 'name' => 'Green Tea', 'price' => 299, 'image' => 'green-tea.jpg', 'category' => 'Beverages'],
            ['id' => 20, 'name' => 'Nescafe Coffee', 'price' => 450, 'image' => 'nescafe-cofee.jpg', 'category' => 'Beverages'],
            ['id' => 21, 'name' => 'Peppermint Oil', 'price' => 310, 'image' => 'Peperment.jpg', 'category' => 'Personal Care'],
            ['id' => 22, 'name' => 'Rose Water', 'price' => 220, 'image' => 'rose-water.jpg', 'category' => 'Skincare'],
            ['id' => 23, 'name' => 'Olive Oil', 'price' => 340, 'image' => 'olive-oil.jpg', 'category' => 'Personal Care'],
            ['id' => 24, 'name' => 'Simple Face Wash', 'price' => 465, 'image' => 'simple-facewash.jpg', 'category' => 'Skincare'],
            ['id' => 25, 'name' => 'OHBT Spray', 'price' => 290, 'image' => 'OHBT-spray.jpg', 'category' => 'Personal Care'],
        ];
    }

    /**
     * Get featured/random products for homepage
     */
    public static function getFeaturedProducts($count = 5) {
        $products = self::getProducts();
        shuffle($products);
        return array_slice($products, 0, $count);
    }

    /**
     * Get a single product by ID
     */
    public static function getProduct($id) {
        foreach (self::getProducts() as $product) {
            if ($product['id'] == $id) return $product;
        }
        return self::getProducts()[0]; // fallback
    }

    /**
     * Get cart items
     */
    public static function getCartItems() {
        return [
            ['id' => 1, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg'],
            ['id' => 2, 'name' => 'Creatine', 'price' => 1250, 'quantity' => 1, 'image' => 'Creatine.jpeg'],
            ['id' => 3, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 1, 'image' => 'Fish Oil.jpg'],
        ];
    }

    /**
     * Get wishlist items
     */
    public static function getWishlistItems() {
        return [
            ['id' => 1, 'name' => 'Protein Shake', 'price' => 1899, 'image' => 'Protein Shake.jpeg'],
            ['id' => 2, 'name' => 'Argan Oil', 'price' => 550, 'image' => 'Argan oil.jpeg'],
            ['id' => 3, 'name' => 'Rose Water', 'price' => 220, 'image' => 'rose-water.jpg'],
            ['id' => 4, 'name' => 'Green Tea', 'price' => 299, 'image' => 'green-tea.jpg'],
        ];
    }

    /**
     * Get user orders
     */
    public static function getOrders() {
        return [
            ['id' => 1, 'user_id' => 2, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-10 14:30:00'],
            ['id' => 2, 'user_id' => 3, 'name' => 'Creatine', 'price' => 1250, 'quantity' => 1, 'image' => 'Creatine.jpeg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-12 09:15:00'],
            ['id' => 3, 'user_id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 3, 'image' => 'Fish Oil.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-14 16:45:00'],
            ['id' => 4, 'user_id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'quantity' => 1, 'image' => 'Protein Shake.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-15 11:20:00'],
            ['id' => 5, 'user_id' => 5, 'name' => 'Dove Shampoo', 'price' => 375, 'quantity' => 2, 'image' => 'dove-shampoo.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-18 08:00:00'],
            ['id' => 6, 'user_id' => 3, 'name' => 'Bandage Roll', 'price' => 120, 'quantity' => 5, 'image' => 'Bandage Roll.jpg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-20 13:10:00'],
            ['id' => 7, 'user_id' => 2, 'name' => 'Nescafe Coffee', 'price' => 450, 'quantity' => 1, 'image' => 'nescafe-cofee.jpg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-22 10:30:00'],
            ['id' => 8, 'user_id' => 4, 'name' => 'Acne Control Gel', 'price' => 399, 'quantity' => 1, 'image' => 'Acne Control Gel.jpeg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-25 17:00:00'],
        ];
    }

    /**
     * Get all users
     */
    public static function getUsers() {
        return [
            ['id' => 1, 'name' => 'Vishal Mer', 'email' => 'vishal@medimax.com', 'role' => 'owner', 'image' => 'Rahul.jpg'],
            ['id' => 2, 'name' => 'Rahul Sharma', 'email' => 'rahul@gmail.com', 'role' => 'customer', 'image' => ''],
            ['id' => 3, 'name' => 'Priya Patel', 'email' => 'priya@gmail.com', 'role' => 'customer', 'image' => 'User PP (11).jpeg'],
            ['id' => 4, 'name' => 'Amit Kumar', 'email' => 'amit@gmail.com', 'role' => 'admin', 'image' => 'User PP (16).jpeg'],
            ['id' => 5, 'name' => 'Neha Singh', 'email' => 'neha@gmail.com', 'role' => 'customer', 'image' => ''],
        ];
    }

    /**
     * Get a single user by ID
     */
    public static function getUser($id) {
        foreach (self::getUsers() as $user) {
            if ($user['id'] == $id) return $user;
        }
        return self::getUsers()[0];
    }

    /**
     * Get admin/current user info
     */
    public static function getAdminUser() {
        return self::getUsers()[0]; // Vishal Mer (owner)
    }

    /**
     * Get dashboard statistics
     */
    public static function getDashboardStats() {
        return [
            'total_orders' => 8,
            'total_customers' => 4,
            'total_turnover' => 24500.00,
        ];
    }

    /**
     * Get categories
     */
    public static function getCategories() {
        return ['Vitamins', 'Supplements', 'Skincare', 'Personal Care', 'First Aid', 'Baby Care', 'Hair Care', 'Beverages'];
    }
}
