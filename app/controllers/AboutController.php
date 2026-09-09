<?php
class AboutController extends Controller {
    public function index() {
        $this->view('pages/about', [
            'pageTitle' => 'About Us - MediMax.com'
        ]);
    }
}
