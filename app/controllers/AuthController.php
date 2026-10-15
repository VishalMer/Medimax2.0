<?php
/**
 * MediMax - Authentication Controller
 * 
 * Handles user registration, login authentication, and session termination.
 */
class AuthController extends Controller {

    /**
     * Display login form or process login authentication
     */
    public function login() {
        $errorMessage = '';
        $successMessage = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                $errorMessage = 'Please provide both username/email and password.';
            } else {
                $pdo = Database::getConnection();
                if (!$pdo) {
                    $errorMessage = 'Could not connect to database. Please ensure MySQL is running in XAMPP.';
                } else {
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :identifier OR name = :identifier LIMIT 1");
                        $stmt->execute([':identifier' => $identifier]);
                        $user = $stmt->fetch();

                        if ($user && password_verify($password, $user['password'])) {
                            if ($user['status'] !== 'active') {
                                $errorMessage = 'Your account is currently inactive or suspended.';
                            } else {
                                $_SESSION['user'] = [
                                    'id'    => (int)$user['id'],
                                    'name'  => $user['name'],
                                    'email' => $user['email'],
                                    'role'  => $user['role'],
                                    'image' => $user['image'] ?: 'Rahul.jpg'
                                ];

                                // Redirect to admin dashboard if role is admin/owner, else storefront home
                                if (in_array($user['role'], ['admin', 'owner'])) {
                                    header('Location: ' . url('admin'));
                                } else {
                                    header('Location: ' . url());
                                }
                                exit;
                            }
                        } else {
                            $errorMessage = 'Invalid username/email or password.';
                        }
                    } catch (PDOException $e) {
                        error_log("Login error: " . $e->getMessage());
                        $errorMessage = 'A database error occurred during sign in. Please try again.';
                    }
                }
            }
        }

        $this->view('pages/login', [
            'pageTitle'      => 'Login - MediMax.com',
            'errorMessage'   => $errorMessage,
            'successMessage' => $successMessage,
            'old'            => ['identifier' => $_POST['identifier'] ?? '']
        ]);
    }
    
    /**
     * Display registration form or process new user registration
     */
    public function register() {
        $errorMessage = '';
        $successMessage = '';
        $old = [
            'name'  => '',
            'email' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $old['name']  = $name;
            $old['email'] = $email;

            $errors = [];

            // Server-side validation matching storefront rules
            if (empty($name)) {
                $errors[] = 'Your name is required.';
            } elseif (mb_strlen($name) < 2) {
                $errors[] = 'Name must be at least 2 characters long.';
            } elseif (!preg_match('/^[A-Za-z\s]+$/', $name)) {
                $errors[] = 'Name can only contain letters and spaces.';
            }

            if (empty($email)) {
                $errors[] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 8 || strlen($password) > 25) {
                $errors[] = 'Password must be between 8 and 25 characters long.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,25}$/', $password)) {
                $errors[] = 'Password must include uppercase, lowercase, number, and special character.';
            }

            if (!empty($errors)) {
                $errorMessage = implode('<br>', $errors);
            } else {
                $pdo = Database::getConnection();
                if (!$pdo) {
                    $errDetail = Database::getError() ? ' (' . htmlspecialchars(Database::getError()) . ')' : '';
                    $errorMessage = 'Database connection failed. Please ensure MySQL is started in XAMPP' . $errDetail;
                } else {
                    try {
                        // Check for duplicate registered email
                        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                        $checkStmt->execute([':email' => $email]);
                        if ($checkStmt->fetch()) {
                            $errorMessage = 'An account with this email address already exists. Please sign in or use another email.';
                        } else {
                            // Hash password securely with bcrypt
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                            // Insert new user into database
                            $insertStmt = $pdo->prepare(
                                "INSERT INTO users (name, email, password, role, image, phone, status, created_at, updated_at) 
                                 VALUES (:name, :email, :password, 'customer', NULL, NULL, 'active', NOW(), NOW())"
                            );
                            $insertStmt->execute([
                                ':name'     => $name,
                                ':email'    => $email,
                                ':password' => $hashedPassword
                            ]);

                            $successMessage = 'Account created successfully! You can now <a href="' . url('login') . '" class="alert-link fw-semibold">Sign in here</a>.';
                            
                            // Reset form fields after successful registration
                            $old['name'] = '';
                            $old['email'] = '';
                        }
                    } catch (PDOException $e) {
                        error_log("Registration DB error: " . $e->getMessage());
                        $errorMessage = 'Database error creating your account: ' . htmlspecialchars($e->getMessage());
                    }
                }
            }
        }

        $this->view('pages/register', [
            'pageTitle'      => 'Register - MediMax.com',
            'errorMessage'   => $errorMessage,
            'successMessage' => $successMessage,
            'old'            => $old
        ]);
    }

    /**
     * Terminate user session and redirect to home
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user']);
        session_destroy();
        header('Location: ' . url());
        exit;
    }
}
