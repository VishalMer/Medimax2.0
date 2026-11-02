<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Edit Profile - MediMax.com']);

$uName     = $user['name'] ?? '';
$uUsername = $user['username'] ?? '';
$uEmail    = $user['email'] ?? '';
$uPhone    = $user['phone'] ?? '';
$uRole     = $user['role'] ?? 'customer';
$uCreated  = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'Recently';

$displayName = !empty($uName) ? $uName : $uUsername;
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


    <div class="row g-4">
      
      <!-- Left Column: User Summary Card -->
      <div class="col-lg-4">
        <div class="mm-card p-4 text-center">
          <div class="position-relative d-inline-block mx-auto mb-3">
            <div class="avatar avatar--profile">
              <img id="avatarPreviewSummary" src="<?= profileImage($user['image'] ?? 'default_pfp.jpg') ?>" alt="<?= htmlspecialchars($displayName) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </div>
            <button type="button" class="avatar-badge-btn" id="summaryChangePhotoBtn" title="Change profile picture" aria-label="Change profile picture"
                    style="position: absolute; bottom: 0; right: 0; width: 30px; height: 30px; background: var(--primary-teal, #26547C); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor: pointer; transition: transform .15s ease, background-color .15s ease;">
              <i class="fas fa-camera" style="font-size: .75rem;" aria-hidden="true"></i>
            </button>
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
            <a href="<?= url('change-password') ?>" class="btn-mm btn-mm-ghost btn-mm-sm text-center">
              <i class="fas fa-key" aria-hidden="true"></i><span>Update password</span>
            </a>
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
              <p class="text-muted mb-0" style="font-size: .84rem;">Update your profile picture, display name and contact numbers for delivery.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              Active Member
            </span>
          </div>

          <form action="<?= url('profile') ?>" method="POST" id="profileForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <!-- Profile Picture Section -->
            <div class="mm-field mb-4 p-3 rounded-3" style="background: var(--mist, #F4F6F8); border: 1px solid var(--line, #e2e8f0);">
              <label class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold" style="color: var(--primary-dark, #26547C);">
                  <i class="fas fa-image me-1"></i>Profile Picture
                </span>
                <span class="text-muted" style="font-weight: normal; font-size: .75rem;">JPG, PNG, WebP up to 2MB</span>
              </label>
              
              <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                <div class="avatar avatar--profile flex-shrink-0" style="width: 68px; height: 68px; border: 2px solid #fff; box-shadow: var(--sh-1, 0 1px 3px rgba(0,0,0,0.1));">
                  <img id="avatarPreviewForm" src="<?= profileImage($user['image'] ?? 'default_pfp.jpg') ?>" alt="Profile Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                
                <div class="flex-grow-1 w-100">
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <label for="image" class="btn-mm btn-mm-ghost btn-mm-sm mb-0" style="cursor: pointer;">
                      <i class="fas fa-cloud-arrow-up me-1"></i><span>Choose new picture</span>
                    </label>
                    <input class="d-none" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/jpg"
                           data-validation="file filesize" data-filesize="2048" data-filetypes="jpg,jpeg,png,webp">
                    
                    <span id="fileNameDisplay" class="text-muted" style="font-size: .8rem;">No file chosen</span>

                    <?php if (!empty($user['image']) && $user['image'] !== 'default_pfp.jpg'): ?>
                      <div class="ms-sm-auto form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                        <label class="form-check-label text-danger" for="remove_image" style="font-size: .8rem; cursor: pointer;">
                          <i class="fas fa-trash-alt me-1"></i>Reset to default
                        </label>
                      </div>
                    <?php endif; ?>
                  </div>
                  <p class="error-text mt-1 mb-0" id="imageError" aria-live="polite"></p>
                </div>
              </div>
            </div>
            
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  var fileInput = document.getElementById('image');
  var formPreview = document.getElementById('avatarPreviewForm');
  var summaryPreview = document.getElementById('avatarPreviewSummary');
  var fileNameDisplay = document.getElementById('fileNameDisplay');
  var removeCheck = document.getElementById('remove_image');
  var summaryBtn = document.getElementById('summaryChangePhotoBtn');
  var errorText = document.getElementById('imageError');
  
  var originalSrc = formPreview ? formPreview.src : '';
  var defaultPfpSrc = <?= json_encode(profileImage('default_pfp.jpg')) ?>;

  if (summaryBtn && fileInput) {
    summaryBtn.addEventListener('click', function(e) {
      e.preventDefault();
      fileInput.click();
    });
    summaryBtn.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.1)';
      this.style.backgroundColor = 'var(--teal, #168996)';
    });
    summaryBtn.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1)';
      this.style.backgroundColor = 'var(--primary-teal, #26547C)';
    });
  }

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        var file = this.files[0];
        var maxBytes = 2 * 1024 * 1024; // 2MB
        var allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        var ext = file.name.split('.').pop().toLowerCase();

        if (!allowedExts.includes(ext)) {
          if (errorText) {
            errorText.textContent = 'Only JPG, JPEG, PNG, or WebP images are allowed.';
            errorText.style.display = 'block';
          }
          this.value = '';
          if (fileNameDisplay) {
            fileNameDisplay.textContent = 'Invalid file type';
            fileNameDisplay.className = 'text-danger';
          }
          return;
        }

        if (file.size > maxBytes) {
          if (errorText) {
            errorText.textContent = 'Image size must be less than 2MB.';
            errorText.style.display = 'block';
          }
          this.value = '';
          if (fileNameDisplay) {
            fileNameDisplay.textContent = 'File exceeds 2MB limit';
            fileNameDisplay.className = 'text-danger';
          }
          return;
        }

        if (errorText) {
          errorText.textContent = '';
          errorText.style.display = 'none';
        }

        var sizeKb = Math.round(file.size / 1024);
        if (fileNameDisplay) {
          fileNameDisplay.textContent = file.name + ' (' + sizeKb + ' KB)';
          fileNameDisplay.className = 'text-success fw-semibold';
        }

        if (removeCheck) {
          removeCheck.checked = false;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
          if (formPreview) formPreview.src = e.target.result;
          if (summaryPreview) summaryPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        if (fileNameDisplay) {
          fileNameDisplay.textContent = 'No file chosen';
          fileNameDisplay.className = 'text-muted';
        }
      }
    });
  }

  if (removeCheck) {
    removeCheck.addEventListener('change', function() {
      if (this.checked) {
        if (fileInput) fileInput.value = '';
        if (fileNameDisplay) {
          fileNameDisplay.textContent = 'Will reset to default picture';
          fileNameDisplay.className = 'text-muted fst-italic';
        }
        if (formPreview) formPreview.src = defaultPfpSrc;
        if (summaryPreview) summaryPreview.src = defaultPfpSrc;
      } else {
        if (fileNameDisplay) {
          fileNameDisplay.textContent = 'No file chosen';
          fileNameDisplay.className = 'text-muted';
        }
        if (formPreview) formPreview.src = originalSrc;
        if (summaryPreview) summaryPreview.src = originalSrc;
      }
    });
  }
});
</script>

<?php Controller::partial('layouts/footer'); ?>
