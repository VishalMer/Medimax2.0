<?php
/**
 * Product card — carries the "dispensing label" strip: category + SKU up top,
 * the goods in the middle, price and actions below a perforated rule.
 * Renders bare (no grid column) so the parent decides the layout.
 */
$name     = $product['name'] ?? 'Unknown product';
$price    = $product['price'] ?? 0;
$original = $product['original_price'] ?? null;
$image    = $product['image'] ?? 'placeholder.png';
$category = $product['category'] ?? 'Pharmacy';
$id       = $product['id'] ?? 0;
$index    = $index ?? 0;
$stock    = (int)($product['stock_quantity'] ?? 1);
$inStock  = $stock > 0;
$isFav    = class_exists('WishlistController') && WishlistController::has($id);
?>
<article class="product-card position-relative"
         data-category="<?= htmlspecialchars($category) ?>"
         data-price="<?= htmlspecialchars((string) $price) ?>"
         data-name="<?= htmlspecialchars($name) ?>"
         data-id="<?= (int) $id ?>"
         data-stock="<?= $stock ?>"
         data-index="<?= (int) $index ?>">

  <div class="product-card__label">
    <span class="product-card__cat"><?= htmlspecialchars($category) ?></span>
    <span class="product-card__sku">MM<?= str_pad((string) $id, 3, '0', STR_PAD_LEFT) ?></span>
  </div>

  <div class="product-card__media">
    <?php if ($original && $original > $price): 
        $discountPct = round((($original - $price) / $original) * 100);
    ?>
    <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;"><?= $discountPct ?>% OFF</span>
    <?php endif; ?>
    <?php if (!$inStock): ?>
    <span class="badge position-absolute top-0 end-0 m-2 bg-secondary" style="z-index: 10; font-size: 0.75rem;"><i class="fas fa-circle-xmark me-1"></i>Out of stock</span>
    <?php endif; ?>
    <img src="<?= asset('images/' . $image) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy"
         data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>" style="<?= !$inStock ? 'filter: grayscale(80%); opacity: 0.85;' : '' ?>">
  </div>

  <div class="product-card__body">
    <h3 class="product-card__name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></h3>
  </div>

  <div class="product-card__foot flex-wrap">
    <div class="product-card__price" style="min-width: 0;">
      <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
      <div class="d-flex flex-column">
        <div class="d-flex align-items-baseline gap-1 flex-wrap">
          <?php if ($original && $original > $price): ?>
            <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;<?= number_format((float) $original) ?></span>
          <?php endif; ?>
          <span class="num">&#8377;<?= number_format((float) $price) ?></span>
        </div>
        <?php if ($original && $original > $price): ?>
          <span style="color: var(--primary-teal, #70ABAF); font-size: 0.75rem; font-weight: bold;">Save &#8377;<?= number_format((float) ($original - $price)) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="product-card__acts flex-shrink-0">
      <?php if ($inStock): ?>
        <button class="icon-btn" type="button" data-act="cart" data-id="<?= (int)$id ?>" data-name="<?= htmlspecialchars($name) ?>" data-stock="<?= $stock ?>"
                aria-label="Add <?= htmlspecialchars($name) ?> to cart" title="Add to cart">
          <i class="fas fa-cart-plus" aria-hidden="true"></i>
        </button>
      <?php else: ?>
        <button class="icon-btn" type="button" disabled aria-disabled="true" title="Out of stock"
                style="opacity: 0.55; cursor: not-allowed; background: var(--mist);">
          <i class="fas fa-ban" aria-hidden="true"></i>
        </button>
      <?php endif; ?>
      <button class="icon-btn <?= $isFav ? 'is-fav' : '' ?>" type="button" 
              data-act="fav" 
              data-id="<?= (int)$id ?>" 
              data-name="<?= htmlspecialchars($name) ?>"
              aria-pressed="<?= $isFav ? 'true' : 'false' ?>" 
              aria-label="<?= $isFav ? 'Remove ' . htmlspecialchars($name) . ' from wishlist' : 'Save ' . htmlspecialchars($name) . ' to wishlist' ?>" 
              title="<?= $isFav ? 'Remove from wishlist' : 'Save to wishlist' ?>">
        <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</article>

