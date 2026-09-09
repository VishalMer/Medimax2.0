<?php
class WishlistController extends Controller {
    public function index() {
        $wishlistItems = DummyData::getWishlistItems();
        
        $this->view('pages/wishlist', [
            'wishlistItems' => $wishlistItems,
            'pageTitle' => 'My Wishlist - MediMax.com'
        ]);
    }
}
