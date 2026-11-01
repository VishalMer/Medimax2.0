<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">People &middot; <span class="num">U<?= str_pad((string) ($user['id'] ?? 0), 3, '0', STR_PAD_LEFT) ?></span></p>
    <h2 class="title-page">Edit user</h2>
    <p>Changing a role takes effect the next time this person signs in.</p>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <?= htmlspecialchars($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <form action="<?= url('admin-edit-user-submit') ?>" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars((string)($user['id'] ?? '')) ?>">

    <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom:1px dashed var(--line-strong)">
      <img src="<?= profileImage($user['image'] ?? 'default_pfp.jpg') ?>" alt="<?= htmlspecialchars($user['name'] ?? '') ?>" width="52" height="52"
           style="border-radius:50%;object-fit:cover;border:1px solid var(--line)">
      <div>
        <strong style="display:block;font-size:.95rem"><?= htmlspecialchars($user['name'] ?? '') ?></strong>
        <span class="num" style="font-size:.76rem;color:var(--ink-40)"><?= htmlspecialchars($user['email'] ?? '') ?></span>
      </div>
    </div>

    <div class="mm-field">
      <label for="name">Full name</label>
      <input class="mm-input" type="text" id="name" name="name"
             value="<?= htmlspecialchars($user['name'] ?? '') ?>" required autocomplete="name">
    </div>

    <div class="mm-field">
      <label for="email">Email address</label>
      <input class="mm-input" type="email" id="email" name="email"
             value="<?= htmlspecialchars($user['email'] ?? '') ?>" required autocomplete="email">
    </div>

    <div class="mm-field">
      <label for="role">Role</label>
      <select class="mm-input" id="role" name="role" required>
        <option value="customer" <?= ($user['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
        <option value="owner" <?= ($user['role'] ?? '') === 'owner' ? 'selected' : '' ?>>Owner</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-floppy-disk" aria-hidden="true"></i><span>Save changes</span>
      </button>
      <a class="btn-mm btn-mm-ghost btn-mm-block" href="<?= url('admin-users') ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Back to users</span>
      </a>
    </div>
  </form>
</div>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
