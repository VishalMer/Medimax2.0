<?php
$pageTitle   = $pageTitle   ?? 'Admin Panel';
$adminUser   = $adminUser   ?? ['id' => 1, 'name' => 'Vishal Mer', 'email' => 'vishal@medimax.com', 'role' => 'owner', 'image' => 'default_pfp.jpg'];
$pageContent = $pageContent ?? '';
$currentPage = $_GET['page'] ?? 'admin';

// Breadcrumb eyebrow: which part of the console you are standing in.
$sections = [
    'admin'                  => 'Overview',
    'admin-products'         => 'Catalogue',
    'admin-add-product'      => 'Catalogue',
    'admin-update-product'   => 'Catalogue',
    'admin-orders'           => 'Fulfilment',
    'admin-users'            => 'People',
    'admin-add-user'         => 'People',
    'admin-update-user'      => 'People',
    'admin-update-profile'   => 'Your account',
    'admin-update-password'  => 'Your account',
];
$sectionLabel = $sections[$currentPage] ?? 'Console';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - MediMax</title>
    <meta name="theme-color" content="#1B3E5E">
    <meta name="robots" content="noindex">
    <link rel="icon" href="<?= asset('images/assets/MediMax_Logo.png') ?>">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.6.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Custom CSS (loads the type stack) -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>


  <div class="admin-shell">

    <!-- Rail -->
    <aside class="admin-rail" id="adminRail">
      <?php include __DIR__ . '/admin_sidebar.php'; ?>
    </aside>
    <div class="admin-scrim" aria-hidden="true"></div>

    <!-- Workspace -->
    <div class="admin-main">
      <header class="admin-topbar">
        <button class="admin-burger icon-btn" type="button" aria-expanded="false" aria-controls="adminRail" aria-label="Open the console menu">
          <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <div class="admin-topbar__crumb">
          <p class="eyebrow mb-0"><?= htmlspecialchars($sectionLabel) ?></p>
          <h1><?= htmlspecialchars($pageTitle) ?></h1>
        </div>

        <div class="admin-topbar__acts">
          <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url() ?>" title="Open the storefront">
            <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i><span class="d-none d-sm-inline">View shop</span>
          </a>
          <span class="avatar" aria-hidden="true">
            <img src="<?= profileImage($adminUser['image'] ?? 'default_pfp.jpg') ?>" alt="<?= htmlspecialchars($adminUser['name'] ?? 'Admin') ?>">
          </span>
        </div>
      </header>

      <main class="admin-body" id="adminMain">
        <?php 
        $adminToast = $_SESSION['flash_toast'] ?? null;
        unset($_SESSION['flash_toast']);
        if (!$adminToast && !empty($_SESSION['flash_success'])) {
            $adminToast = [
                'message' => $_SESSION['flash_success'],
                'icon'    => 'fa-circle-check'
            ];
        }
        if (!$adminToast && !empty($_SESSION['flash_error'])) {
            $adminToast = [
                'message' => $_SESSION['flash_error'],
                'icon'    => 'fa-circle-exclamation'
            ];
        }
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        ?>
        <?php if ($adminToast): ?>
          <div id="mmSessionToast" 
               data-msg="<?= htmlspecialchars($adminToast['message'] ?? '') ?>" 
               data-icon="<?= htmlspecialchars($adminToast['icon'] ?? 'fa-circle-check') ?>" 
               style="display:none;"></div>
        <?php endif; ?>
        <?= $pageContent ?>
      </main>
    </div>

  </div>

  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JS -->
  <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
