<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Cart - MediMax.com']);

$items = $cartItems ?? [];
$count = count($items);
$units = 0;
foreach ($items as $it) { $units += (int) ($it['quantity'] ?? 1); }
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">Basket</p>
      <h1 class="title-page">Your cart</h1>
      <p class="lede mb-0">
        <?php if ($count): ?>
          <span class="num"><?= $count ?></span> <?= $count === 1 ? 'line' : 'lines' ?>,
          <span class="num"><?= $units ?></span> <?= $units === 1 ? 'unit' : 'units' ?> &mdash; review the quantities before you check out.
        <?php else: ?>
          Nothing here yet. Add something from the shelf and it will show up on this page.
        <?php endif; ?>
      </p>
    </div>

    <?php if (!empty($items)): ?>

      <div class="row g-4">
        <div class="col-lg-8">

          <div class="panel">
            <div class="panel__head">
              <div>
                <p class="eyebrow mb-1">Dispensing list</p>
                <h2>Items in your cart</h2>
              </div>
              <span class="num" style="color:rgba(255,255,255,.85);font-size:.8rem"><?= $units ?> units</span>
            </div>

            <div class="panel__body">
              <?php foreach ($items as $item): ?>
                <?php
                  $iName  = $item['name'] ?? 'Product';
                  $iPrice = (float) ($item['price'] ?? 0);
                  $iQty   = (int) ($item['quantity'] ?? 1);
                  $iId    = $item['id'] ?? '';
                  $iImg   = $item['image'] ?? 'placeholder.png';
                  $iSub   = $iPrice * $iQty;
                ?>
                <article class="line-item">
                  <div class="line-item__media">
                    <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>" loading="lazy"
                         data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
                  </div>

                  <div>
                    <h3 class="line-item__name"><?= htmlspecialchars($iName) ?></h3>
                    <p class="line-item__meta">
                      <span>Unit price <span class="num">&#8377;<?= number_format($iPrice, 2) ?></span></span>
                      <span>Line <span class="num">MM<?= str_pad((string) $iId, 3, '0', STR_PAD_LEFT) ?></span></span>
                    </p>

                    <form action="<?= url('cart/update') ?>" method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                      <input type="hidden" name="cart_id" value="<?= $iId ?>">
                      <div class="stepper">
                        <button type="button" data-step="-1" aria-label="Decrease quantity"><i class="fas fa-minus" aria-hidden="true"></i></button>
                        <input type="number" name="quantity" value="<?= $iQty ?>" min="1"
                               aria-label="Quantity of <?= htmlspecialchars($iName) ?>">
                        <button type="button" data-step="1" aria-label="Increase quantity"><i class="fas fa-plus" aria-hidden="true"></i></button>
                      </div>
                      <button type="submit" class="btn-mm btn-mm-light btn-mm-sm">
                        <i class="fas fa-rotate" aria-hidden="true"></i><span>Update</span>
                      </button>
                    </form>
                  </div>

                  <div class="line-item__side">
                    <span class="line-item__total num">&#8377;<?= number_format($iSub, 2) ?></span>
                    <a class="icon-btn" href="<?= url('cart/remove&id=' . $iId) ?>"
                       data-confirm="Remove <?= htmlspecialchars($iName) ?> from your cart?"
                       aria-label="Remove <?= htmlspecialchars($iName) ?>" title="Remove from cart">
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
              <form action="<?= url('cart/clear') ?>" method="POST" class="m-0">
                <button type="submit" class="btn-mm btn-mm-danger btn-mm-sm"
                        data-confirm="Empty the whole cart? This clears every line.">
                  <i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Empty cart</span>
                </button>
              </form>
            </div>
          </div>

        </div>

        <div class="col-lg-4">
          <div class="receipt-col">
            <div class="mm-card p-3 mb-0" style="border-radius:var(--r-lg) var(--r-lg) 0 0;border-bottom:0">
              <p class="eyebrow mb-0">Order summary</p>
            </div>

            <div class="receipt">
              <div class="d-flex align-items-baseline justify-content-between">
                <h2 class="h5 mb-0">Payable now</h2>
                <span class="num" style="font-size:.72rem;color:var(--ink-40)"><?= date('d M Y') ?></span>
              </div>

              <div class="receipt__rows">
                <div class="receipt__row">
                  <span>Items</span>
                  <span class="num"><?= $count ?></span>
                </div>
                <div class="receipt__row">
                  <span>Units</span>
                  <span class="num"><?= $units ?></span>
                </div>
                <div class="receipt__row">
                  <span>Subtotal</span>
                  <span class="num">&#8377;<?= number_format((float) ($grandTotal ?? 0), 2) ?></span>
                </div>
                <div class="receipt__row">
                  <span>Delivery</span>
                  <span>Confirmed at checkout</span>
                </div>
                <div class="receipt__row receipt__row--total">
                  <span>Total</span>
                  <span class="num">&#8377;<?= number_format((float) ($grandTotal ?? 0), 2) ?></span>
                </div>
              </div>

              <a class="btn-mm btn-mm-amber btn-mm-block btn-mm-lg" href="<?= url('checkout') ?>">
                <i class="fas fa-lock" aria-hidden="true"></i><span>Checkout securely</span>
              </a>

              <hr class="label-rule">

              <ul class="list-unstyled m-0" style="font-size:.8rem;color:var(--ink-60);display:grid;gap:.5rem">
                <li><i class="fas fa-truck-medical me-2" style="color:var(--teal-700)" aria-hidden="true"></i>Free delivery over &#8377;499</li>
                <li><i class="fas fa-rotate-left me-2" style="color:var(--teal-700)" aria-hidden="true"></i>7-day returns on unopened packs</li>
                <li><i class="fas fa-shield-heart me-2" style="color:var(--teal-700)" aria-hidden="true"></i>Genuine stock, batch-tracked</li>
              </ul>

              <p class="receipt__note">
                Prescription items are verified by a registered pharmacist before dispatch.
                You will be asked to upload a valid prescription where one is required.
              </p>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>

      <?php Controller::component('empty_state', [
          'title'      => 'Your cart is empty',
          'message'    => 'Add a product and it lands here with its quantity and running total.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-cart-shopping',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
