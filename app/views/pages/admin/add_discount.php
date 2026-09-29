<?php ob_start(); ?>

<div class="form-card" style="max-width: 600px;">
  <div class="form-card__head">
    <p class="eyebrow">Marketing</p>
    <h2 class="title-page">Add a discount</h2>
    <p>Create a new coupon code to offer discounts to customers.</p>
  </div>

  <form action="#" method="POST">
    <div class="mm-field">
      <label for="code">Coupon Code</label>
      <input class="mm-input" type="text" id="code" name="code" required placeholder="e.g. SUMMER20" style="text-transform: uppercase;">
    </div>

    <div class="mm-field">
      <label for="title">Discount Name</label>
      <input class="mm-input" type="text" id="title" name="title" required placeholder="20% Summer Sale">
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="discountType">Discount Type</label>
        <select class="mm-input" id="discountType" name="discountType" required>
          <option value="percentage">Percentage (%)</option>
          <option value="fixed">Fixed Amount (&#8377;)</option>
        </select>
      </div>
      <div class="col-md-6 mm-field">
        <label for="discountValue">Value</label>
        <input class="mm-input num" type="number" id="discountValue" name="discountValue" required placeholder="20">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="minimumOrderValue">Minimum Order (&#8377;)</label>
        <input class="mm-input num" type="number" id="minimumOrderValue" name="minimumOrderValue" required placeholder="499">
      </div>
      <div class="col-md-6 mm-field">
        <label for="maximumDiscount">Maximum Discount (&#8377;)</label>
        <input class="mm-input num" type="number" id="maximumDiscount" name="maximumDiscount" placeholder="Leave empty for no limit">
      </div>
    </div>
    
    <div class="mm-field">
      <label for="applicableCategory">Applicable Category</label>
      <select class="mm-input" id="applicableCategory" name="applicableCategory">
        <option value="All">All Products</option>
        <?php foreach (DummyData::getCategories() as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mm-field">
      <label for="expiryDate">Expiry Date</label>
      <input class="mm-input" type="date" id="expiryDate" name="expiryDate" required>
    </div>

    <div class="mm-field form-check">
      <input class="form-check-input" type="checkbox" id="isActive" name="isActive" checked>
      <label class="form-check-label" for="isActive">Active</label>
    </div>

    <div class="form-actions mt-4">
      <button type="button" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block" onclick="window.MediMax.toast('Discount created successfully (Demo)', 'fa-check'); setTimeout(() => window.location.href='<?= url('admin-discounts') ?>', 1000);">
        <i class="fas fa-plus" aria-hidden="true"></i><span>Create discount</span>
      </button>
      <a class="btn-mm btn-mm-ghost btn-mm-block" href="<?= url('admin-discounts') ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Cancel</span>
      </a>
    </div>
  </form>
</div>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
