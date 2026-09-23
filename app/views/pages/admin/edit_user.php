<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">People &middot; <span class="num">U<?= str_pad((string) ($user['id'] ?? 0), 3, '0', STR_PAD_LEFT) ?></span></p>
    <h2 class="title-page">Edit user</h2>
    <p>Changing a role takes effect the next time this person signs in.</p>
  </div>

  <form action="<?= url('admin-edit-user-submit') ?>" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'] ?? '') ?>">

    <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom:1px dashed var(--line-strong)">
      <?php if (!empty($user['image'])): ?>
        <img src="<?= asset('images/' . $user['image']) ?>" alt="" width="52" height="52"
             style="border-radius:50%;object-fit:cover;border:1px solid var(--line)"
             data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
      <?php else: ?>
        <span class="avatar" data-avatar-tint="<?= htmlspecialchars($user['name'] ?? '') ?>" aria-hidden="true">
          <?= strtoupper(substr((string) ($user['name'] ?? 'U'), 0, 1)) ?>
        </span>
      <?php endif; ?>
      <div>
        <strong style="display:block;font-size:.95rem"><?= htmlspecialchars($user['name'] ?? '') ?></strong>
        <span class="num" style="font-size:.76rem;color:var(--ink-40)"><?= htmlspecialchars($user['email'] ?? '') ?></span>
      </div>
    </div>

    <div class="mm-field">
      <label for="name">Full name</label>
      <input class="mm-input" type="text" id="name" name="name"
             value="<?= htmlspecialchars($user['name'] ?? '') ?>" required data-validation="required alpha min" data-min="2" autocomplete="name">
    </div>

    <div class="mm-field">
      <label for="email">Email address</label>
      <input class="mm-input" type="email" id="email" name="email"
             value="<?= htmlspecialchars($user['email'] ?? '') ?>" required data-validation="required email" autocomplete="email">
    </div>

    <div class="mm-field">
      <label for="role">Role</label>
      <input class="mm-input" type="text" id="role" name="role" list="roleOptions"
             value="<?= htmlspecialchars($user['role'] ?? '') ?>" required data-validation="required alpha">
      <datalist id="roleOptions">
        <option value="customer">
        <option value="admin">
        <option value="owner">
      </datalist>
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
