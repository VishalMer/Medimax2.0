<?php
class OrderController extends Controller {
    public function index() {
        $allOrders = [
            ['id' => 1, 'user_id' => 2, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-10 14:30:00'],
            ['id' => 2, 'user_id' => 3, 'name' => 'Creatine', 'price' => 1250, 'quantity' => 1, 'image' => 'Creatine.jpeg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-12 09:15:00'],
            ['id' => 3, 'user_id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 3, 'image' => 'Fish Oil.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-14 16:45:00'],
            ['id' => 4, 'user_id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'quantity' => 1, 'image' => 'Protein Shake.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-15 11:20:00'],
            ['id' => 5, 'user_id' => 5, 'name' => 'Dove Shampoo', 'price' => 375, 'quantity' => 2, 'image' => 'dove-shampoo.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-18 08:00:00'],
            ['id' => 6, 'user_id' => 3, 'name' => 'Bandage Roll', 'price' => 120, 'quantity' => 5, 'image' => 'Bandage Roll.jpg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-20 13:10:00'],
            ['id' => 7, 'user_id' => 2, 'name' => 'Nescafe Coffee', 'price' => 450, 'quantity' => 1, 'image' => 'nescafe-cofee.jpg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-22 10:30:00'],
            ['id' => 8, 'user_id' => 4, 'name' => 'Acne Control Gel', 'price' => 399, 'quantity' => 1, 'image' => 'Acne Control Gel.jpeg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-25 17:00:00'],
        ];
        
        // Filter to user_id = 2 for logged in user view
        $userOrders = array_filter($allOrders, function($o) {
            return $o['user_id'] == 2;
        });
        
        $this->view('pages/orders', [
            'orders' => $userOrders,
            'pageTitle' => 'My Orders - MediMax.com'
        ]);
    }
}
