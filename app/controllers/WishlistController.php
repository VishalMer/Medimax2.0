<?php
class WishlistController extends Controller {
    public function index() {
        $wishlistItems = [
            ['id' => 1, 'name' => 'Protein Shake', 'price' => 1899, 'image' => 'Protein Shake.jpeg'],
            ['id' => 2, 'name' => 'Argan Oil', 'price' => 550, 'image' => 'Argan oil.jpeg'],
            ['id' => 3, 'name' => 'Rose Water', 'price' => 220, 'image' => 'rose-water.jpg'],
            ['id' => 4, 'name' => 'Green Tea', 'price' => 299, 'image' => 'green-tea.jpg'],
        ];
        
        $this->view('pages/wishlist', [
            'wishlistItems' => $wishlistItems,
            'pageTitle' => 'My Wishlist - MediMax.com'
        ]);
    }
}
