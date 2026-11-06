<?php
$currentPage = $_GET['page'] ?? 'home';
$pageTitle   = $pageTitle ?? 'MediMax';

// Dynamic counts for the header badges from real session/DB state
$navCartCount = class_exists('CartController') ? CartController::getCount() : 0;
$navFavCount  = class_exists('WishlistController') ? WishlistController::getCount() : 0;
$isLoggedIn   = !empty($_SESSION['user']);
$navUser           = $_SESSION['user'] ?? null;
$hasName           = !empty($navUser['name']);
$displayHeaderName = $hasName ? $navUser['name'] : (!empty($navUser['username']) ? $navUser['username'] : 'User');
$navUserFirst      = $navUser ? strtok($displayHeaderName, ' ') : 'Sign in';

$currentReqUri = $_SERVER['REQUEST_URI'] ?? '';
$profileUrl = url('profile');
if (!empty($currentReqUri) && strpos($currentReqUri, 'page=profile') === false) {
    $profileUrl .= '&amp;return_to=' . urlencode($currentReqUri);
}
$changePasswordUrl = url('change-password');
if (!empty($currentReqUri) && strpos($currentReqUri, 'page=change-password') === false && strpos($currentReqUri, 'page=update-password') === false) {
    $changePasswordUrl .= '&amp;return_to=' . urlencode($currentReqUri);
}
?>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F2F8F9">
    <meta name="description" content="MediMax — a licensed online chemist for medicines, supplements, skincare and first aid, delivered across India.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" href="<?= asset('images/assets/MediMax_Logo.png') ?>">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.6.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- MediMax design system -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<a class="visually-hidden-focusable btn-mm btn-mm-primary position-absolute top-0 start-0 m-2" style="z-index:2000" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg fixed-top mm-nav" aria-label="Main">
  <div class="mm-container d-flex align-items-center justify-content-between">

    <a class="navbar-brand" href="<?= url() ?>">
      <span>MediMax<small>Licensed chemist</small></span>
    </a>

    <!-- Mobile Top Bar Quick Actions: Wishlist, Cart & Hamburger Toggler (Visible on mobile < 992px) -->
    <div class="d-flex align-items-center gap-2 d-lg-none">
      <a class="icon-btn" href="<?= url('wishlist') ?>" aria-label="Wishlist" title="Wishlist">
        <i class="far fa-heart" aria-hidden="true"></i>
        <span class="icon-btn__count" id="headerWishlistCountMobile"<?= $navFavCount > 0 ? '' : ' style="display:none;"' ?>><?= $navFavCount ?></span>
      </a>
      <a class="icon-btn" href="<?= url('cart') ?>" aria-label="Cart" title="Cart">
        <i class="fas fa-basket-shopping" aria-hidden="true"></i>
        <span class="icon-btn__count" id="headerCartCountMobile"<?= $navCartCount > 0 ? '' : ' style="display:none;"' ?>><?= $navCartCount ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navOffcanvas" aria-controls="navOffcanvas" aria-label="Open menu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <!-- Offcanvas Navigation Drawer (Full slide-out drawer on mobile, inline navbar on desktop) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="navOffcanvas" aria-labelledby="navOffcanvasLabel">
      <div class="offcanvas-header">
        <span class="navbar-brand m-0" id="navOffcanvasLabel">
          <span>MediMax<small>Licensed chemist</small></span>
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
      </div>

      <div class="offcanvas-body d-lg-flex align-items-lg-center gap-lg-3">

        <!-- Mobile Drawer Search Form (Visible inside drawer on mobile) -->
        <form class="mm-search d-lg-none mb-3" method="get" action="<?= url() ?>" role="search">
          <input type="hidden" name="page" value="products">
          <i class="fas fa-magnifying-glass mm-search__icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="navSearchMobile">Search products</label>
          <input id="navSearchMobile" type="search" name="q" data-validation="max" data-max="120" placeholder="Search medicines &amp; wellness" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
        </form>

        <!-- Main Navigation Links -->
        <ul class="navbar-nav me-lg-auto mb-0">
          <li class="nav-item"><a class="nav-link <?= isActive('home', $currentPage) ?>" href="<?= url() ?>"><i class="fas fa-house me-2 d-lg-none text-muted" aria-hidden="true"></i>Home</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('products', $currentPage) ?>" href="<?= url('products') ?>"><i class="fas fa-boxes-stacked me-2 d-lg-none text-muted" aria-hidden="true"></i>Products</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('orders', $currentPage) ?>" href="<?= url('orders') ?>"><i class="fas fa-box-open me-2 d-lg-none text-muted" aria-hidden="true"></i>Orders</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('about', $currentPage) ?>" href="<?= url('about') ?>"><i class="fas fa-circle-info me-2 d-lg-none text-muted" aria-hidden="true"></i>About</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('contact', $currentPage) ?>" href="<?= url('contact') ?>"><i class="fas fa-headset me-2 d-lg-none text-muted" aria-hidden="true"></i>Contact</a></li>
        </ul>

        <!-- Desktop Search Form (Hidden on mobile) -->
        <form class="mm-search my-3 my-lg-0 d-none d-lg-block" method="get" action="<?= url() ?>" role="search">
          <input type="hidden" name="page" value="products">
          <i class="fas fa-magnifying-glass mm-search__icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="navSearch">Search products</label>
          <input id="navSearch" type="search" name="q" data-validation="max" data-max="120" placeholder="Search medicines &amp; wellness" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
        </form>

        <!-- Desktop User Actions (Hidden on mobile) -->
        <div class="d-none d-lg-flex align-items-center gap-2">
          <a class="icon-btn" href="<?= url('wishlist') ?>" aria-label="Wishlist" title="Wishlist">
            <i class="far fa-heart" aria-hidden="true"></i>
            <span class="icon-btn__count" id="headerWishlistCount"<?= $navFavCount > 0 ? '' : ' style="display:none;"' ?>><?= $navFavCount ?></span>
          </a>
          <a class="icon-btn" href="<?= url('cart') ?>" aria-label="Cart" title="Cart">
            <i class="fas fa-basket-shopping" aria-hidden="true"></i>
            <span class="icon-btn__count" id="headerCartCount"<?= $navCartCount > 0 ? '' : ' style="display:none;"' ?>><?= $navCartCount ?></span>
          </a>
          <button class="account-btn" id="profileOptionsBtn" type="button" aria-haspopup="true" aria-expanded="false">
            <?php if ($isLoggedIn): ?>
              <span class="avatar"><img src="<?= profileImage($navUser['image'] ?? 'default_pfp.jpg') ?>" alt="<?= htmlspecialchars($displayHeaderName) ?>"></span>
              <span id="userName"><?= htmlspecialchars($navUserFirst) ?></span>
            <?php else: ?>
              <span class="avatar"><img src="<?= profileImage('default_pfp.jpg') ?>" alt="Sign in"></span>
              <span id="userName">Sign in</span>
            <?php endif; ?>
            <i class="fas fa-chevron-down" style="font-size:.62rem;opacity:.5" aria-hidden="true"></i>
          </button>
        </div>

        <!-- Mobile Drawer Account Section (Visible inside drawer on mobile < 992px) -->
        <div class="d-lg-none mt-auto pt-3 border-top">
          <?php if ($isLoggedIn): ?>
            <div class="d-flex align-items-center gap-3 p-2 mb-2 rounded-3" style="background: var(--mist);">
              <span class="avatar flex-shrink-0" style="width: 38px; height: 38px;"><img src="<?= profileImage($navUser['image'] ?? 'default_pfp.jpg') ?>" alt="<?= htmlspecialchars($displayHeaderName) ?>"></span>
              <div class="overflow-hidden">
                <div class="fw-bold text-truncate" style="font-size:.88rem"><?= htmlspecialchars($displayHeaderName) ?></div>
                <?php if (!empty($navUser['username'])): ?>
                  <div class="text-muted text-truncate" style="font-size:.76rem">@<?= htmlspecialchars($navUser['username']) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="d-flex flex-column gap-1">
              <a class="nav-link py-2" href="<?= $profileUrl ?>"><i class="fas fa-user-pen me-2 text-muted" aria-hidden="true"></i>Edit profile</a>
              <a class="nav-link py-2" href="<?= $changePasswordUrl ?>"><i class="fas fa-key me-2 text-muted" aria-hidden="true"></i>Change password</a>
              <?php if (in_array($navUser['role'] ?? '', ['admin', 'owner'])): ?>
                <a class="nav-link py-2" href="<?= url('admin') ?>"><i class="fas fa-user-tie me-2 text-muted" aria-hidden="true"></i>Admin panel</a>
              <?php endif; ?>
              <a class="nav-link is-exit py-2" href="<?= url('delete-account') ?>" data-confirm="Are you sure you want to delete and deactivate your account? You will be signed out immediately."><i class="fas fa-user-xmark me-2 text-danger" aria-hidden="true"></i>Delete account</a>
              <a class="nav-link is-exit py-2" href="<?= url('logout') ?>"><i class="fas fa-right-from-bracket me-2 text-danger" aria-hidden="true"></i>Log out</a>
            </div>
          <?php else: ?>
            <div class="d-grid gap-2">
              <a href="<?= url('login') ?>" class="btn-mm btn-mm-primary text-center"><i class="fas fa-arrow-right-to-bracket me-1" aria-hidden="true"></i>Sign in</a>
              <a href="<?= url('register') ?>" class="btn-mm btn-mm-ghost text-center"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Create account</a>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</nav>

<div class="account-menu d-none" id="profileOptions">
  <?php if ($isLoggedIn): ?>
    <div class="account-menu__head">
      <p class="eyebrow eyebrow--muted mb-1">Signed in</p>
      <strong style="font-size:.92rem"><?= htmlspecialchars($displayHeaderName) ?></strong>
      <?php if (!empty($navUser['username'])): ?>
        <div style="font-size:.78rem;color:var(--primary-teal,#26547C);font-weight:600">@<?= htmlspecialchars($navUser['username']) ?></div>
      <?php endif; ?>
      <?php if (!empty($navUser['email'])): ?>
        <div class="num" style="font-size:.72rem;color:var(--ink-40)"><?= htmlspecialchars($navUser['email']) ?></div>
      <?php endif; ?>
    </div>
    <hr class="label-rule my-1">
    <a href="<?= $profileUrl ?>"><i class="fas fa-user-pen" aria-hidden="true"></i> Edit profile</a>
    <a href="<?= $changePasswordUrl ?>"><i class="fas fa-key" aria-hidden="true"></i> Change password</a>
    <a href="<?= url('orders') ?>"><i class="fas fa-box-open" aria-hidden="true"></i> My orders</a>
    <?php if (in_array($navUser['role'] ?? '', ['admin', 'owner'])): ?>
      <a href="<?= url('admin') ?>"><i class="fas fa-user-tie" aria-hidden="true"></i> Admin panel</a>
    <?php endif; ?>
    <a href="<?= url('contact') ?>"><i class="fas fa-headset" aria-hidden="true"></i> Support</a>
    <hr class="label-rule my-1">
    <a class="is-exit" href="<?= url('delete-account') ?>" data-confirm="Are you sure you want to delete and deactivate your account? You will be signed out immediately."><i class="fas fa-user-xmark" aria-hidden="true"></i> Delete account</a>
    <a class="is-exit" href="<?= url('logout') ?>"><i class="fas fa-right-from-bracket" aria-hidden="true"></i> Log out</a>
  <?php else: ?>
    <div class="account-menu__head">
      <p class="eyebrow eyebrow--muted mb-1">Welcome to MediMax</p>
      <strong style="font-size:.92rem">Account</strong>
      <div style="font-size:.76rem;color:var(--ink-60)">Sign in or create an account</div>
    </div>
    <hr class="label-rule my-1">
    <a href="<?= url('login') ?>"><i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign in</a>
    <a href="<?= url('register') ?>"><i class="fas fa-user-plus" aria-hidden="true"></i> Create account</a>
    <a href="<?= url('contact') ?>"><i class="fas fa-headset" aria-hidden="true"></i> Support</a>
  <?php endif; ?>
</div>

<main id="main">

<?php 
$sessionToast = $_SESSION['flash_toast'] ?? null;
unset($_SESSION['flash_toast']);
if (!$sessionToast && !empty($_SESSION['flash_success'])) {
    $sessionToast = [
        'message' => $_SESSION['flash_success'],
        'icon'    => 'fa-circle-check'
    ];
}
if (!$sessionToast && !empty($_SESSION['flash_error'])) {
    $sessionToast = [
        'message' => $_SESSION['flash_error'],
        'icon'    => 'fa-circle-exclamation'
    ];
}
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if ($sessionToast): ?>
  <div id="mmSessionToast" 
       data-msg="<?= htmlspecialchars($sessionToast['message'] ?? '') ?>" 
       data-icon="<?= htmlspecialchars($sessionToast['icon'] ?? 'fa-circle-check') ?>" 
       style="display:none;"></div>
<?php endif; ?>
