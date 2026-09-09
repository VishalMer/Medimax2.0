<?php ob_start(); ?>

<div class="form-card">
  <div class="form-card__head">
    <p class="eyebrow">Catalogue &middot; <span class="num">MM<?= str_pad((string) ($product['id'] ?? 0), 3, '0', STR_PAD_LEFT) ?></span></p>
    <h2 class="title-page">Edit product</h2>
    <p>Changes go live on the shelf immediately. Leave the image empty to keep the current one.</p>
  </div>

  <form action="<?= url('admin-edit-product-submit') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">

    <div class="mm-field">
      <label for="name">Product name</label>
      <input class="mm-input" type="text" id="name" name="name"
             value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
    </div>

    <div class="mm-field">
      <label for="price">Price (&#8377;)</label>
      <input class="mm-input num" type="number" id="price" name="price" step="0.01" min="0"
             value="<?= htmlspecialchars((string) ($product['price'] ?? '')) ?>" required>
    </div>

    <div class="mm-field">
      <label for="image">Replace image</label>
      <input class="mm-input" type="file" id="image" name="image" accept="image/*">
    </div>

    <?php if (!empty($product['image'])): ?>
      <div class="d-flex align-items-center gap-3 p-3 mb-3"
           style="background:var(--mist);border-radius:var(--r-md);border:1px solid var(--line)">
        <img src="<?= asset('images/' . $product['image']) ?>" alt="Current product image"
             style="height:76px;width:76px;object-fit:contain;background:#fff;border-radius:var(--r-sm);border:1px solid var(--line)"
             data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
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
