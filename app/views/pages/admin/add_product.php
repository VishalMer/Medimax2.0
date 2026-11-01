<?php 
ob_start(); 
$catList = $categories ?? [];
?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Catalogue</p>
    <h2 class="title-page">Add a product</h2>
    <p>It appears on the shelf as soon as it is saved. Prices are in rupees, inclusive of tax.</p>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <?= htmlspecialchars($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <form action="<?= url('admin-add-product-submit') ?>" method="POST" enctype="multipart/form-data">
    <div class="mm-field">
      <label for="name">Product name</label>
      <input class="mm-input" type="text" id="name" name="name" required placeholder="Vitamin C 500mg, 60 tablets">
      <p class="mb-0 mt-2" style="font-size:.76rem;color:var(--ink-40)">
        Write it the way a customer would search for it: brand, strength, pack size.
      </p>
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="price">Price (&#8377;)</label>
        <input class="mm-input num" type="number" id="price" name="price" step="0.01" min="0" required placeholder="450.00">
      </div>

      <div class="col-md-6 mm-field">
        <label for="stock_quantity">Stock quantity</label>
        <input class="mm-input num" type="number" id="stock_quantity" name="stock_quantity" min="0" value="50" required placeholder="50">
      </div>
    </div>

    <div class="mm-field">
      <label for="category_id">Category / Shelf</label>
      <select class="mm-input" id="category_id" name="category_id" required>
        <?php if (!empty($catList)): ?>
          <?php foreach ($catList as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        <?php else: ?>
          <option value="1">Vitamins</option>
          <option value="2">Supplements</option>
          <option value="3">Skincare</option>
          <option value="4">Hair Care</option>
          <option value="5">First Aid</option>
          <option value="6">Baby Care</option>
          <option value="7">Personal Care</option>
          <option value="8">Beverages</option>
          <option value="9">Ayurveda &amp; Herbal</option>
        <?php endif; ?>
      </select>
    </div>

    <div class="mm-field">
      <label for="description">Description (optional)</label>
      <textarea class="mm-input" id="description" name="description" rows="3" placeholder="Key formulation, ingredients, and pack benefits..."></textarea>
    </div>

    <div class="mm-field">
      <label for="image">Product image</label>
      <input class="mm-input" type="file" id="image" name="image" accept="image/*" required>
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
