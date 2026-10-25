<?php
/**
 * MediMax Bootstrap - Route Definitions
 */

// Frontend routes
$router->register('home', 'HomeController', 'index');
$router->register('products', 'ProductController', 'index');
$router->register('product', 'ProductController', 'detail');
$router->register('product-detail', 'ProductController', 'detail');
$router->register('cart', 'CartController', 'index');
$router->register('cart/add', 'CartController', 'add');
$router->register('cart/update', 'CartController', 'update');
$router->register('cart/remove', 'CartController', 'remove');
$router->register('cart/clear', 'CartController', 'clear');
$router->register('cart/move-to-wishlist', 'CartController', 'moveToWishlist');
$router->register('cart/apply-coupon', 'CartController', 'applyCoupon');
$router->register('cart/remove-coupon', 'CartController', 'removeCoupon');
$router->register('cart-add', 'CartController', 'add');
$router->register('cart-update', 'CartController', 'update');
$router->register('cart-remove', 'CartController', 'remove');
$router->register('cart-clear', 'CartController', 'clear');
$router->register('cart-move-to-wishlist', 'CartController', 'moveToWishlist');
$router->register('cart-apply-coupon', 'CartController', 'applyCoupon');
$router->register('cart-remove-coupon', 'CartController', 'removeCoupon');

$router->register('wishlist', 'WishlistController', 'index');
$router->register('wishlist/add', 'WishlistController', 'add');
$router->register('wishlist/remove', 'WishlistController', 'remove');
$router->register('wishlist/toggle', 'WishlistController', 'toggle');
$router->register('wishlist/clear', 'WishlistController', 'clear');
$router->register('wishlist-add', 'WishlistController', 'add');
$router->register('wishlist-remove', 'WishlistController', 'remove');
$router->register('wishlist-toggle', 'WishlistController', 'toggle');
$router->register('wishlist-clear', 'WishlistController', 'clear');
$router->register('orders', 'OrderController', 'index');
$router->register('about', 'AboutController', 'index');
$router->register('contact', 'ContactController', 'index');
$router->register('login', 'AuthController', 'login');
$router->register('register', 'AuthController', 'register');
$router->register('logout', 'AuthController', 'logout');
$router->register('profile', 'AuthController', 'profile');
$router->register('edit-profile', 'AuthController', 'profile');
$router->register('change-password', 'AuthController', 'changePassword');
$router->register('update-password', 'AuthController', 'changePassword');
$router->register('checkout', 'CheckoutController', 'index');
$router->register('checkout/place-order', 'CheckoutController', 'placeOrder');
$router->register('checkout-place-order', 'CheckoutController', 'placeOrder');
$router->register('order-success', 'CheckoutController', 'success');

// Admin routes
$router->register('admin', 'AdminController', 'dashboard');
$router->register('admin-products', 'AdminController', 'productList');
$router->register('admin-add-product', 'AdminController', 'addProduct');
$router->register('admin-edit-product', 'AdminController', 'editProduct');
$router->register('admin-users', 'AdminController', 'users');
$router->register('admin-add-user', 'AdminController', 'addUser');
$router->register('admin-edit-user', 'AdminController', 'editUser');
$router->register('admin-orders', 'AdminController', 'allOrders');
$router->register('admin-update-profile', 'AdminController', 'updateProfile');
$router->register('admin-update-password', 'AdminController', 'updatePassword');
$router->register('admin-update-password-submit', 'AdminController', 'updatePassword');
$router->register('admin-discounts', 'AdminController', 'discounts');
$router->register('admin-add-discount', 'AdminController', 'addDiscount');
$router->register('admin-edit-discount', 'AdminController', 'editDiscount');
$router->register('admin-toggle-status', 'AdminController', 'toggleStatus');
$router->register('user-deactivate-profile', 'AuthController', 'deactivateProfile');

