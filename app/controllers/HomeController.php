<?php
class HomeController extends Controller {
    public function index() {
        $products = [
            ['id' => 1, 'name' => 'Vitamin C Tablets', 'price' => 299, 'original_price' => 349, 'image' => 'Vitamin C.jpeg', 'category' => 'Vitamins'],
            ['id' => 2, 'name' => 'Fish Oil', 'price' => 680, 'original_price' => null, 'image' => 'Fish Oil.jpg', 'category' => 'Supplements'],
            ['id' => 3, 'name' => 'Creatine', 'price' => 1250, 'original_price' => null, 'image' => 'Creatine.jpeg', 'category' => 'Supplements'],
            ['id' => 4, 'name' => 'Protein Shake', 'price' => 1899, 'original_price' => null, 'image' => 'Protein Shake.jpeg', 'category' => 'Supplements'],
            ['id' => 6, 'name' => 'Hair Vitamins', 'price' => 599, 'original_price' => null, 'image' => 'Hair Vitamins.jpg', 'category' => 'Hair Care'],
        ];
        $this->view('pages/home', ['products' => $products, 'pageTitle' => 'Home - MediMax.com']);
    }
}
