<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Edit Profile - MediMax.com']);

$uName     = $user['name'] ?? '';
$uUsername = $user['username'] ?? '';
$uEmail    = $user['email'] ?? '';
$uPhone    = $user['phone'] ?? '';
$uRole     = $user['role'] ?? 'customer';
$uCreated  = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'Recently';

$displayName = !empty($uName) ? $uName : $uUsername;
$avatarInitial = strtoupper(substr($displayName ?: 'U', 0, 1));
$returnTo = $returnTo ?? url();
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head mb-4">
      <p class="eyebrow">Account Settings</p>
      <h1 class="title-page">My Profile</h1>
      <p class="lede mb-0">Manage your personal details, dispensing contact information, and delivery name.</p>
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
      
      <!-- Left Column: User Summary Card -->
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

          <div class="text-start" style="font-size: .84rem;">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted"><i class="fas fa-envelope me-1"></i>Email:</span>
              <span class="num text-dark fw-semibold" style="word-break: break-all;"><?= htmlspecialchars($uEmail) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted"><i class="fas fa-phone me-1"></i>Phone:</span>
              <span class="num text-dark"><?= !empty($uPhone) ? htmlspecialchars($uPhone) : '<span class="text-muted italic">Not set</span>' ?></span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted"><i class="fas fa-calendar-check me-1"></i>Member since:</span>
              <span class="num text-dark"><?= htmlspecialchars($uCreated) ?></span>
            </div>
          </div>

          <hr class="label-rule my-3">

          <div class="d-grid gap-2">
            <a href="<?= url('orders') ?>" class="btn-mm btn-mm-ghost btn-mm-sm text-center">
              <i class="fas fa-box-open" aria-hidden="true"></i><span>View order history</span>
            </a>
            <a href="<?= url('wishlist') ?>" class="btn-mm btn-mm-ghost btn-mm-sm text-center">
              <i class="far fa-heart" aria-hidden="true"></i><span>View saved items</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: Profile Edit Form -->
      <div class="col-lg-8">
        <div class="mm-card p-4 p-md-5">
          
          <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
              <h2 class="h5 mb-1" style="color: var(--primary-dark, #26547C);">Edit Profile Details</h2>
              <p class="text-muted mb-0" style="font-size: .84rem;">Update your display name and contact numbers for delivery.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              Active Member
            </span>
          </div>

          <form action="<?= url('profile') ?>" method="POST" id="profileForm" novalidate>
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
            
            <!-- Unique Username (Read-only credential) -->
            <div class="mm-field mb-3">
              <label for="username">Username (Unique handle)</label>
              <input class="mm-input" type="text" id="username" name="username_display"
                     value="@<?= htmlspecialchars(ltrim($uUsername, '@')) ?>" readonly disabled
                     style="cursor: not-allowed; background: var(--mist, #F4F6F8);">
              <p class="mb-0 mt-1" style="font-size: .75rem; color: var(--ink-40, #666);">
                <i class="fas fa-lock me-1"></i>Your unique username is assigned to your account and is used for login.
              </p>
            </div>

            <!-- Email Address (Read-only credential) -->
            <div class="mm-field mb-3">
              <label for="email">Email address</label>
              <input class="mm-input" type="email" id="email" name="email_display"
                     value="<?= htmlspecialchars($uEmail) ?>" readonly disabled
                     style="cursor: not-allowed; background: var(--mist, #F4F6F8);">
              <p class="mb-0 mt-1" style="font-size: .75rem; color: var(--ink-40, #666);">
                <i class="fas fa-lock me-1"></i>Primary email address linked to your account.
              </p>
            </div>

            <!-- Full Name (Editable, empty by default on registration) -->
            <div class="mm-field mb-3">
              <label for="name">
                Full Name (Display Name)
                <span class="text-muted" style="font-weight: normal; font-size: .8rem;">— empty by default</span>
              </label>
              <input class="mm-input" type="text" id="name" name="name"
                     placeholder="e.g. Rahul Sharma"
                     data-validation="max" data-max="100"
                     value="<?= htmlspecialchars($uName) ?>">
              <p class="mb-0 mt-1" style="font-size: .75rem; color: var(--ink-60, #555);">
                <i class="fas fa-circle-info text-primary me-1"></i>
                When filled, this name is displayed in the top right header button and used on your prescription order slips.
                If left empty, your username <strong>@<?= htmlspecialchars($uUsername) ?></strong> will be displayed.
              </p>
            </div>
            <p class="error-text" id="nameError" aria-live="polite"></p>

            <!-- Mobile Phone Number -->
            <div class="mm-field mb-4">
              <label for="phone">Phone / Mobile number</label>
              <input class="mm-input num" type="tel" id="phone" name="phone"
                     placeholder="e.g. +91 98765 43210"
                     data-validation="max" data-max="20"
                     value="<?= htmlspecialchars($uPhone) ?>">
              <p class="mb-0 mt-1" style="font-size: .75rem; color: var(--ink-40, #666);">
                Used for SMS delivery notifications and pharmacist verification calls.
              </p>
            </div>
            <p class="error-text" id="phoneError" aria-live="polite"></p>

            <div class="d-flex align-items-center gap-3 pt-2">
              <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg">
                <i class="fas fa-floppy-disk me-1"></i><span>Save profile changes</span>
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
