<?php
ob_start();

$rows = $products ?? [];
$catalogueValue = 0.0;
$activeCount = 0;
foreach ($rows as $p) { 
    $catalogueValue += (float) ($p['price'] ?? 0);
    $isActive = isset($p['is_active']) ? ((int)$p['is_active'] === 1) : true;
    if ($isActive) { $activeCount++; }
}
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">Catalogue</p>
    <h2 class="title-section mt-2 mb-0">Products on the shelf</h2>
    <p>
      <span class="num"><?= count($rows) ?></span> listed (<span class="num"><?= $activeCount ?></span> active on storefront), worth <span class="num">&#8377;<?= number_format($catalogueValue, 2) ?></span> at list price.
    </p>
  </div>
  <a class="btn-mm btn-mm-amber btn-mm-sm" href="<?= url('admin-add-product') ?>">
    <i class="fas fa-plus" aria-hidden="true"></i><span>Add product</span>
  </a>
</div>

<div class="mm-table-wrap">
  <div class="mm-table-scroll">
    <table class="mm-table" id="productsTable">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Product</th>
          <th scope="col">Category</th>
          <th scope="col">Price</th>
          <th scope="col">Status</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $product): ?>
            <?php
              $pId      = (int)($product['id'] ?? 0);
              $pName    = $product['name'] ?? '';
              $pImage   = $product['image'] ?? '';
              $isActive = isset($product['is_active']) ? ((int)$product['is_active'] === 1) : true;
            ?>
            <tr id="product-row-<?= $pId ?>" class="<?= $isActive ? '' : 'is-row-inactive' ?>">
              <td><span class="mm-table__id">MM<?= str_pad((string) $pId, 3, '0', STR_PAD_LEFT) ?></span></td>
              <td>
                <div class="cell-product">
                  <?php if ($pImage !== ''): ?>
                    <img class="mm-table__thumb" src="<?= asset('images/' . $pImage) ?>"
                         alt="" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
                  <?php else: ?>
                    <span class="mm-table__thumb d-grid" style="place-items:center;color:var(--ink-40)">
                      <i class="fa-regular fa-image" aria-hidden="true"></i>
                    </span>
                  <?php endif; ?>
                  <div>
                    <strong><?= htmlspecialchars($pName) ?></strong>
                    <span><?= htmlspecialchars($product['category'] ?? 'Pharmacy') ?></span>
                  </div>
                </div>
              </td>
              <td><span class="pill pill--neutral pill--plain"><?= htmlspecialchars($product['category'] ?? 'Pharmacy') ?></span></td>
              <td><span class="num">&#8377;<?= number_format((float) ($product['price'] ?? 0), 2) ?></span></td>
              <td>
                <button type="button" 
                        class="status-toggle-btn <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                        data-type="product" 
                        data-id="<?= $pId ?>"
                        title="<?= $isActive ? 'Active on store: Click to Deactivate' : 'Inactive (Hidden from store): Click to Activate' ?>">
                  <span class="status-dot"></span>
                  <span class="status-text"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                </button>
              </td>
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-product') ?>&amp;id=<?= $pId ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" 
                        class="btn-mm <?= $isActive ? 'btn-mm-danger' : 'btn-mm-teal' ?> btn-mm-sm action-soft-toggle"
                        data-type="product"
                        data-id="<?= $pId ?>" 
                        data-action="<?= $isActive ? 'deactivate' : 'activate' ?>"
                        data-name="<?= htmlspecialchars($pName) ?>">
                  <i class="fa-solid <?= $isActive ? 'fa-eye-slash' : 'fa-eye' ?>" aria-hidden="true"></i>
                  <span><?= $isActive ? 'Deactivate' : 'Activate' ?></span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="mm-table__empty">
              <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
              No products yet. <a href="<?= url('admin-add-product') ?>">Add the first one</a>.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="mt-3 mb-0" style="font-size:.78rem;color:var(--ink-40)">
  <i class="fa-solid fa-circle-info me-1"></i> Inactive products are hidden from the customer storefront and search, but are preserved safely in the database with their sales and order history intact.
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
  document.querySelectorAll('#productsTable .status-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var row = document.getElementById('product-row-' + id);
      var actionBtn = row ? row.querySelector('.action-soft-toggle') : null;

      handleStatusToggle(type, id, 'toggle', function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.classList.toggle('is-active', isNowActive);
        btn.classList.toggle('is-inactive', !isNowActive);
        btn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
        btn.setAttribute('title', isNowActive ? 'Active on store: Click to Deactivate' : 'Inactive (Hidden): Click to Activate');
        
        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (actionBtn) {
          actionBtn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
          actionBtn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
          actionBtn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-eye-slash' : 'fa-eye');
          actionBtn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';
        }
      });
    });
  });

  // Handle Deactivate / Activate action button with confirmation
  document.querySelectorAll('#productsTable .action-soft-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var action = this.getAttribute('data-action');
      var name = this.getAttribute('data-name');
      var row = document.getElementById('product-row-' + id);
      var statusBtn = row ? row.querySelector('.status-toggle-btn') : null;

      var promptMsg = (action === 'deactivate') 
        ? "Deactivate product '" + name + "'? It will be delisted from the customer catalogue but preserved in your inventory records."
        : "Reactivate product '" + name + "'? It will go live on the customer shelf immediately.";

      if (!confirm(promptMsg)) return;

      handleStatusToggle(type, id, action, function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
        btn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
        btn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-eye-slash' : 'fa-eye');
        btn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';

        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (statusBtn) {
          statusBtn.classList.toggle('is-active', isNowActive);
          statusBtn.classList.toggle('is-inactive', !isNowActive);
          statusBtn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
          statusBtn.setAttribute('title', isNowActive ? 'Active on store: Click to Deactivate' : 'Inactive (Hidden): Click to Activate');
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
