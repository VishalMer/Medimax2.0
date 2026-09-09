<?php
ob_start();

$rows = $products ?? [];
$catalogueValue = 0.0;
foreach ($rows as $p) { $catalogueValue += (float) ($p['price'] ?? 0); }
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">Catalogue</p>
    <h2 class="title-section mt-2 mb-0">Products on the shelf</h2>
    <p><span class="num"><?= count($rows) ?></span> listed, worth <span class="num">&#8377;<?= number_format($catalogueValue, 2) ?></span> at list price.</p>
  </div>
  <a class="btn-mm btn-mm-amber btn-mm-sm" href="<?= url('admin-add-product') ?>">
    <i class="fas fa-plus" aria-hidden="true"></i><span>Add product</span>
  </a>
</div>

<div class="mm-table-wrap">
  <div class="mm-table-scroll">
    <table class="mm-table">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Product</th>
          <th scope="col">Category</th>
          <th scope="col">Price</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $product): ?>
            <?php
              $pId    = $product['id'] ?? '';
              $pName  = $product['name'] ?? '';
              $pImage = $product['image'] ?? '';
            ?>
            <tr>
              <td><span class="mm-table__id">MM<?= str_pad((string) $pId, 3, '0', STR_PAD_LEFT) ?></span></td>
              <td>
                <div class="cell-product">
                  <?php if ($pImage !== ''): ?>
                    <img class="mm-table__thumb" src="<?= asset('images/' . $pImage) ?>"
                         alt="" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
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
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-product') ?>&amp;id=<?= $pId ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" class="btn-mm btn-mm-danger btn-mm-sm"
                        data-delete-product="<?= $pId ?>"
                        data-product-name="<?= htmlspecialchars($pName) ?>">
                  <i class="fa-solid fa-trash" aria-hidden="true"></i><span>Delete</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="mm-table__empty">
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
  Deleting is not wired to a route in this build &mdash; the button confirms, then reports what would be removed.
</p>

<script>
// Prototype stub: no delete route exists yet, so say so instead of pretending.
document.querySelectorAll('[data-delete-product]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var id = btn.getAttribute('data-delete-product');
    var name = btn.getAttribute('data-product-name') || 'this product';
    if (!window.confirm('Delete "' + name + '" from the catalogue? This cannot be undone.')) return;
    var code = 'MM' + ('00' + id).slice(-3);
    if (window.MediMax) window.MediMax.toast('Delete requested for ' + code, 'fa-trash');
  });
});
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
