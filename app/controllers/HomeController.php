<?php
class HomeController extends Controller {
    public function index() {
        $products = DummyData::getFeaturedProducts(5);
        $this->view('pages/home', ['products' => $products, 'pageTitle' => 'Home - MediMax.com']);
    }
}
