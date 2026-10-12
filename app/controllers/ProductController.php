<?php
class ProductController extends Controller {
    public function index() {
        $this->view('pages/products', [
            'pageTitle' => 'Products - MediMax.com'
        ]);
    }
}
