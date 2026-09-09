<?php
class AuthController extends Controller {
    public function login() {
        $this->view('pages/login', [
            'pageTitle' => 'Login - MediMax.com'
        ]);
    }
    
    public function register() {
        $this->view('pages/register', [
            'pageTitle' => 'Register - MediMax.com'
        ]);
    }
}
