<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Your account</p>
    <h2 class="title-page">Change password</h2>
    <p>You stay signed in on this device. Other sessions are signed out.</p>
  </div>

  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3 shadow-sm" role="alert">
      <i class="fas fa-circle-exclamation flex-shrink-0 text-danger"></i>
      <div><?= $errorMessage ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3 shadow-sm" role="alert">
      <i class="fas fa-circle-check flex-shrink-0 text-success"></i>
      <div><?= $successMessage ?></div>
    </div>
  <?php endif; ?>

  <form action="<?= url('admin-update-password') ?>" method="POST" id="passwordForm">
    <div class="mm-field">
      <label for="old_password">Current password</label>
      <div class="mm-field--pw">
        <input class="mm-input" type="password" id="old_password" name="old_password" required data-validation="required"
          placeholder="••••••••" autocomplete="current-password">
        <button class="pw-toggle" type="button" data-pw-toggle="old_password" aria-label="Show password">
          <i class="far fa-eye" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <hr class="label-rule">

    <div class="mm-field">
      <label for="new_password">New password</label>
      <div class="mm-field--pw">
        <input class="mm-input" type="password" id="new_password" name="new_password" required data-validation="required strongPassword min max" data-min="8" data-max="25"
          placeholder="At least 8 characters" autocomplete="new-password">
        <button class="pw-toggle" type="button" data-pw-toggle="new_password" aria-label="Show password">
          <i class="far fa-eye" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <div class="mm-field">
      <label for="confirm_password">Confirm new password</label>
      <div class="mm-field--pw">
        <input class="mm-input" type="password" id="confirm_password" name="confirm_password" required data-validation="required confirmPassword" data-password-id="new_password"
          placeholder="Type it again" autocomplete="new-password">
        <button class="pw-toggle" type="button" data-pw-toggle="confirm_password" aria-label="Show password">
          <i class="far fa-eye" aria-hidden="true"></i>
        </button>
      </div>
    </div>

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

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
