<?php
class ProductController extends Controller {
    public function index() {
        $products = [
            ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'original_price' => 349, 'image' => 'Vitamin C.jpeg', 'category' => 'Vitamins'],
            ['id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'original_price' => null, 'image' => 'Fish Oil.jpg', 'category' => 'Supplements'],
            ['id' => 3, 'name' => 'Creatine', 'price' => 1250, 'original_price' => null, 'image' => 'Creatine.jpeg', 'category' => 'Supplements'],
            ['id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'original_price' => null, 'image' => 'Protein Shake.jpeg', 'category' => 'Supplements'],
            ['id' => 5, 'name' => 'Multisource Protein', 'price' => 2100, 'original_price' => null, 'image' => 'Multisource Protein.jpeg', 'category' => 'Supplements'],
            ['id' => 6, 'name' => 'Hair Vitamins', 'price' => 599, 'original_price' => null, 'image' => 'Hair Vitamins.jpg', 'category' => 'Hair Care'],
            ['id' => 7, 'name' => 'Calcium Syrup', 'price' => 320, 'original_price' => null, 'image' => 'Calcium Syrup.jpeg', 'category' => 'Vitamins'],
            ['id' => 8, 'name' => 'Turmeric Supplement', 'price' => 275, 'original_price' => null, 'image' => 'Turmeric.jpg', 'category' => 'Vitamins'],
            ['id' => 9, 'name' => 'Acne Control Gel', 'price' => 399, 'original_price' => null, 'image' => 'Acne Control Gel.jpeg', 'category' => 'Skincare'],
            ['id' => 10, 'name' => 'Argan Oil', 'price' => 550, 'original_price' => null, 'image' => 'Argan oil.jpeg', 'category' => 'Hair Care'],
            ['id' => 11, 'name' => 'Nourishing Cream', 'price' => 485, 'original_price' => null, 'image' => 'Nourishing cream.jpeg', 'category' => 'Skincare'],
            ['id' => 12, 'name' => 'Floslek Suncare', 'price' => 780, 'original_price' => null, 'image' => 'Floslek sun care.jpeg', 'category' => 'Skincare'],
            ['id' => 13, 'name' => 'Bandage Roll', 'price' => 120, 'original_price' => null, 'image' => 'Bandage Roll.jpg', 'category' => 'First Aid'],
            ['id' => 14, 'name' => 'Bandage', 'price' => 85, 'original_price' => null, 'image' => 'Bandage.jpg', 'category' => 'First Aid'],
            ['id' => 15, 'name' => 'Dettol', 'price' => 195, 'original_price' => null, 'image' => 'dettol.jpg', 'category' => 'First Aid'],
            ['id' => 16, 'name' => 'Diaper Care Cream', 'price' => 350, 'original_price' => null, 'image' => 'Diaper Care Cream.jpg', 'category' => 'Baby Care'],
            ['id' => 17, 'name' => 'Conditioner', 'price' => 420, 'original_price' => null, 'image' => 'Conditioner.jpg', 'category' => 'Hair Care'],
            ['id' => 18, 'name' => 'Dove Shampoo', 'price' => 375, 'original_price' => null, 'image' => 'dove-shampoo.jpg', 'category' => 'Hair Care'],
            ['id' => 19, 'name' => 'Green Tea', 'price' => 299, 'original_price' => null, 'image' => 'green-tea.jpg', 'category' => 'Beverages'],
            ['id' => 20, 'name' => 'Nescafe Coffee', 'price' => 450, 'original_price' => null, 'image' => 'nescafe-cofee.jpg', 'category' => 'Beverages'],
            ['id' => 21, 'name' => 'Peppermint Oil', 'price' => 310, 'original_price' => null, 'image' => 'Peperment.jpg', 'category' => 'Personal Care'],
            ['id' => 22, 'name' => 'Rose Water', 'price' => 220, 'original_price' => null, 'image' => 'rose-water.jpg', 'category' => 'Skincare'],
            ['id' => 23, 'name' => 'Olive Oil', 'price' => 340, 'original_price' => null, 'image' => 'olive-oil.jpg', 'category' => 'Personal Care'],
            ['id' => 24, 'name' => 'Simple Face Wash', 'price' => 465, 'original_price' => null, 'image' => 'simple-facewash.jpg', 'category' => 'Skincare'],
            ['id' => 25, 'name' => 'OHBT Spray', 'price' => 290, 'original_price' => null, 'image' => 'OHBT-spray.jpg', 'category' => 'Personal Care'],
            ['id' => 26, 'name' => 'Paracetamol 500mg', 'price' => 99, 'original_price' => 120, 'image' => 'placeholder.png', 'category' => 'First Aid'],
            ['id' => 27, 'name' => 'Blood Pressure Monitor', 'price' => 1699, 'original_price' => 1999, 'image' => 'placeholder.png', 'category' => 'Medical Devices'],
            ['id' => 28, 'name' => 'First Aid Kit', 'price' => 649, 'original_price' => 799, 'image' => 'placeholder.png', 'category' => 'First Aid'],
        ];
        
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $q = strtolower(trim($_GET['q']));
            $products = array_filter($products, function($p) use ($q) {
                return strpos(strtolower($p['name']), $q) !== false;
            });
        }
        
        $this->view('pages/products', [
            'products' => $products,
            'pageTitle' => 'Products - MediMax.com'
        ]);
    }
}
