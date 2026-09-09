<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Your account</p>
    <h2 class="title-page">Change password</h2>
    <p>You stay signed in on this device. Other sessions are signed out.</p>
  </div>

  <form action="<?= url('admin-update-password-submit') ?>" method="POST" id="passwordForm">
    <div class="mm-field mm-field--pw">
      <label for="old_password">Current password</label>
      <input class="mm-input" type="password" id="old_password" name="old_password" required
             placeholder="••••••••" autocomplete="current-password">
      <button class="pw-toggle" type="button" data-pw-toggle="old_password" aria-label="Show password">
        <i class="far fa-eye" aria-hidden="true"></i>
      </button>
    </div>

    <hr class="label-rule">

    <div class="mm-field mm-field--pw">
      <label for="new_password">New password</label>
      <input class="mm-input" type="password" id="new_password" name="new_password" required
             placeholder="At least 8 characters" autocomplete="new-password">
      <button class="pw-toggle" type="button" data-pw-toggle="new_password" aria-label="Show password">
        <i class="far fa-eye" aria-hidden="true"></i>
      </button>
    </div>

    <div class="mm-field mm-field--pw">
      <label for="confirm_password">Confirm new password</label>
      <input class="mm-input" type="password" id="confirm_password" name="confirm_password" required
             placeholder="Type it again" autocomplete="new-password">
      <button class="pw-toggle" type="button" data-pw-toggle="confirm_password" aria-label="Show password">
        <i class="far fa-eye" aria-hidden="true"></i>
      </button>
    </div>
    <p class="error-text" id="confirmError" aria-live="polite"
       style="display:block;min-height:1rem;margin:-.7rem 0 .5rem;font-size:.76rem;font-weight:600;color:var(--danger)"></p>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-key" aria-hidden="true"></i><span>Update password</span>
      </button>
      <a class="btn-mm btn-mm-ghost btn-mm-block" href="<?= url('admin') ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Back to dashboard</span>
      </a>
    </div>
  </form>
</div>

<script>
// Front-of-house check: catch the mismatch before the round trip.
(function () {
  var form = document.getElementById('passwordForm');
  if (!form) return;
  var pw = document.getElementById('new_password');
  var cf = document.getElementById('confirm_password');
  var out = document.getElementById('confirmError');

  var check = function () {
    var bad = cf.value && pw.value !== cf.value;
    out.textContent = bad ? 'The two new passwords do not match.' : '';
    cf.setAttribute('aria-invalid', bad ? 'true' : 'false');
    cf.style.borderColor = bad ? 'var(--danger)' : '';
    return !bad;
  };

  cf.addEventListener('input', check);
  pw.addEventListener('input', function () { if (cf.value) check(); });
  form.addEventListener('submit', function (e) {
    if (!check()) { e.preventDefault(); cf.focus(); }
  });
})();
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
