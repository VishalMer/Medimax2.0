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

        $errorMessage = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);
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
                        // Look up by unique email OR unique username
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id1 OR username = :id2 LIMIT 1");
                        $stmt->execute([
                            ':id1' => $identifier,
                            ':id2' => $identifier
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
                                'id'       => (int)$user['id'],
                                'username' => $user['username'] ?? '',
                                'name'     => $user['name'] ?? null,
                                'email'    => $user['email'],
                                'role'     => $user['role'],
                                'image'    => $user['image'] ?: 'Rahul.jpg',
                                'phone'    => $user['phone'] ?? ''
                            ];

                            // Migrate guest wishlist and cart items to database account
                            WishlistController::syncSessionWishlistToDb((int)$user['id']);
                            CartController::syncSessionCartToDb((int)$user['id']);

                            $greetName = !empty($user['name']) ? $user['name'] : ($user['username'] ?? 'User');
                            $_SESSION['flash_success'] = 'Welcome back, ' . htmlspecialchars($greetName) . '!';

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
            'username' => '',
            'email'    => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $old['username'] = $username;
            $old['email']    = $email;

            $errors = [];

            // 1. Username validation: unique handle
            if (empty($username)) {
                $errors[] = 'A username is required.';
            } elseif (strlen($username) < 3 || strlen($username) > 30) {
                $errors[] = 'Username must be between 3 and 30 characters long.';
            } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
                $errors[] = 'Username can only contain letters, numbers, and underscores (no spaces).';
            }

            // 2. Email validation
            if (empty($email)) {
                $errors[] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            // 3. Password validation
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
                        // Check for duplicate username (case-insensitive in DB)
                        $checkUserStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
                        $checkUserStmt->execute([':username' => $username]);
                        if ($checkUserStmt->fetch()) {
                            $errorMessage = 'The username "' . htmlspecialchars($username) . '" is already taken. Please choose another username.';
                        } else {
                            // Check for duplicate registered email
                            $checkEmailStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                            $checkEmailStmt->execute([':email' => $email]);
                            if ($checkEmailStmt->fetch()) {
                                $errorMessage = 'An account with this email address already exists. Please sign in or use another email.';
                            } else {
                                // Hash password securely with bcrypt
                                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                                // Insert new user with unique username; name is NULL by default until profile is edited
                                $insertStmt = $pdo->prepare(
                                    "INSERT INTO users (username, name, email, password, role, image, phone, status, is_active, created_at, updated_at) 
                                     VALUES (:username, NULL, :email, :password, 'customer', NULL, NULL, 'active', 1, NOW(), NOW())"
                                );
                                $insertStmt->execute([
                                    ':username' => $username,
                                    ':email'    => $email,
                                    ':password' => $hashedPassword
                                ]);

                                $newUserId = (int)$pdo->lastInsertId();

                                // Automatically log in the newly registered user
                                $_SESSION['user'] = [
                                    'id'       => $newUserId,
                                    'username' => $username,
                                    'name'     => null,
                                    'email'    => $email,
                                    'role'     => 'customer',
                                    'image'    => 'Rahul.jpg',
                                    'phone'    => ''
                                ];

                                $_SESSION['flash_success'] = 'Welcome to MediMax, @' . htmlspecialchars($username) . '! Your account has been created. You can set your display name in your profile.';

                                // Redirect to home page immediately upon successful registration
                                header('Location: ' . url());
                                exit;
                            }
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
     * Customer Profile & Edit Name
     */
    public function profile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']['id'])) {
            $_SESSION['flash_error'] = 'Please sign in to access your profile settings.';
            header('Location: ' . url('login'));
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $pdo = Database::getConnection();
        $user = null;

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, username, name, email, role, phone, image, created_at FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Profile lookup error: " . $e->getMessage());
            }
        }

        if (!$user) {
            header('Location: ' . url());
            exit;
        }

        // Determine safe return URL
        $rawReturn = $_REQUEST['return_to'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        $returnTo = $this->getSafeReturnUrl($rawReturn, url());

        $errorMessage = '';
        $successMessage = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $returnTo = $this->getSafeReturnUrl($_POST['return_to'] ?? $rawReturn, url());

            $errors = [];
            if (!empty($name)) {
                if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                    $errors[] = 'Full name must be between 2 and 100 characters long.';
                } elseif (!preg_match('/^[A-Za-z\s.\'-]+$/', $name)) {
                    $errors[] = 'Full name can only contain letters, spaces, and hyphens.';
                }
            }

            if (!empty($phone) && !preg_match('/^[0-9+\s-]{8,20}$/', $phone)) {
                $errors[] = 'Please enter a valid phone number (8 to 20 digits, spaces or dashes).';
            }

            if (!empty($errors)) {
                $errorMessage = implode('<br>', $errors);
                // Retain entered values so inputs are not lost
                $user['name'] = $name;
                $user['phone'] = $phone;
            } else {
                if ($pdo) {
                    try {
                        $upd = $pdo->prepare("UPDATE users SET name = :name, phone = :phone, updated_at = NOW() WHERE id = :id");
                        $upd->execute([
                            ':name'  => !empty($name) ? $name : null,
                            ':phone' => !empty($phone) ? $phone : null,
                            ':id'    => $userId
                        ]);

                        // Update session so header updates immediately
                        $_SESSION['user']['name'] = !empty($name) ? $name : null;
                        $_SESSION['user']['phone'] = !empty($phone) ? $phone : null;
                        $user['name'] = !empty($name) ? $name : null;
                        $user['phone'] = !empty($phone) ? $phone : null;

                        $_SESSION['flash_success'] = 'Your profile details have been saved successfully! Your display name is now updated.';
                        header('Location: ' . $returnTo);
                        exit;
                    } catch (PDOException $e) {
                        error_log("Profile update DB error: " . $e->getMessage());
                        $errorMessage = 'Database error updating profile: ' . htmlspecialchars($e->getMessage());
                        $user['name'] = $name;
                        $user['phone'] = $phone;
                    }
                } else {
                    $errorMessage = 'Database connection error. Please try again.';
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                }
            }
        }

        $this->view('pages/profile', [
            'user'           => $user,
            'errorMessage'   => $errorMessage,
            'successMessage' => $successMessage,
            'returnTo'       => $returnTo,
            'pageTitle'      => 'Edit Profile - MediMax.com'
        ]);
    }

    /**
     * Sanitize and validate return_to URL to prevent open redirects and loops
     */
    private function getSafeReturnUrl($rawReturn, $defaultUrl = null) {
        if ($defaultUrl === null) {
            $defaultUrl = url();
        }
        if (empty($rawReturn) || !is_string($rawReturn)) {
            return $defaultUrl;
        }

        $rawReturn = trim($rawReturn);
        $parsed = parse_url($rawReturn);
        if ($parsed === false) {
            return $defaultUrl;
        }

        // If host is present, ensure it matches current HTTP_HOST
        if (!empty($parsed['host'])) {
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            $hostOnly = explode(':', $currentHost)[0];
            $parsedHostOnly = explode(':', $parsed['host'])[0];
            if (!empty($hostOnly) && strcasecmp($parsedHostOnly, $hostOnly) !== 0) {
                return $defaultUrl;
            }
        }

        $path = $parsed['path'] ?? '';
        $query = !empty($parsed['query']) ? '?' . $parsed['query'] : '';
        $localUrl = $path . $query;

        if (empty($localUrl)) {
            return $defaultUrl;
        }

        // Must start with BASE_URL or root slash
        if (!str_starts_with($localUrl, BASE_URL) && !str_starts_with($localUrl, '/')) {
            return $defaultUrl;
        }

        // Prevent circular loop back to edit profile page
        $queryParams = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }
        $targetPage = strtolower($queryParams['page'] ?? '');
        if ($targetPage === 'profile' || $targetPage === 'edit-profile') {
            return $defaultUrl;
        }

        return $localUrl;
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
