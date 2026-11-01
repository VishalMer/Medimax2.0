<?php 
ob_start(); 
$catList = $categories ?? [];
?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Catalogue &middot; <span class="num">MM<?= str_pad((string) ($product['id'] ?? 0), 3, '0', STR_PAD_LEFT) ?></span></p>
    <h2 class="title-page">Edit product</h2>
    <p>Changes go live on the shelf immediately. Leave the image empty to keep the current one.</p>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <?= htmlspecialchars($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <form action="<?= url('admin-edit-product-submit') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars((string)($product['id'] ?? '')) ?>">

    <div class="mm-field">
      <label for="name">Product name</label>
      <input class="mm-input" type="text" id="name" name="name"
             value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
    </div>

    <div class="row">
      <div class="col-md-6 mm-field">
        <label for="price">Price (&#8377;)</label>
        <input class="mm-input num" type="number" id="price" name="price" step="0.01" min="0"
               value="<?= htmlspecialchars((string) ($product['price'] ?? '')) ?>" required>
      </div>

      <div class="col-md-6 mm-field">
        <label for="stock_quantity">Stock quantity</label>
        <input class="mm-input num" type="number" id="stock_quantity" name="stock_quantity" min="0"
               value="<?= htmlspecialchars((string) ($product['stock_quantity'] ?? 50)) ?>" required>
      </div>
    </div>

    <div class="mm-field">
      <label for="category_id">Category / Shelf</label>
      <select class="mm-input" id="category_id" name="category_id" required>
        <?php if (!empty($catList)): ?>
          <?php foreach ($catList as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)($product['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
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
      <label for="image">Replace image (optional)</label>
      <input class="mm-input" type="file" id="image" name="image" accept="image/*">
    </div>

    <?php if (!empty($product['image'])): ?>
      <div class="d-flex align-items-center gap-3 p-3 mb-3"
           style="background:var(--mist);border-radius:var(--r-md);border:1px solid var(--line)">
        <img src="<?= asset('images/' . $product['image']) ?>" alt="Current product image"
             style="height:76px;width:76px;object-fit:contain;background:#fff;border-radius:var(--r-sm);border:1px solid var(--line)"
             data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        <div>
          <p class="mm-label mb-1">Currently on the shelf</p>
          <p class="mb-0 num" style="font-size:.78rem;color:var(--ink-60)"><?= htmlspecialchars($product['image']) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="form-actions">
      <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
        <i class="fas fa-floppy-disk" aria-hidden="true"></i><span>Save changes</span>
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
