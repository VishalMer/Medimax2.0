<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Forgot Password - MediMax.com' ?></title>
  <meta name="theme-color" content="#26547C">
  <meta name="description" content="Reset your MediMax account password to regain access to your orders, prescriptions, and saved medicines.">
  <link rel="icon" href="<?= asset('images/assets/MediMax_Logo.png') ?>">
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
      MediMax
    </a>

    <div>
      <p class="eyebrow">Licensed chemist &middot; Since 2019</p>
      <h2 class="title-page">Your medicine cabinet, kept in order.</h2>
      <ul class="auth-aside__list">
        <li><i class="fas fa-shield-halved" aria-hidden="true"></i><span>Encrypted, time-limited password recovery tokens.</span></li>
        <li><i class="fas fa-envelope-circle-check" aria-hidden="true"></i><span>Instructions sent directly to your registered address.</span></li>
        <li><i class="fas fa-headset" aria-hidden="true"></i><span>Our pharmacy support desk is available Mon–Sat.</span></li>
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
        MediMax
      </a>

      <?php if (!empty($sent)): ?>
        <p class="eyebrow">Password assistance</p>
        <h1>Check your inbox</h1>
        <p class="lede">We've sent password reset instructions to <strong><?= htmlspecialchars($submittedEmail ?? 'your email') ?></strong>.</p>

        <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3" role="alert" style="font-size: .88rem; line-height: 1.4;">
          <i class="fas fa-circle-info flex-shrink-0"></i>
          <div>Didn't get the email? Check your spam folder. The link expires in 15 minutes.</div>
        </div>

        <a href="<?= url('login') ?>" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block mb-2">
          <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i><span>Return to Sign in</span>
        </a>

        <p class="auth-alt">
          <a href="<?= url('forgot-password') ?>" style="color:var(--ink-60);font-weight:600;">Try another email</a>
        </p>

      <?php else: ?>
        <p class="eyebrow">Password assistance</p>
        <h1>Forgot password</h1>
        <p class="lede">Use the username or email you registered with.</p>

        <form method="post" id="forgotPasswordForm" action="<?= url('forgot-password') ?>" novalidate>
          <div class="mm-field">
            <label for="identifier">Username or email</label>
            <input class="mm-input" type="text" id="identifier" name="identifier"
                   autocomplete="username" placeholder="Username or email (e.g. userA)" required
                   value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
          </div>
          <p class="error-text" id="identifierError" aria-live="polite"></p>

          <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
            <i class="fas fa-paper-plane" aria-hidden="true"></i><span>Send reset link</span>
          </button>

          <p class="auth-alt">Remember your password? <a href="<?= url('login') ?>">Sign in</a></p>
          <p class="auth-alt mt-1">New to MediMax? <a href="<?= url('register') ?>">Create an account</a></p>
        </form>
      <?php endif; ?>

      <p class="auth-foot">
        <a href="<?= url() ?>" style="color:var(--ink-60)">&larr; Back to the shop</a>
      </p>

    </div>
  </main>

</body>
</html>
