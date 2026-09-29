<?php
class CheckoutController extends Controller {
    
    public function index() {
        $cartItems = DummyData::getCartItems();
        
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
