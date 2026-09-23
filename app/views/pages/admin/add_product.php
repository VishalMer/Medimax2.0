<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Catalogue</p>
    <h2 class="title-page">Add a product</h2>
    <p>It appears on the shelf as soon as it is saved. Prices are in rupees, inclusive of tax.</p>
  </div>

  <form action="<?= url('admin-add-product-submit') ?>" method="POST" enctype="multipart/form-data">
    <div class="mm-field">
      <label for="name">Product name</label>
      <input class="mm-input" type="text" id="name" name="name" required data-validation="required min max" data-min="2" data-max="150" placeholder="Vitamin C 500mg, 60 tablets">
      <p class="mb-0 mt-2" style="font-size:.76rem;color:var(--ink-40)">
        Write it the way a customer would search for it: brand, strength, pack size.
      </p>
    </div>

    <div class="mm-field">
      <label for="price">Price (&#8377;)</label>
      <input class="mm-input num" type="number" id="price" name="price" step="0.01" min="0" required data-validation="required" placeholder="450.00">
    </div>

    <div class="mm-field">
      <label for="image">Product image</label>
      <input class="mm-input" type="file" id="image" name="image" accept="image/*" required data-validation="required file filesize" data-filesize="2048" data-filetypes="jpg,jpeg,png,webp">
      <p class="mb-0 mt-2" style="font-size:.76rem;color:var(--ink-40)">
        Square works best. Shoot the front of the pack on a plain background.
      </p>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-plus" aria-hidden="true"></i><span>Add product</span>
      </button>
      <a class="btn-mm btn-mm-ghost btn-mm-block" href="<?= url('admin-products') ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Back to products</span>
      </a>
    </div>
  </form>
</div>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
