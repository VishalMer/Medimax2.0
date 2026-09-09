<?php
class ContactController extends Controller {
    public function index() {
        $this->view('pages/contact', [
            'pageTitle' => 'Contact Us - MediMax.com'
        ]);
    }
}
