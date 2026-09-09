<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Register - MediMax.com' ?></title>
  <meta name="theme-color" content="#26547C">
  <meta name="description" content="Create a MediMax account to order medicine, track deliveries and save a wishlist.">
  <link rel="icon" href="<?= asset('images/MediMax_Logo.png') ?>">
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <!-- custom.css (loads the type stack) -->
  <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
</head>
<body class="auth-body">

  <aside class="auth-aside">
    <a class="auth-aside__brand" href="<?= url() ?>">
      <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
      MediMax
    </a>

    <div>
      <p class="eyebrow">Two minutes, no paperwork</p>
      <h2 class="title-page">Open an account at the counter.</h2>
      <ul class="auth-aside__list">
        <li><i class="fas fa-shield-heart" aria-hidden="true"></i><span>Genuine stock only, dispatched from our own shelves.</span></li>
        <li><i class="fas fa-receipt" aria-hidden="true"></i><span>Every order, invoice and delivery stage in one place.</span></li>
        <li><i class="fas fa-user-doctor" aria-hidden="true"></i><span>A registered pharmacist checks prescription orders.</span></li>
      </ul>
    </div>

    <p class="num" style="font-size:.72rem;color:rgba(255,255,255,.5)">
      Mon&ndash;Sat&nbsp;&middot;&nbsp;08:00&ndash;22:00 IST&nbsp;&middot;&nbsp;+91 12345 67890
    </p>
  </aside>

  <main class="auth-main">
    <div class="auth-card">
      <a class="auth-card__brand" href="<?= url() ?>">
        <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
        MediMax
      </a>

      <p class="eyebrow">Get started</p>
      <h1>Create your account</h1>
      <p class="lede">You only need a name, an email and a password.</p>

      <form method="post" id="registrationForm" action="<?= url('register') ?>" novalidate>
        <div class="mm-field">
          <label for="username">Your name</label>
          <input class="mm-input" type="text" id="username" name="name"
                 autocomplete="name" placeholder="Priya Patel" required>
        </div>
        <p class="error-text" id="usernameError" aria-live="polite"></p>

        <div class="mm-field">
          <label for="email">Email</label>
          <input class="mm-input" type="email" id="email" name="email"
                 autocomplete="email" placeholder="you@example.com" required>
        </div>
        <p class="error-text" id="emailError" aria-live="polite"></p>

        <div class="mm-field mm-field--pw">
          <label for="password">Password</label>
          <input class="mm-input" type="password" id="password" name="password"
                 autocomplete="new-password" placeholder="At least 8 characters" required>
          <button class="pw-toggle" type="button" data-pw-toggle="password" aria-label="Show password">
            <i class="far fa-eye" aria-hidden="true"></i>
          </button>
        </div>
        <p class="error-text" id="passwordError" aria-live="polite"></p>

        <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
          <i class="fas fa-user-plus" aria-hidden="true"></i><span>Create account</span>
        </button>

        <p class="auth-alt">Already registered? <a href="<?= url('login') ?>">Sign in</a></p>
      </form>

      <p class="auth-foot">
        By creating an account you agree to our terms of use and privacy policy.<br>
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
