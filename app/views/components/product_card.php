<?php
/**
 * Product card — carries the "dispensing label" strip: category + SKU up top,
 * the goods in the middle, price and actions below a perforated rule.
 * Renders bare (no grid column) so the parent decides the layout.
 */
$name     = $product['name'] ?? 'Unknown product';
$price    = $product['price'] ?? 0;
$image    = $product['image'] ?? 'placeholder.png';
$category = $product['category'] ?? 'Pharmacy';
$id       = $product['id'] ?? 0;
$index    = $index ?? 0;
?>
<article class="product-card"
         data-category="<?= htmlspecialchars($category) ?>"
         data-price="<?= htmlspecialchars((string) $price) ?>"
         data-name="<?= htmlspecialchars($name) ?>"
         data-index="<?= (int) $index ?>">

  <div class="product-card__label">
    <span class="product-card__cat"><?= htmlspecialchars($category) ?></span>
    <span class="product-card__sku">MM<?= str_pad((string) $id, 3, '0', STR_PAD_LEFT) ?></span>
  </div>

  <div class="product-card__media">
    <img src="<?= asset('images/' . $image) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy"
         data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
  </div>

  <div class="product-card__body">
    <h3 class="product-card__name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></h3>
  </div>

  <div class="product-card__foot">
    <div class="product-card__price">
      <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
      <span class="num">&#8377;<?= number_format((float) $price) ?></span>
    </div>
    <div class="product-card__acts">
      <button class="icon-btn" type="button" data-act="cart" data-name="<?= htmlspecialchars($name) ?>"
              aria-label="Add <?= htmlspecialchars($name) ?> to cart" title="Add to cart">
        <i class="fas fa-cart-plus" aria-hidden="true"></i>
      </button>
      <button class="icon-btn" type="button" data-act="fav" data-name="<?= htmlspecialchars($name) ?>"
              aria-pressed="false" aria-label="Save <?= htmlspecialchars($name) ?> to wishlist" title="Save to wishlist">
        <i class="far fa-heart" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</article>
