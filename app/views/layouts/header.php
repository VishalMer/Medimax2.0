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
$avatarLetter      = strtoupper(substr($displayHeaderName, 0, 1));
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
  <div class="mm-container d-flex flex-wrap align-items-center justify-content-between">

    <a class="navbar-brand" href="<?= url() ?>">
      <span>MediMax<small>Licensed chemist</small></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navOffcanvas" aria-controls="navOffcanvas" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="navOffcanvas" aria-labelledby="navOffcanvasLabel">
      <div class="offcanvas-header">
        <span class="navbar-brand m-0" id="navOffcanvasLabel">
          <span>MediMax<small>Licensed chemist</small></span>
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
      </div>

      <div class="offcanvas-body d-lg-flex align-items-lg-center gap-lg-3">

        <ul class="navbar-nav me-lg-auto mb-0">
          <li class="nav-item"><a class="nav-link <?= isActive('home', $currentPage) ?>" href="<?= url() ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('products', $currentPage) ?>" href="<?= url('products') ?>">Products</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('orders', $currentPage) ?>" href="<?= url('orders') ?>">Orders</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('about', $currentPage) ?>" href="<?= url('about') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('contact', $currentPage) ?>" href="<?= url('contact') ?>">Contact</a></li>
        </ul>

        <form class="mm-search my-3 my-lg-0" method="get" action="<?= url() ?>" role="search">
          <input type="hidden" name="page" value="products">
          <i class="fas fa-magnifying-glass mm-search__icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="navSearch">Search products</label>
          <input id="navSearch" type="search" name="q" data-validation="max" data-max="120" placeholder="Search medicines &amp; wellness" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
        </form>

        <div class="d-flex align-items-center gap-2">
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
              <span class="avatar" data-avatar-tint="<?= htmlspecialchars($displayHeaderName) ?>"><?= htmlspecialchars($avatarLetter) ?></span>
              <span id="userName"><?= htmlspecialchars($navUserFirst) ?></span>
            <?php else: ?>
              <span class="avatar"><i class="far fa-user" aria-hidden="true"></i></span>
              <span id="userName">Sign in</span>
            <?php endif; ?>
            <i class="fas fa-chevron-down" style="font-size:.62rem;opacity:.5" aria-hidden="true"></i>
          </button>
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
    <?php
      $currentReqUri = $_SERVER['REQUEST_URI'] ?? '';
      $profileUrl = url('profile');
      if (!empty($currentReqUri) && strpos($currentReqUri, 'page=profile') === false) {
          $profileUrl .= '&amp;return_to=' . urlencode($currentReqUri);
      }
    ?>
    <a href="<?= $profileUrl ?>"><i class="fas fa-user-pen" aria-hidden="true"></i> Edit profile</a>
    <a href="<?= url('orders') ?>"><i class="fas fa-box-open" aria-hidden="true"></i> My orders</a>
    <?php if (in_array($navUser['role'] ?? '', ['admin', 'owner'])): ?>
      <a href="<?= url('admin') ?>"><i class="fas fa-user-tie" aria-hidden="true"></i> Admin panel</a>
      <a href="<?= url('admin-update-profile') ?>"><i class="fas fa-address-card" aria-hidden="true"></i> Update profile</a>
      <a href="<?= url('admin-update-password') ?>"><i class="fas fa-key" aria-hidden="true"></i> Change password</a>
    <?php endif; ?>
    <a href="<?= url('contact') ?>"><i class="fas fa-headset" aria-hidden="true"></i> Support</a>
    <hr class="label-rule my-1">
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

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="mm-container pt-3">
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 rounded-3 shadow-sm mb-3" role="alert" style="font-size: .9rem;">
      <i class="fas fa-circle-check flex-shrink-0 text-success"></i>
      <div class="flex-grow-1"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="mm-container pt-3">
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 rounded-3 shadow-sm mb-3" role="alert" style="font-size: .9rem;">
      <i class="fas fa-circle-exclamation flex-shrink-0 text-danger"></i>
      <div class="flex-grow-1"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
