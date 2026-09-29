<?php
class AdminController extends Controller {
    
    private $adminUser;
    
    public function __construct() {
        $this->adminUser = DummyData::getAdminUser();
    }
    
    public function dashboard() {
        $stats = DummyData::getDashboardStats();
        $this->view('pages/admin/dashboard', [
            'stats' => $stats,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Admin Dashboard'
        ]);
    }

    public function productList() {
        $products = DummyData::getProducts();
        $this->view('pages/admin/product_list', [
            'products' => $products,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Products'
        ]);
    }

    public function addProduct() {
        $this->view('pages/admin/add_product', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add New Product'
        ]);
    }

    public function editProduct() {
        $id = $_GET['id'] ?? 1;
        $product = DummyData::getProduct($id);
        $this->view('pages/admin/edit_product', [
            'product' => $product,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Product'
        ]);
    }

    public function users() {
        $users = DummyData::getUsers();
        $this->view('pages/admin/users', [
            'users' => $users,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Users'
        ]);
    }

    public function addUser() {
        $this->view('pages/admin/add_user', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add New User'
        ]);
    }

    public function editUser() {
        $id = $_GET['id'] ?? 2;
        $user = DummyData::getUser($id);
        $this->view('pages/admin/edit_user', [
            'user' => $user,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit User'
        ]);
    }

    public function allOrders() {
        $orders = DummyData::getOrders();
        $this->view('pages/admin/all_orders', [
            'orders' => $orders,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Orders'
        ]);
    }

    public function updateProfile() {
        $this->view('pages/admin/update_profile', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Update Profile'
        ]);
    }

    public function updatePassword() {
        $this->view('pages/admin/update_password', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Update Password'
        ]);
    }

    public function discounts() {
        $coupons = DummyData::getCoupons();
        $this->view('pages/admin/discounts', [
            'coupons' => $coupons,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Manage Discounts'
        ]);
    }

    public function addDiscount() {
        $this->view('pages/admin/add_discount', [
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Add Discount'
        ]);
    }

    public function editDiscount() {
        $id = $_GET['id'] ?? 1;
        $coupons = DummyData::getCoupons();
        $coupon = null;
        foreach($coupons as $c) {
            if ($c['id'] == $id) { $coupon = $c; break; }
        }
        $this->view('pages/admin/edit_discount', [
            'coupon' => $coupon,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Discount'
        ]);
    }
}
