<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Wishlist - MediMax.com']);

$items = $wishlistItems ?? [];
$count = count($items);
$saved = 0.0;
foreach ($items as $it) { $saved += (float) ($it['price'] ?? 0); }
?>

<div class="page">
  <div class="mm-container">

    <!-- Flash message alerts -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: var(--r-md); background: #E4F3EC; border-color: rgba(31,122,85,.3); color: #1F7A55;">
        <i class="fas fa-circle-check" aria-hidden="true"></i>
        <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      </div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: var(--r-md); background: #FBEAE8; border-color: rgba(180,52,43,.3); color: #B4342B;">
        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
        <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      </div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="page-head">
      <p class="eyebrow">Saved for later</p>
      <h1 class="title-page">Your wishlist</h1>
      <p class="lede mb-0" id="wishlistLede">
        <?php if ($count): ?>
          <span class="num" id="wishlistCountSummary"><?= $count ?></span> <span id="wishlistCountPlural"><?= $count === 1 ? 'product' : 'products' ?></span> put aside,
          worth <span class="num" id="wishlistTotalSaved">&#8377;<?= number_format($saved, 2) ?></span> in total.
          Move anything to your cart when you are ready.
        <?php else: ?>
          Tap the heart on any product to keep it here without buying it yet.
        <?php endif; ?>
      </p>
    </div>

    <!-- Active Wishlist Items Panel -->
    <div class="panel" id="wishlistPanel" style="<?= empty($items) ? 'display:none;' : '' ?>">
      <div class="panel__head">
        <div>
          <p class="eyebrow mb-1">Shortlist</p>
          <h2>Products you saved</h2>
        </div>
        <span class="num" id="wishlistPanelCount" style="color:rgba(255,255,255,.85);font-size:.8rem"><?= $count ?> saved</span>
      </div>

      <div class="panel__body" id="wishlistBody">
        <?php foreach ($items as $item): ?>
          <?php
            $iName  = $item['name'] ?? 'Product';
            $iPrice = (float) ($item['price'] ?? 0);
            $iId    = $item['id'] ?? '';
            $iPid   = $item['product_id'] ?? ($item['id'] ?? '');
            $iImg   = $item['image'] ?? 'placeholder.png';
            $inStock = ($item['stock_quantity'] ?? 1) > 0;
          ?>
          <article class="line-item" id="wishlistItem-<?= $iId ?>" data-wishlist-id="<?= $iId ?>" data-product-id="<?= $iPid ?>" data-price="<?= $iPrice ?>" data-name="<?= htmlspecialchars($iName) ?>">
            <div class="line-item__media">
              <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>" loading="lazy"
                   data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
            </div>

            <div>
              <h3 class="line-item__name"><?= htmlspecialchars($iName) ?></h3>
              <p class="line-item__meta mb-0">
                <span>Price <span class="num">&#8377;<?= number_format($iPrice, 2) ?></span></span>
                <span>Code <span class="num">MM<?= str_pad((string) $iPid, 3, '0', STR_PAD_LEFT) ?></span></span>
                <?php if ($inStock): ?>
                  <span class="pill pill--ok"><i class="fas fa-circle-check" aria-hidden="true"></i>In stock</span>
                <?php else: ?>
                  <span class="pill pill--warn"><i class="fas fa-circle-exclamation" aria-hidden="true"></i>Out of stock</span>
                <?php endif; ?>
              </p>
            </div>

            <div class="line-item__side line-item__side--row">
              <form action="<?= url('cart/add') ?>" method="POST" class="m-0">
                <input type="hidden" name="product_id" value="<?= $iPid ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="from_wishlist" value="1">
                <button type="submit" class="icon-btn icon-btn--cart"
                        aria-label="Move <?= htmlspecialchars($iName) ?> to cart"
                        title="Move to cart">
                  <i class="fas fa-cart-plus" aria-hidden="true"></i>
                </button>
              </form>
              <a class="icon-btn icon-btn--danger" href="<?= url('wishlist/remove') . '&id=' . $iId . '&product_id=' . $iPid ?>"
                 data-act="remove-wishlist"
                 data-id="<?= $iId ?>"
                 data-product-id="<?= $iPid ?>"
                 data-confirm="Remove <?= htmlspecialchars($iName) ?> from your wishlist?"
                 aria-label="Remove <?= htmlspecialchars($iName) ?>" title="Remove from wishlist">
                <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="panel__foot d-flex flex-wrap gap-2 justify-content-between">
        <a class="btn-mm btn-mm-ghost btn-mm-sm" href="<?= url('products') ?>">
          <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Keep shopping</span>
        </a>
        <form action="<?= url('wishlist/clear') ?>" method="POST" class="m-0">
          <button type="submit" class="btn-mm btn-mm-danger btn-mm-sm"
                  data-confirm="Clear the whole wishlist? Every saved product is removed.">
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Clear wishlist</span>
          </button>
        </form>
      </div>
    </div>

    <!-- Empty State (shown when wishlist has no products) -->
    <div id="wishlistEmptyState" style="<?= !empty($items) ? 'display:none;' : '' ?>">
      <?php Controller::component('empty_state', [
          'title'      => 'Nothing saved yet',
          'message'    => 'Your wishlist keeps products aside so you can decide later. Nothing is reserved or charged.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-heart',
      ]); ?>
    </div>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
