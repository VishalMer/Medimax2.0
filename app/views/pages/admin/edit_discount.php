<?php 
ob_start(); 
$c = $coupon ?? [];
if (empty($c)) {
    echo "<p>Coupon not found.</p>";
    exit;
}
?>

<div class="form-card" style="max-width: 600px;">
  <div class="form-card__head">
    <p class="eyebrow">Marketing</p>
    <h2 class="title-page">Edit discount</h2>
    <p>Update the settings for <?= htmlspecialchars($c['code']) ?>.</p>
  </div>

  <form action="#" method="POST">
    <div class="mm-field">
      <label for="code">Coupon Code</label>
      <input class="mm-input" type="text" id="code" name="code" required value="<?= htmlspecialchars($c['code']) ?>" style="text-transform: uppercase;">
    </div>

    <div class="mm-field">
      <label for="title">Discount Name</label>
      <input class="mm-input" type="text" id="title" name="title" required value="<?= htmlspecialchars($c['title']) ?>">
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="discountType">Discount Type</label>
        <select class="mm-input" id="discountType" name="discountType" required>
          <option value="percentage" <?= $c['discountType'] == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
          <option value="fixed" <?= $c['discountType'] == 'fixed' ? 'selected' : '' ?>>Fixed Amount (&#8377;)</option>
        </select>
      </div>
      <div class="col-md-6 mm-field">
        <label for="discountValue">Value</label>
        <input class="mm-input num" type="number" id="discountValue" name="discountValue" required value="<?= htmlspecialchars($c['discountValue']) ?>">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="minimumOrderValue">Minimum Order (&#8377;)</label>
        <input class="mm-input num" type="number" id="minimumOrderValue" name="minimumOrderValue" required value="<?= htmlspecialchars($c['minimumOrderValue']) ?>">
      </div>
      <div class="col-md-6 mm-field">
        <label for="maximumDiscount">Maximum Discount (&#8377;)</label>
        <input class="mm-input num" type="number" id="maximumDiscount" name="maximumDiscount" value="<?= htmlspecialchars($c['maximumDiscount']) ?>">
      </div>
    </div>
    
    <div class="mm-field">
      <label for="applicableCategory">Applicable Category</label>
      <select class="mm-input" id="applicableCategory" name="applicableCategory">
        <option value="All" <?= $c['applicableCategory'] == 'All' ? 'selected' : '' ?>>All Products</option>
        <?php foreach (['Vitamins', 'Supplements', 'Skincare', 'Personal Care', 'First Aid', 'Baby Care', 'Hair Care', 'Beverages'] as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $c['applicableCategory'] == $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mm-field">
      <label for="expiryDate">Expiry Date</label>
      <input class="mm-input" type="date" id="expiryDate" name="expiryDate" required value="<?= htmlspecialchars(date('Y-m-d', strtotime($c['expiryDate']))) ?>">
    </div>

    <div class="mm-field form-check">
      <input class="form-check-input" type="checkbox" id="isActive" name="isActive" <?= $c['isActive'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="isActive">Active</label>
    </div>

    <div class="form-actions mt-4">
      <button type="button" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block" onclick="window.MediMax.toast('Discount updated successfully (Demo)', 'fa-check'); setTimeout(() => window.location.href='<?= url('admin-discounts') ?>', 1000);">
        <i class="fas fa-save" aria-hidden="true"></i><span>Save changes</span>
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
