<?php
class OrderController extends Controller {
    public function index() {
        $userOrders = [
            ['id' => 1, 'user_id' => 2, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-10 14:30:00'],
            ['id' => 3, 'user_id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 3, 'image' => 'Fish Oil.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-14 16:45:00'],
            ['id' => 7, 'user_id' => 2, 'name' => 'Nescafe Coffee', 'price' => 450, 'quantity' => 1, 'image' => 'nescafe-cofee.jpg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-22 10:30:00'],
        ];
        
        $this->view('pages/orders', [
            'orders' => $userOrders,
            'pageTitle' => 'My Orders - MediMax.com'
        ]);
    }
}
