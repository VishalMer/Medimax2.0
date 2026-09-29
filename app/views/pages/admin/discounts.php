<?php
ob_start();
$rows = $coupons ?? [];
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">Marketing</p>
    <h2 class="title-section mt-2 mb-0">Discount & Coupons</h2>
    <p><span class="num"><?= count($rows) ?></span> active campaigns.</p>
  </div>
  <a class="btn-mm btn-mm-amber btn-mm-sm" href="<?= url('admin-add-discount') ?>">
    <i class="fas fa-plus" aria-hidden="true"></i><span>Add discount</span>
  </a>
</div>

<div class="mm-table-wrap">
  <div class="mm-table-scroll">
    <table class="mm-table">
      <thead>
        <tr>
          <th scope="col">Code</th>
          <th scope="col">Type</th>
          <th scope="col">Discount</th>
          <th scope="col">Min. Order</th>
          <th scope="col">Valid Until</th>
          <th scope="col">Status</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $c): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($c['code']) ?></strong>
              </td>
              <td><?= ucfirst(htmlspecialchars($c['discountType'])) ?></td>
              <td>
                <?php if ($c['discountType'] === 'percentage'): ?>
                  <?= htmlspecialchars($c['discountValue']) ?>% (Max &#8377;<?= $c['maximumDiscount'] ?: 'No limit' ?>)
                <?php else: ?>
                  &#8377;<?= htmlspecialchars($c['discountValue']) ?>
                <?php endif; ?>
              </td>
              <td><span class="num">&#8377;<?= number_format((float) ($c['minimumOrderValue']), 2) ?></span></td>
              <td><span class="num"><?= date('d M Y', strtotime($c['expiryDate'])) ?></span></td>
              <td>
                 <span class="pill pill--<?= $c['isActive'] ? 'success' : 'neutral' ?> pill--plain">
                   <?= $c['isActive'] ? 'Active' : 'Inactive' ?>
                 </span>
              </td>
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-discount') ?>&amp;id=<?= $c['id'] ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" class="btn-mm btn-mm-danger btn-mm-sm"
                        data-delete-discount="<?= $c['id'] ?>"
                        data-discount-code="<?= htmlspecialchars($c['code']) ?>">
                  <i class="fa-solid fa-trash" aria-hidden="true"></i><span>Delete</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="mm-table__empty">
              <i class="fas fa-ticket" aria-hidden="true"></i>
              No discounts yet. <a href="<?= url('admin-add-discount') ?>">Add the first one</a>.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('[data-delete-discount]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var code = btn.getAttribute('data-discount-code') || 'this coupon';
    if (!window.confirm('Delete coupon "' + code + '"? This cannot be undone.')) return;
    if (window.MediMax) window.MediMax.toast('Delete requested for ' + code, 'fa-trash');
  });
});
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
