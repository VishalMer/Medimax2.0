<?php
class AdminController extends Controller {
    
    private $adminUser;
    
    public function __construct() {
        $this->adminUser = [
            'id' => 1,
            'name' => 'Vishal Mer',
            'email' => 'vishal@medimax.com',
            'role' => 'owner',
            'image' => 'Rahul.jpg'
        ];
    }
    
    public function dashboard() {
        $this->view('pages/admin/dashboard', [
            'stats' => [
                'total_orders' => 8,
                'total_customers' => 4,
                'total_turnover' => 24500.00,
            ],
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Admin Dashboard'
        ]);
    }

    public function productList() {
        $products = [
            ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'category' => 'Vitamins', 'image' => 'Vitamin C.jpeg'],
            ['id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'category' => 'Supplements', 'image' => 'Fish Oil.jpg'],
            ['id' => 3, 'name' => 'Creatine', 'price' => 1250, 'category' => 'Supplements', 'image' => 'Creatine.jpeg'],
            ['id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'category' => 'Supplements', 'image' => 'Protein Shake.jpeg'],
            ['id' => 5, 'name' => 'Multisource Protein', 'price' => 2100, 'category' => 'Supplements', 'image' => 'Multisource Protein.jpeg'],
            ['id' => 6, 'name' => 'Hair Vitamins', 'price' => 599, 'category' => 'Hair Care', 'image' => 'Hair Vitamins.jpg'],
            ['id' => 7, 'name' => 'Calcium Syrup', 'price' => 320, 'category' => 'Vitamins', 'image' => 'Calcium Syrup.jpeg'],
            ['id' => 8, 'name' => 'Turmeric Supplement', 'price' => 275, 'category' => 'Vitamins', 'image' => 'Turmeric.jpg'],
            ['id' => 9, 'name' => 'Acne Control Gel', 'price' => 399, 'category' => 'Skincare', 'image' => 'Acne Control Gel.jpeg'],
            ['id' => 10, 'name' => 'Argan Oil', 'price' => 550, 'category' => 'Hair Care', 'image' => 'Argan oil.jpeg'],
            ['id' => 11, 'name' => 'Nourishing Cream', 'price' => 485, 'category' => 'Skincare', 'image' => 'Nourishing cream.jpeg'],
            ['id' => 12, 'name' => 'Floslek Suncare', 'price' => 780, 'category' => 'Skincare', 'image' => 'Floslek sun care.jpeg'],
            ['id' => 13, 'name' => 'Bandage Roll', 'price' => 120, 'category' => 'First Aid', 'image' => 'Bandage Roll.jpg'],
            ['id' => 14, 'name' => 'Bandage', 'price' => 85, 'category' => 'First Aid', 'image' => 'Bandage.jpg'],
            ['id' => 15, 'name' => 'Dettol', 'price' => 195, 'category' => 'First Aid', 'image' => 'dettol.jpg'],
            ['id' => 16, 'name' => 'Diaper Care Cream', 'price' => 350, 'category' => 'Baby Care', 'image' => 'Diaper Care Cream.jpg'],
            ['id' => 17, 'name' => 'Conditioner', 'price' => 420, 'category' => 'Hair Care', 'image' => 'Conditioner.jpg'],
            ['id' => 18, 'name' => 'Dove Shampoo', 'price' => 375, 'category' => 'Hair Care', 'image' => 'dove-shampoo.jpg'],
            ['id' => 19, 'name' => 'Green Tea', 'price' => 299, 'category' => 'Beverages', 'image' => 'green-tea.jpg'],
            ['id' => 20, 'name' => 'Nescafe Coffee', 'price' => 450, 'category' => 'Beverages', 'image' => 'nescafe-cofee.jpg'],
            ['id' => 21, 'name' => 'Peppermint Oil', 'price' => 310, 'category' => 'Personal Care', 'image' => 'Peperment.jpg'],
            ['id' => 22, 'name' => 'Rose Water', 'price' => 220, 'category' => 'Skincare', 'image' => 'rose-water.jpg'],
            ['id' => 23, 'name' => 'Olive Oil', 'price' => 340, 'category' => 'Personal Care', 'image' => 'olive-oil.jpg'],
            ['id' => 24, 'name' => 'Simple Face Wash', 'price' => 465, 'category' => 'Skincare', 'image' => 'simple-facewash.jpg'],
            ['id' => 25, 'name' => 'OHBT Spray', 'price' => 290, 'category' => 'Personal Care', 'image' => 'OHBT-spray.jpg'],
            ['id' => 26, 'name' => 'Paracetamol 500mg', 'price' => 99, 'category' => 'First Aid', 'image' => 'placeholder.png'],
            ['id' => 27, 'name' => 'Blood Pressure Monitor', 'price' => 1699, 'category' => 'Medical Devices', 'image' => 'placeholder.png'],
            ['id' => 28, 'name' => 'First Aid Kit', 'price' => 649, 'category' => 'First Aid', 'image' => 'placeholder.png'],
        ];
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
        $product = [
            'id' => $id,
            'name' => 'Vitamin C Tablets',
            'price' => 299,
            'image' => 'Vitamin C.jpeg',
            'category' => 'Vitamins'
        ];
        $this->view('pages/admin/edit_product', [
            'product' => $product,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Product'
        ]);
    }

    public function users() {
        $users = [
            ['id' => 1, 'name' => 'Vishal Mer', 'email' => 'vishal@medimax.com', 'role' => 'owner', 'image' => 'Rahul.jpg'],
            ['id' => 2, 'name' => 'Rahul Sharma', 'email' => 'rahul@gmail.com', 'role' => 'customer', 'image' => ''],
            ['id' => 3, 'name' => 'Priya Patel', 'email' => 'priya@gmail.com', 'role' => 'customer', 'image' => 'User PP (11).jpeg'],
            ['id' => 4, 'name' => 'Amit Kumar', 'email' => 'amit@gmail.com', 'role' => 'admin', 'image' => 'User PP (16).jpeg'],
            ['id' => 5, 'name' => 'Neha Singh', 'email' => 'neha@gmail.com', 'role' => 'customer', 'image' => ''],
        ];
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
        $user = [
            'id' => $id,
            'name' => 'Rahul Sharma',
            'email' => 'rahul@gmail.com',
            'role' => 'customer',
            'image' => ''
        ];
        $this->view('pages/admin/edit_user', [
            'user' => $user,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit User'
        ]);
    }

    public function allOrders() {
        $orders = [
            ['id' => 1, 'user_id' => 2, 'name' => 'Vitamin C', 'price' => 450, 'quantity' => 2, 'image' => 'Vitamin C.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-10 14:30:00'],
            ['id' => 2, 'user_id' => 3, 'name' => 'Creatine', 'price' => 1250, 'quantity' => 1, 'image' => 'Creatine.jpeg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-12 09:15:00'],
            ['id' => 3, 'user_id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'quantity' => 3, 'image' => 'Fish Oil.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-14 16:45:00'],
            ['id' => 4, 'user_id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'quantity' => 1, 'image' => 'Protein Shake.jpeg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-15 11:20:00'],
            ['id' => 5, 'user_id' => 5, 'name' => 'Dove Shampoo', 'price' => 375, 'quantity' => 2, 'image' => 'dove-shampoo.jpg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-18 08:00:00'],
            ['id' => 6, 'user_id' => 3, 'name' => 'Bandage Roll', 'price' => 120, 'quantity' => 5, 'image' => 'Bandage Roll.jpg', 'payment' => 'Completed', 'delivery' => 'Delivered', 'placed_on' => '2025-06-20 13:10:00'],
            ['id' => 7, 'user_id' => 2, 'name' => 'Nescafe Coffee', 'price' => 450, 'quantity' => 1, 'image' => 'nescafe-cofee.jpg', 'payment' => 'Completed', 'delivery' => 'Shipped', 'placed_on' => '2025-06-22 10:30:00'],
            ['id' => 8, 'user_id' => 4, 'name' => 'Acne Control Gel', 'price' => 399, 'quantity' => 1, 'image' => 'Acne Control Gel.jpeg', 'payment' => 'Pending', 'delivery' => 'Processing', 'placed_on' => '2025-06-25 17:00:00'],
        ];
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
        $coupons = [
            ['id' => 1, 'code' => 'MEDI10', 'title' => '10% OFF', 'description' => 'Get 10% off on your order', 'discountType' => 'percentage', 'discountValue' => 10, 'minimumOrderValue' => 499, 'maximumDiscount' => 100, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 100, 'isActive' => true],
            ['id' => 2, 'code' => 'MEDI20', 'title' => '20% OFF', 'description' => 'Get 20% off on your order', 'discountType' => 'percentage', 'discountValue' => 20, 'minimumOrderValue' => 999, 'maximumDiscount' => 300, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 50, 'isActive' => true],
            ['id' => 3, 'code' => 'SAVE50', 'title' => '₹50 OFF', 'description' => 'Flat ₹50 off', 'discountType' => 'fixed', 'discountValue' => 50, 'minimumOrderValue' => 499, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 200, 'isActive' => true],
            ['id' => 4, 'code' => 'HEALTH15', 'title' => '15% OFF', 'description' => '15% off on healthcare', 'discountType' => 'percentage', 'discountValue' => 15, 'minimumOrderValue' => 799, 'maximumDiscount' => 200, 'applicableCategory' => 'First Aid', 'expiryDate' => '2027-12-31', 'usageLimit' => 100, 'isActive' => true],
            ['id' => 5, 'code' => 'WELCOME100', 'title' => '₹100 OFF', 'description' => 'Welcome discount', 'discountType' => 'fixed', 'discountValue' => 100, 'minimumOrderValue' => 999, 'maximumDiscount' => null, 'applicableCategory' => 'All', 'expiryDate' => '2027-12-31', 'usageLimit' => 10, 'isActive' => true],
        ];
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
        $coupon = [
            'id' => $id,
            'code' => 'MEDI10',
            'title' => '10% OFF',
            'description' => 'Get 10% off on your order',
            'discountType' => 'percentage',
            'discountValue' => 10,
            'minimumOrderValue' => 499,
            'maximumDiscount' => 100,
            'applicableCategory' => 'All',
            'expiryDate' => '2027-12-31',
            'usageLimit' => 100,
            'isActive' => true
        ];
        $this->view('pages/admin/edit_discount', [
            'coupon' => $coupon,
            'adminUser' => $this->adminUser,
            'pageTitle' => 'Edit Discount'
        ]);
    }
}
