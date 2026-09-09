<?php
class ProductController extends Controller {
    public function index() {
        $products = DummyData::getProducts();
        
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
