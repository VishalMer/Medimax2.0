<?php
ob_start();
$rows = $coupons ?? [];
$activeCount = 0;
foreach ($rows as $c) {
    if (!empty($c['isActive'])) { $activeCount++; }
}
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">Marketing</p>
    <h2 class="title-section mt-2 mb-0">Discount & Coupons</h2>
    <p><span class="num"><?= count($rows) ?></span> campaigns (<span class="num"><?= $activeCount ?></span> active).</p>
  </div>
  <a class="btn-mm btn-mm-amber btn-mm-sm" href="<?= url('admin-add-discount') ?>">
    <i class="fas fa-plus" aria-hidden="true"></i><span>Add discount</span>
  </a>
</div>

<div class="mm-table-wrap">
  <div class="mm-table-scroll">
    <table class="mm-table" id="discountsTable">
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
            <?php
              $cId = (int)($c['id'] ?? 0);
              $isActive = !empty($c['isActive']);
              $code = $c['code'] ?? '';
            ?>
            <tr id="discount-row-<?= $cId ?>" class="<?= $isActive ? '' : 'is-row-inactive' ?>">
              <td>
                <strong><?= htmlspecialchars($code) ?></strong>
              </td>
              <td><?= ucfirst(htmlspecialchars($c['discountType'] ?? '')) ?></td>
              <td>
                <?php if (($c['discountType'] ?? '') === 'percentage'): ?>
                  <?= htmlspecialchars($c['discountValue'] ?? 0) ?>% (Max &#8377;<?= $c['maximumDiscount'] ?: 'No limit' ?>)
                <?php else: ?>
                  &#8377;<?= htmlspecialchars($c['discountValue'] ?? 0) ?>
                <?php endif; ?>
              </td>
              <td><span class="num">&#8377;<?= number_format((float) ($c['minimumOrderValue'] ?? 0), 2) ?></span></td>
              <td><span class="num"><?= !empty($c['expiryDate']) ? date('d M Y', strtotime($c['expiryDate'])) : 'N/A' ?></span></td>
              <td>
                <button type="button" 
                        class="status-toggle-btn <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                        data-type="discount" 
                        data-id="<?= $cId ?>"
                        title="<?= $isActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate' ?>">
                  <span class="status-dot"></span>
                  <span class="status-text"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                </button>
              </td>
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-discount') ?>&amp;id=<?= $cId ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" 
                        class="btn-mm <?= $isActive ? 'btn-mm-danger' : 'btn-mm-teal' ?> btn-mm-sm action-soft-toggle"
                        data-type="discount"
                        data-id="<?= $cId ?>" 
                        data-action="<?= $isActive ? 'deactivate' : 'activate' ?>"
                        data-name="<?= htmlspecialchars($code) ?>">
                  <i class="fa-solid <?= $isActive ? 'fa-ban' : 'fa-check' ?>" aria-hidden="true"></i>
                  <span><?= $isActive ? 'Deactivate' : 'Activate' ?></span>
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

<p class="mt-3 mb-0" style="font-size:.78rem;color:var(--ink-40)">
  <i class="fa-solid fa-circle-info me-1"></i> Deactivating a coupon prevents customers from applying it at checkout while preserving usage tracking and order audit records.
</p>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function handleStatusToggle(type, id, action, callback) {
    var url = '<?= url("admin-toggle-status") ?>&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id) + '&action=' + encodeURIComponent(action) + '&ajax=1';
    
    fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success && typeof callback === 'function') {
        callback(data);
      } else if (!data.success) {
        alert(data.message || 'Operation failed.');
      }
    })
    .catch(function (err) {
      console.error(err);
      window.location.reload();
    });
  }

  // Handle direct click on Status pill button
  document.querySelectorAll('#discountsTable .status-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var row = document.getElementById('discount-row-' + id);
      var actionBtn = row ? row.querySelector('.action-soft-toggle') : null;

      handleStatusToggle(type, id, 'toggle', function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.classList.toggle('is-active', isNowActive);
        btn.classList.toggle('is-inactive', !isNowActive);
        btn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
        btn.setAttribute('title', isNowActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate');
        
        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (actionBtn) {
          actionBtn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
          actionBtn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
          actionBtn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-ban' : 'fa-check');
          actionBtn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';
        }
      });
    });
  });

  // Handle Deactivate / Activate action button with confirmation
  document.querySelectorAll('#discountsTable .action-soft-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var action = this.getAttribute('data-action');
      var name = this.getAttribute('data-name');
      var row = document.getElementById('discount-row-' + id);
      var statusBtn = row ? row.querySelector('.status-toggle-btn') : null;

      var promptMsg = (action === 'deactivate') 
        ? "Deactivate coupon code '" + name + "'? It will be disabled at checkout."
        : "Reactivate coupon code '" + name + "'? It will become valid for customers.";

      if (!confirm(promptMsg)) return;

      handleStatusToggle(type, id, action, function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
        btn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
        btn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-ban' : 'fa-check');
        btn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';

        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (statusBtn) {
          statusBtn.classList.toggle('is-active', isNowActive);
          statusBtn.classList.toggle('is-inactive', !isNowActive);
          statusBtn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
          statusBtn.setAttribute('title', isNowActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate');
        }
      });
    });
  });
});
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
