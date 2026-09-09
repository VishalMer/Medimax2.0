<?php
class OrderController extends Controller {
    public function index() {
        $allOrders = DummyData::getOrders();
        
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
