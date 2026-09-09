<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Wishlist - MediMax.com']);

$items = $wishlistItems ?? [];
$count = count($items);
$saved = 0.0;
foreach ($items as $it) { $saved += (float) ($it['price'] ?? 0); }
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">Saved for later</p>
      <h1 class="title-page">Your wishlist</h1>
      <p class="lede mb-0">
        <?php if ($count): ?>
          <span class="num"><?= $count ?></span> <?= $count === 1 ? 'product' : 'products' ?> put aside,
          worth <span class="num">&#8377;<?= number_format($saved, 2) ?></span> in total.
          Move anything to your cart when you are ready.
        <?php else: ?>
          Tap the heart on any product to keep it here without buying it yet.
        <?php endif; ?>
      </p>
    </div>

    <?php if (!empty($items)): ?>

      <div class="panel">
        <div class="panel__head">
          <div>
            <p class="eyebrow mb-1">Shortlist</p>
            <h2>Products you saved</h2>
          </div>
          <span class="num" style="color:rgba(255,255,255,.85);font-size:.8rem"><?= $count ?> saved</span>
        </div>

        <div class="panel__body">
          <?php foreach ($items as $item): ?>
            <?php
              $iName  = $item['name'] ?? 'Product';
              $iPrice = (float) ($item['price'] ?? 0);
              $iId    = $item['id'] ?? '';
              $iPid   = $item['product_id'] ?? '';
              $iImg   = $item['image'] ?? 'placeholder.png';
            ?>
            <article class="line-item">
              <div class="line-item__media">
                <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>" loading="lazy"
                     data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
              </div>

              <div>
                <h3 class="line-item__name"><?= htmlspecialchars($iName) ?></h3>
                <p class="line-item__meta mb-0">
                  <span>Price <span class="num">&#8377;<?= number_format($iPrice, 2) ?></span></span>
                  <span>Code <span class="num">MM<?= str_pad((string) $iPid, 3, '0', STR_PAD_LEFT) ?></span></span>
                  <span class="pill pill--ok"><i class="fas fa-circle-check" aria-hidden="true"></i>In stock</span>
                </p>
              </div>

              <div class="line-item__side">
                <form action="<?= url('cart/add') ?>" method="POST" class="m-0">
                  <input type="hidden" name="product_id" value="<?= $iPid ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="btn-mm btn-mm-teal btn-mm-sm">
                    <i class="fas fa-cart-plus" aria-hidden="true"></i><span>Move to cart</span>
                  </button>
                </form>
                <a class="icon-btn" href="<?= url('wishlist/remove&id=' . $iId) ?>"
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

    <?php else: ?>

      <?php Controller::component('empty_state', [
          'title'      => 'Nothing saved yet',
          'message'    => 'Your wishlist keeps products aside so you can decide later. Nothing is reserved or charged.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-heart',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
