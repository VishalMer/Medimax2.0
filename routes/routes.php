<?php
/**
 * MediMax Bootstrap - Route Definitions
 */

// Frontend routes
$router->register('home', 'HomeController', 'index');
$router->register('products', 'ProductController', 'index');
$router->register('cart', 'CartController', 'index');
$router->register('wishlist', 'WishlistController', 'index');
$router->register('orders', 'OrderController', 'index');
$router->register('about', 'AboutController', 'index');
$router->register('contact', 'ContactController', 'index');
$router->register('login', 'AuthController', 'login');
$router->register('register', 'AuthController', 'register');

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
