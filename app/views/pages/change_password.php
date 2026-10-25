<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Update Password - MediMax.com']);

$uName     = $user['name'] ?? '';
$uUsername = $user['username'] ?? '';
$uEmail    = $user['email'] ?? '';
$uRole     = $user['role'] ?? 'customer';
$displayName = !empty($uName) ? $uName : $uUsername;
$avatarInitial = strtoupper(substr($displayName ?: 'U', 0, 1));
$returnTo = $returnTo ?? url('profile');
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head mb-4">
      <p class="eyebrow">Account Security</p>
      <h1 class="title-page">Update Password</h1>
      <p class="lede mb-0">Ensure your account is protected with a strong, secure password.</p>
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

    <div class="row g-4">
      
      <!-- Left Column: User Summary & Password Guide -->
      <div class="col-lg-4">
        <div class="mm-card p-4 text-center">
          <div class="avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; border-radius: 50%;" data-avatar-tint="<?= htmlspecialchars($displayName) ?>">
            <?= htmlspecialchars($avatarInitial) ?>
          </div>
          <h2 class="h5 mb-1 text-dark"><?= htmlspecialchars($displayName) ?></h2>
          <p class="text-muted num mb-2" style="font-size: .88rem; color: var(--primary-teal, #26547C);">@<?= htmlspecialchars($uUsername) ?></p>
          <span class="badge bg-light text-secondary border px-2 py-1 mb-3 text-capitalize" style="font-size: .75rem;">
            <?= htmlspecialchars($uRole) ?> Account
          </span>

          <hr class="label-rule my-3">

          <!-- Security Tips Box -->
          <div class="text-start p-3 rounded-3" style="background: var(--mist, #F4F6F8); font-size: .82rem;">
            <p class="fw-bold mb-2 text-dark">
              <i class="fas fa-shield-halved text-primary me-2"></i>Password Requirements:
            </p>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-1 text-muted">
              <li><i class="fas fa-check text-success me-1"></i> 8 to 25 characters in length</li>
              <li><i class="fas fa-check text-success me-1"></i> At least one uppercase letter (A-Z)</li>
              <li><i class="fas fa-check text-success me-1"></i> At least one lowercase letter (a-z)</li>
              <li><i class="fas fa-check text-success me-1"></i> At least one number (0-9)</li>
              <li><i class="fas fa-check text-success me-1"></i> At least one special symbol (@$!%*?&)</li>
            </ul>
          </div>

          <hr class="label-rule my-3">

          <div class="d-grid gap-2">
            <a href="<?= url('profile') ?>" class="btn-mm btn-mm-ghost btn-mm-sm text-center">
              <i class="fas fa-user-pen" aria-hidden="true"></i><span>Edit profile details</span>
            </a>
            <a href="<?= url('orders') ?>" class="btn-mm btn-mm-ghost btn-mm-sm text-center">
              <i class="fas fa-box-open" aria-hidden="true"></i><span>View order history</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: Password Change Form -->
      <div class="col-lg-8">
        <div class="mm-card p-4 p-md-5">
          
          <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
              <h2 class="h5 mb-1" style="color: var(--primary-dark, #26547C);">Change Password</h2>
              <p class="text-muted mb-0" style="font-size: .84rem;">Enter your current password to authorize, then choose a strong new password.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              <i class="fas fa-lock me-1"></i>Encrypted
            </span>
          </div>

          <form action="<?= url('change-password') ?>" method="POST" id="changePasswordForm" novalidate>
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <!-- Current Password -->
            <div class="mm-field mb-4">
              <label for="old_password">Current Password <span class="text-danger">*</span></label>
              <div class="mm-field--pw">
                <input class="mm-input" type="password" id="old_password" name="old_password" required
                       data-validation="required"
                       placeholder="Enter your current password" autocomplete="current-password">
                <button class="pw-toggle" type="button" data-pw-toggle="old_password" aria-label="Toggle current password visibility">
                  <i class="far fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              <p class="error-text" id="old_passwordError" aria-live="polite"></p>
            </div>

            <hr class="label-rule my-4">

            <!-- New Password -->
            <div class="mm-field mb-3">
              <label for="new_password">New Password <span class="text-danger">*</span></label>
              <div class="mm-field--pw">
                <input class="mm-input" type="password" id="new_password" name="new_password" required
                       data-validation="required strongPassword min max" data-min="8" data-max="25"
                       placeholder="Enter new strong password" autocomplete="new-password">
                <button class="pw-toggle" type="button" data-pw-toggle="new_password" aria-label="Toggle new password visibility">
                  <i class="far fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              <p class="mb-0 mt-1" style="font-size: .75rem; color: var(--ink-40, #666);">
                Must be 8–25 characters with uppercase, lowercase, numbers, and symbols.
              </p>
              <p class="error-text" id="new_passwordError" aria-live="polite"></p>
            </div>

            <!-- Confirm New Password -->
            <div class="mm-field mb-4">
              <label for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
              <div class="mm-field--pw">
                <input class="mm-input" type="password" id="confirm_password" name="confirm_password" required
                       data-validation="required confirmPassword" data-password-id="new_password"
                       placeholder="Re-type new password" autocomplete="new-password">
                <button class="pw-toggle" type="button" data-pw-toggle="confirm_password" aria-label="Toggle confirm password visibility">
                  <i class="far fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              <p class="error-text" id="confirm_passwordError" aria-live="polite"></p>
            </div>

            <div class="d-flex align-items-center gap-3 pt-2">
              <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg">
                <i class="fas fa-key me-1"></i><span>Update password</span>
              </button>
              <a href="<?= htmlspecialchars($returnTo) ?>" class="btn-mm btn-mm-ghost">Cancel</a>
            </div>

          </form>

        </div>
      </div>

    </div>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
