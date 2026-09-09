<?php
class CartController extends Controller {
    public function index() {
        $cartItems = DummyData::getCartItems();
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
