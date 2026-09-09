<?php
$currentPage = $_GET['page'] ?? 'home';
$pageTitle   = $pageTitle ?? 'MediMax';

// Display-only counts for the header badges (reads the same dummy data the pages do).
$navCartCount = class_exists('DummyData') ? count(DummyData::getCartItems()) : 0;
$navFavCount  = class_exists('DummyData') ? count(DummyData::getWishlistItems()) : 0;
$navUser      = class_exists('DummyData') ? DummyData::getAdminUser() : ['name' => 'Guest', 'email' => '', 'role' => ''];
$navUserFirst = strtok($navUser['name'] ?? 'Guest', ' ');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F2F8F9">
    <meta name="description" content="MediMax — a licensed online chemist for medicines, supplements, skincare and first aid, delivered across India.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" href="<?= asset('images/MediMax_Logo.png') ?>">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.6.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- MediMax design system -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
</head>
<body>
<a class="visually-hidden-focusable btn-mm btn-mm-primary position-absolute top-0 start-0 m-2" style="z-index:2000" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg fixed-top mm-nav" aria-label="Main">
  <div class="mm-container d-flex flex-wrap align-items-center justify-content-between">

    <a class="navbar-brand" href="<?= url() ?>">
      <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
      <span>MediMax<small>Licensed chemist</small></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navOffcanvas" aria-controls="navOffcanvas" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="navOffcanvas" aria-labelledby="navOffcanvasLabel">
      <div class="offcanvas-header">
        <span class="navbar-brand m-0" id="navOffcanvasLabel">
          <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
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
          <input id="navSearch" type="search" name="q" placeholder="Search medicines &amp; wellness" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
        </form>

        <div class="d-flex align-items-center gap-2">
          <a class="icon-btn" href="<?= url('wishlist') ?>" aria-label="Wishlist">
            <i class="far fa-heart" aria-hidden="true"></i>
            <?php if ($navFavCount): ?><span class="icon-btn__count"><?= $navFavCount ?></span><?php endif; ?>
          </a>
          <a class="icon-btn" href="<?= url('cart') ?>" aria-label="Cart">
            <i class="fas fa-basket-shopping" aria-hidden="true"></i>
            <?php if ($navCartCount): ?><span class="icon-btn__count"><?= $navCartCount ?></span><?php endif; ?>
          </a>
          <button class="account-btn" id="profileOptionsBtn" type="button" aria-haspopup="true">
            <span class="avatar" data-avatar-tint="<?= htmlspecialchars($navUser['name'] ?? '') ?>"><?= strtoupper(substr($navUser['name'] ?? 'G', 0, 1)) ?></span>
            <span id="userName"><?= htmlspecialchars($navUserFirst) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.62rem;opacity:.5" aria-hidden="true"></i>
          </button>
        </div>

      </div>
    </div>
  </div>
</nav>

<div class="account-menu d-none" id="profileOptions">
  <div class="account-menu__head">
    <p class="eyebrow eyebrow--muted mb-1">Signed in</p>
    <strong style="font-size:.92rem"><?= htmlspecialchars($navUser['name'] ?? 'Guest') ?></strong>
    <?php if (!empty($navUser['email'])): ?>
      <div class="num" style="font-size:.72rem;color:var(--ink-40)"><?= htmlspecialchars($navUser['email']) ?></div>
    <?php endif; ?>
  </div>
  <hr class="label-rule my-1">
  <a href="<?= url('admin-update-profile') ?>"><i class="fas fa-address-card" aria-hidden="true"></i> Update profile</a>
  <a href="<?= url('admin-update-password') ?>"><i class="fas fa-key" aria-hidden="true"></i> Change password</a>
  <a href="<?= url('orders') ?>"><i class="fas fa-box-open" aria-hidden="true"></i> My orders</a>
  <a href="<?= url('admin') ?>"><i class="fas fa-user-tie" aria-hidden="true"></i> Admin panel</a>
  <a href="<?= url('contact') ?>"><i class="fas fa-headset" aria-hidden="true"></i> Support</a>
  <hr class="label-rule my-1">
  <a class="is-exit" href="<?= url('login') ?>"><i class="fas fa-right-from-bracket" aria-hidden="true"></i> Log out</a>
</div>

<main id="main">
