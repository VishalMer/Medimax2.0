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
        // If user is already authenticated, redirect them away from the login page
        if (!empty($_SESSION['user'])) {
            if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'owner'])) {
                header('Location: ' . url('admin'));
            } else {
                header('Location: ' . url());
            }
            exit;
        }

        $errorMessage = '';
        $successMessage = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                $errorMessage = 'Please provide both your username or email and password.';
            } else {
                $pdo = Database::getConnection();
                if (!$pdo) {
                    $errorMessage = 'Could not connect to database. Please make sure MySQL is running in XAMPP.';
                } else {
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR name = :name LIMIT 1");
                        $stmt->execute([
                            ':email' => $identifier,
                            ':name'  => $identifier
                        ]);
                        $user = $stmt->fetch();

                        if (!$user) {
                            $errorMessage = 'No account found with this email or username. Please check your credentials or create a new account.';
                        } elseif (!password_verify($password, $user['password'])) {
                            $errorMessage = 'Incorrect password. Please verify your password and try again.';
                        } elseif ($user['status'] !== 'active' || (isset($user['is_active']) && (int)$user['is_active'] === 0)) {
                            $errorMessage = 'Your account is currently inactive. Please contact support or an administrator to reactivate your account.';
                        } else {
                            // User exists, password is valid, account is active
                            $_SESSION['user'] = [
                                'id'    => (int)$user['id'],
                                'name'  => $user['name'],
                                'email' => $user['email'],
                                'role'  => $user['role'],
                                'image' => $user['image'] ?: 'Rahul.jpg'
                            ];

                            // Migrate guest wishlist and cart items to database account
                            WishlistController::syncSessionWishlistToDb((int)$user['id']);
                            CartController::syncSessionCartToDb((int)$user['id']);

                            $_SESSION['flash_success'] = 'Welcome back, ' . htmlspecialchars($user['name']) . '!';

                            // Redirect to admin console if admin/owner, else storefront home
                            if (in_array($user['role'], ['admin', 'owner'])) {
                                header('Location: ' . url('admin'));
                            } else {
                                header('Location: ' . url());
                            }
                            exit;
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
        // If user is already authenticated, redirect them away from the register page
        if (!empty($_SESSION['user'])) {
            header('Location: ' . url());
            exit;
        }

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
                                "INSERT INTO users (name, email, password, role, image, phone, status, is_active, created_at, updated_at) 
                                 VALUES (:name, :email, :password, 'customer', NULL, NULL, 'active', 1, NOW(), NOW())"
                            );
                            $insertStmt->execute([
                                ':name'     => $name,
                                ':email'    => $email,
                                ':password' => $hashedPassword
                            ]);

                            $newUserId = (int)$pdo->lastInsertId();

                            // Automatically log in the newly registered user
                            $_SESSION['user'] = [
                                'id'    => $newUserId,
                                'name'  => $name,
                                'email' => $email,
                                'role'  => 'customer',
                                'image' => 'Rahul.jpg'
                            ];

                            $_SESSION['flash_success'] = 'Welcome to MediMax, ' . htmlspecialchars($name) . '! Your account has been created successfully.';

                            // Redirect to home page immediately upon successful registration
                            header('Location: ' . url());
                            exit;
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
     * Soft-deactivate current logged-in user profile (sets is_active=0, status=inactive)
     */
    public function deactivateProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $currentUserId = $_SESSION['user']['id'] ?? null;
        if (!$currentUserId) {
            header('Location: ' . url('login'));
            exit;
        }

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0, status = 'inactive', updated_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $currentUserId]);
            } catch (PDOException $e) {
                error_log("Profile deactivation error: " . $e->getMessage());
            }
        }

        // Destroy session so user is logged out immediately
        unset($_SESSION['user']);
        session_destroy();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_success'] = 'Your profile has been deactivated. Your account data has been safely preserved, but you cannot log in until an administrator reactivates your account.';
        header('Location: ' . url('login'));
        exit;
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
