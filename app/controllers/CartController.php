<?php
class CartController extends Controller {
    public function index() {
        $cartItems = [
            ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'quantity' => 2, 'image' => 'Vitamin C.jpeg'],
            ['id' => 26, 'name' => 'Paracetamol 500mg', 'price' => 99, 'quantity' => 3, 'image' => 'placeholder.png'],
            ['id' => 27, 'name' => 'Blood Pressure Monitor', 'price' => 1699, 'quantity' => 1, 'image' => 'placeholder.png'],
        ];
        $grandTotal = 0;
        
        foreach ($cartItems as $item) {
            $grandTotal += $item['price'] * $item['quantity'];
        }
        
        $this->view('pages/cart', [
            'cartItems' => $cartItems,
            'grandTotal' => $grandTotal,
            'pageTitle' => 'Shopping Cart - MediMax.com'
        ]);
    }
}
