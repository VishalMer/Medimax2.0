<?php ob_start(); ?>

<div class="form-card form-card--wide">
  <div class="form-card__head">
    <p class="eyebrow">Your account</p>
    <h2 class="title-page">Update profile</h2>
    <p>This is the name and photo colleagues see next to your actions in the console.</p>
  </div>

  <form action="<?= url('admin-update-profile-submit') ?>" method="POST" enctype="multipart/form-data">
    <div class="row g-4">

      <div class="col-md-7">
        <div class="mm-field">
          <label for="name">Full name</label>
          <input class="mm-input" type="text" id="name" name="name"
                 value="<?= htmlspecialchars($adminUser['name'] ?? '') ?>" required data-validation="required alpha min" data-min="2" autocomplete="name">
        </div>

        <div class="mm-field">
          <label for="email">Email address</label>
          <input class="mm-input" type="email" id="email" name="email"
                 value="<?= htmlspecialchars($adminUser['email'] ?? '') ?>" required data-validation="required email" autocomplete="email">
        </div>

        <div class="mm-field">
          <label for="role">Role</label>
          <input class="mm-input" type="text" id="role" name="role"
                 value="<?= htmlspecialchars($adminUser['role'] ?? 'admin') ?>" disabled>
          <p class="mb-0 mt-2" style="font-size:.76rem;color:var(--ink-40)">
            Only an owner can change a role. Ask one to change yours.
          </p>
        </div>
      </div>

      <div class="col-md-5">
        <div class="h-100 p-4 text-center d-flex flex-column align-items-center justify-content-center"
             style="background:var(--mist);border-radius:var(--r-md);border:1px solid var(--line)">
          <?php if (!empty($adminUser['image'])): ?>
            <img src="<?= asset('images/' . $adminUser['image']) ?>" alt="Your profile picture"
                 style="width:132px;height:132px;object-fit:cover;border-radius:50%;border:3px solid var(--paper);box-shadow:var(--sh-2)"
                 data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          <?php else: ?>
            <span class="d-grid" style="place-items:center;width:132px;height:132px;border-radius:50%;background:var(--navy);color:#fff;font-family:var(--font-display);font-size:2.6rem;font-weight:600;border:3px solid var(--paper);box-shadow:var(--sh-2)">
              <?= strtoupper(substr((string) ($adminUser['name'] ?? 'A'), 0, 1)) ?>
            </span>
          <?php endif; ?>

          <div class="w-100 mt-4 text-start">
            <label class="mm-label" for="image">Change picture</label>
            <input class="mm-input" type="file" id="image" name="image" accept="image/*" data-validation="file filesize" data-filesize="2048" data-filetypes="jpg,jpeg,png,webp">
            <p class="mb-0 mt-2" style="font-size:.74rem;color:var(--ink-40)">Square image, at least 200&times;200.</p>
          </div>
        </div>
      </div>

    </div>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-floppy-disk" aria-hidden="true"></i><span>Save profile</span>
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
