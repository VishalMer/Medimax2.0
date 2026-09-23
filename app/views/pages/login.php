<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Login - MediMax.com' ?></title>
  <meta name="theme-color" content="#26547C">
  <meta name="description" content="Sign in to MediMax to track orders, reorder medicine and manage your wishlist.">
  <link rel="icon" href="<?= asset('images/MediMax_Logo.png') ?>">
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <!-- custom.css (loads the type stack) -->
  <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="auth-body">

  <!-- Left: the counter you are walking up to --------------------------- -->
  <aside class="auth-aside">
    <a class="auth-aside__brand" href="<?= url() ?>">
      <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
      MediMax
    </a>

    <div>
      <p class="eyebrow">Licensed chemist &middot; Since 2019</p>
      <h2 class="title-page">Your medicine cabinet, kept in order.</h2>
      <ul class="auth-aside__list">
        <li><i class="fas fa-clock-rotate-left" aria-hidden="true"></i><span>Reorder anything you have bought before in two taps.</span></li>
        <li><i class="fas fa-truck-fast" aria-hidden="true"></i><span>Watch payment and delivery status until the parcel lands.</span></li>
        <li><i class="fas fa-heart" aria-hidden="true"></i><span>Keep a wishlist of the packs you buy every month.</span></li>
      </ul>
    </div>

    <p class="num" style="font-size:.72rem;color:rgba(255,255,255,.5)">
      Mon&ndash;Sat&nbsp;&middot;&nbsp;08:00&ndash;22:00 IST&nbsp;&middot;&nbsp;+91 12345 67890
    </p>
  </aside>

  <!-- Right: the form ---------------------------------------------------- -->
  <main class="auth-main">
    <div class="auth-card">
      <a class="auth-card__brand" href="<?= url() ?>">
        <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
        MediMax
      </a>

      <p class="eyebrow">Welcome back</p>
      <h1>Sign in</h1>
      <p class="lede">Use the username or email you registered with.</p>

      <form method="post" id="loginForm" action="<?= url('login') ?>" novalidate>
        <div class="mm-field">
          <label for="identifier">Username or email</label>
          <input class="mm-input" type="text" id="identifier" name="identifier"
               autocomplete="username" placeholder="you@example.com" required data-validation="required">
        </div>
        <p class="error-text" id="identifierError" aria-live="polite"></p>

        <div class="mm-field mm-field--pw">
          <label for="password">Password</label>
          <input class="mm-input" type="password" id="password" name="password"
               autocomplete="current-password" placeholder="••••••••" required data-validation="required">
          <button class="pw-toggle" type="button" data-pw-toggle="password" aria-label="Show password">
            <i class="far fa-eye" aria-hidden="true"></i>
          </button>
        </div>
        <p class="error-text" id="passwordError" aria-live="polite"></p>

        <p class="text-end mb-3" style="font-size:.82rem">
          <a href="#" style="color:var(--navy);font-weight:600;text-decoration:none">Forgot your password?</a>
        </p>

        <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
          <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i><span>Sign in</span>
        </button>

        <p class="auth-alt">New to MediMax? <a href="<?= url('register') ?>">Create an account</a></p>
      </form>

      <p class="auth-foot">
        <a href="<?= url() ?>" style="color:var(--ink-60)">&larr; Back to the shop</a>
      </p>
    </div>
  </main>

  <!-- Bootstrap JS CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- app.js -->
  <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
