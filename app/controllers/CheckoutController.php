<?php
class CheckoutController extends Controller {
    
    public function index() {
        $cartItems = [
            ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'quantity' => 2, 'image' => 'Vitamin C.jpeg'],
            ['id' => 26, 'name' => 'Paracetamol 500mg', 'price' => 99, 'quantity' => 3, 'image' => 'placeholder.png'],
            ['id' => 27, 'name' => 'Blood Pressure Monitor', 'price' => 1699, 'quantity' => 1, 'image' => 'placeholder.png'],
        ];
        
        // Pass everything to the view
        $this->view('pages/checkout', [
            'cartItems' => $cartItems,
            'pageTitle' => 'Checkout - MediMax.com'
        ]);
    }

    public function success() {
        // We will read URL parameters for demo display
        $orderId = $_GET['order_id'] ?? 'MED-' . date('Y') . '-' . rand(10000, 99999);
        $amount = $_GET['amount'] ?? '0.00';
        $method = $_GET['method'] ?? 'UPI';

        $this->view('pages/order_success', [
            'orderId' => $orderId,
            'amount' => $amount,
            'method' => $method,
            'pageTitle' => 'Order Successful - MediMax.com'
        ]);
    }
}
