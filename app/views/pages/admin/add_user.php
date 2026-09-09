<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">People</p>
    <h2 class="title-page">Add a user</h2>
    <p>Create an account for a colleague or a customer. They can change the password after their first sign-in.</p>
  </div>

  <form action="<?= url('admin-add-user-submit') ?>" method="POST">
    <div class="mm-field">
      <label for="name">Full name</label>
      <input class="mm-input" type="text" id="name" name="name" required placeholder="Priya Patel" autocomplete="name">
    </div>

    <div class="mm-field">
      <label for="email">Email address</label>
      <input class="mm-input" type="email" id="email" name="email" required placeholder="priya@example.com" autocomplete="email">
    </div>

    <div class="mm-field">
      <label for="role">Role</label>
      <input class="mm-input" type="text" id="role" name="role" list="roleOptions" required placeholder="customer">
      <datalist id="roleOptions">
        <option value="customer">
        <option value="admin">
        <option value="owner">
      </datalist>
      <p class="mb-0 mt-2" style="font-size:.76rem;color:var(--ink-40)">
        Customers see the shop only. Admins reach this console. Owners can change roles.
      </p>
    </div>

    <div class="mm-field mm-field--pw">
      <label for="password">Temporary password</label>
      <input class="mm-input" type="password" id="password" name="password" required
             placeholder="At least 8 characters" autocomplete="new-password">
      <button class="pw-toggle" type="button" data-pw-toggle="password" aria-label="Show password">
        <i class="far fa-eye" aria-hidden="true"></i>
      </button>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-user-plus" aria-hidden="true"></i><span>Add user</span>
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
