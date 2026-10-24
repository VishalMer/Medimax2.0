<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Shopping Cart - MediMax.com']);

$items = $cartItems ?? [];
$count = $count ?? count($items);
$units = $units ?? 0;
$subtotal = (float)($subtotal ?? 0.0);
$deliveryFee = (float)($deliveryFee ?? 0.0);
$discountAmount = (float)($discountAmount ?? 0.0);
$grandTotal = (float)($grandTotal ?? 0.0);
$isFreeDelivery = $isFreeDelivery ?? ($subtotal >= 499.00 && $subtotal > 0);
$freeDeliveryRemaining = (float)($freeDeliveryRemaining ?? 0.0);
$freeDeliveryPercent = (int)($freeDeliveryPercent ?? 100);
$appliedCoupon = $appliedCoupon ?? ($_SESSION['applied_coupon'] ?? null);
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

    <!-- Page Header -->
    <div class="page-head">
      <p class="eyebrow">Basket</p>
      <h1 class="title-page">Your cart</h1>
      <p class="lede mb-0" id="cartLede">
        <?php if ($count): ?>
          <span class="num" id="cartCountSummary"><?= $count ?></span> <span id="cartCountPlural"><?= $count === 1 ? 'line' : 'lines' ?></span>,
          <span class="num" id="cartUnitsSummary"><?= $units ?></span> <span id="cartUnitsPlural"><?= $units === 1 ? 'unit' : 'units' ?></span> &mdash; review your quantities before you check out.
        <?php else: ?>
          Nothing here yet. Add medicines or wellness products from the shelf and they will land here.
        <?php endif; ?>
      </p>
    </div>

    <!-- Active Cart Content Row -->
    <div class="row g-4" id="cartMainRow" style="<?= empty($items) ? 'display:none;' : '' ?>">
      <div class="col-lg-8">

        <!-- Free Delivery Progress Notification -->
        <div id="freeDeliveryBanner" class="alert d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" 
             style="border-radius: var(--r-md); <?= $isFreeDelivery ? 'background: #E4F3EC; border: 1px solid rgba(31,122,85,.3); color: #1F7A55;' : 'background: #E5F0F8; border: 1px solid rgba(44,110,155,.2); color: #2C6E9B;' ?>">
          <div class="d-flex align-items-center gap-2">
            <i class="fas <?= $isFreeDelivery ? 'fa-truck-fast' : 'fa-truck' ?>" style="font-size: 1.15rem;" aria-hidden="true"></i>
            <span id="freeDeliveryText">
              <?php if ($isFreeDelivery): ?>
                <strong>Congratulations!</strong> Your order qualifies for <strong>FREE Delivery</strong> across India.
              <?php else: ?>
                Add <strong>&#8377;<span id="freeDeliveryRemainingText"><?= number_format($freeDeliveryRemaining, 2) ?></span></strong> more to unlock <strong>FREE Delivery</strong>!
              <?php endif; ?>
            </span>
          </div>
          <div class="progress flex-grow-1 ms-lg-3" style="height: 6px; min-width: 140px; max-width: 200px; background: rgba(0,0,0,0.08); border-radius: 999px;">
            <div class="progress-bar" id="freeDeliveryProgressBar" role="progressbar" 
                 style="width: <?= $freeDeliveryPercent ?>%; background: <?= $isFreeDelivery ? '#1F7A55' : 'var(--teal-700, #2C7A80)' ?>; border-radius: 999px;" 
                 aria-valuenow="<?= $freeDeliveryPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>

        <div class="panel">
          <div class="panel__head">
            <div>
              <p class="eyebrow mb-1">Dispensing list</p>
              <h2>Items in your cart</h2>
            </div>
            <span class="num" id="panelUnitsBadge" style="color:rgba(255,255,255,.85);font-size:.8rem"><?= $units ?> units</span>
          </div>

          <div class="panel__body" id="cartItemsList">
            <?php foreach ($items as $item): ?>
              <?php
                $iName  = $item['name'] ?? 'Product';
                $iPrice = (float) ($item['price'] ?? 0);
                $iOrig  = (float) ($item['original_price'] ?? 0);
                $iQty   = (int) ($item['quantity'] ?? 1);
                $iId    = (int) ($item['id'] ?? 0);
                $iPid   = (int) ($item['product_id'] ?? 0);
                $iImg   = $item['image'] ?? 'placeholder.png';
                $iStock = (int) ($item['stock_quantity'] ?? 10);
                $iSub   = $iPrice * $iQty;
                $maxAllowed = min(10, $iStock > 0 ? $iStock : 10);
              ?>
              <article class="line-item" id="cartItem-<?= $iId ?>" 
                       data-cart-id="<?= $iId ?>" 
                       data-product-id="<?= $iPid ?>" 
                       data-unit-price="<?= $iPrice ?>"
                       data-max-stock="<?= $maxAllowed ?>"
                       data-name="<?= htmlspecialchars($iName) ?>">
                <div class="line-item__media">
                  <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>" loading="lazy"
                       data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
                </div>

                <div>
                  <h3 class="line-item__name">
                    <a href="<?= url('products') ?>&amp;q=<?= urlencode($iName) ?>" style="color: inherit; text-decoration: none;">
                      <?= htmlspecialchars($iName) ?>
                    </a>
                  </h3>
                  <p class="line-item__meta mb-2">
                    <span>Unit price 
                      <?php if ($iOrig > $iPrice): ?>
                        <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;<?= number_format($iOrig, 2) ?></span>
                      <?php endif; ?>
                      <span class="num fw-bold">&#8377;<?= number_format($iPrice, 2) ?></span>
                    </span>
                    <span>Code <span class="num">MM<?= str_pad((string) $iPid, 3, '0', STR_PAD_LEFT) ?></span></span>
                    <?php if ($iStock <= 5 && $iStock > 0): ?>
                      <span class="pill pill--warn"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i>Only <?= $iStock ?> left</span>
                    <?php else: ?>
                      <span class="pill pill--ok"><i class="fas fa-circle-check" aria-hidden="true"></i>In stock</span>
                    <?php endif; ?>
                  </p>

                  <!-- Stepper & Quantity Form with Live AJAX -->
                  <form action="<?= url('cart/update') ?>" method="POST" class="cart-update-form d-flex align-items-center gap-2 flex-wrap m-0">
                    <input type="hidden" name="cart_id" value="<?= $iId ?>">
                    <input type="hidden" name="product_id" value="<?= $iPid ?>">
                    <div class="stepper" style="border-radius: var(--r-sm);">
                      <button type="button" data-step="-1" aria-label="Decrease quantity of <?= htmlspecialchars($iName) ?>">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                      </button>
                      <input type="number" name="quantity" class="cart-qty-input" value="<?= $iQty ?>" min="1" max="<?= $maxAllowed ?>" 
                             aria-label="Quantity of <?= htmlspecialchars($iName) ?>"
                             data-cart-id="<?= $iId ?>">
                      <button type="button" data-step="1" aria-label="Increase quantity of <?= htmlspecialchars($iName) ?>">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                      </button>
                    </div>
                    <button type="submit" class="btn-mm btn-mm-light btn-mm-sm cart-update-btn d-none" title="Update item">
                      <i class="fas fa-rotate" aria-hidden="true"></i><span>Update</span>
                    </button>
                  </form>
                </div>

                <div class="line-item__side line-item__side--row">
                  <span class="line-item__total num fs-6" id="lineSubtotal-<?= $iId ?>">&#8377;<?= number_format($iSub, 2) ?></span>
                  
                  <!-- Move to Wishlist -->
                  <button type="button" class="icon-btn" data-act="cart-to-wishlist" 
                          data-cart-id="<?= $iId ?>" 
                          data-product-id="<?= $iPid ?>"
                          data-name="<?= htmlspecialchars($iName) ?>"
                          aria-label="Save <?= htmlspecialchars($iName) ?> for later in wishlist" 
                          title="Save for later in wishlist">
                    <i class="far fa-heart" aria-hidden="true"></i>
                  </button>

                  <!-- Remove Item -->
                  <a class="icon-btn icon-btn--danger" href="<?= url('cart/remove') . '&id=' . $iId . '&product_id=' . $iPid ?>"
                     data-act="remove-cart"
                     data-cart-id="<?= $iId ?>"
                     data-product-id="<?= $iPid ?>"
                     data-confirm="Remove <?= htmlspecialchars($iName) ?> from your cart?"
                     aria-label="Remove <?= htmlspecialchars($iName) ?>" title="Remove from cart">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <div class="panel__foot d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <a class="btn-mm btn-mm-ghost btn-mm-sm" href="<?= url('products') ?>">
              <i class="fas fa-arrow-left" aria-hidden="true"></i><span>Keep shopping</span>
            </a>
            <form action="<?= url('cart/clear') ?>" method="POST" class="m-0" id="cartClearForm">
              <button type="submit" class="btn-mm btn-mm-danger btn-mm-sm"
                      data-confirm="Empty your entire cart? Every item will be removed.">
                <i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Empty cart</span>
              </button>
            </form>
          </div>
        </div>

      </div>

      <!-- Right Column: Sticky Order Summary & Receipt -->
      <div class="col-lg-4">
        <div class="receipt-col">

          <!-- Coupon / Promo Code Card -->
          <div class="mm-card p-3 mb-3 shadow-sm" style="border-radius: var(--r-md); background: #fff;">
            <p class="eyebrow mb-2"><i class="fas fa-tag me-1" style="color: var(--teal-700);"></i>Have a Promo Code?</p>
            
            <?php if (!empty($appliedCoupon)): ?>
              <div class="p-2 px-3 rounded d-flex align-items-center justify-content-between gap-2" 
                   style="background: #E4F3EC; border: 1px dashed #1F7A55;">
                <div>
                  <strong class="text-success" style="font-size: 0.9rem;"><?= htmlspecialchars($appliedCoupon['code']) ?></strong>
                  <div style="font-size: 0.75rem; color: #1F7A55;"><?= htmlspecialchars($appliedCoupon['title']) ?> applied</div>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-bold" id="removeCouponBtn" style="font-size: 0.8rem;">
                  <i class="fas fa-xmark me-1"></i>Remove
                </button>
              </div>
            <?php else: ?>
              <form action="<?= url('cart/apply-coupon') ?>" method="POST" id="couponForm" class="d-flex gap-2 mb-2">
                <input type="text" name="coupon_code" id="couponInput" class="form-control form-control-sm text-uppercase fw-bold" 
                       placeholder="e.g. MEDI10" style="letter-spacing: 0.5px; border-radius: var(--r-sm);">
                <button type="submit" class="btn-mm btn-mm-primary btn-mm-sm px-3" style="white-space: nowrap;">
                  Apply
                </button>
              </form>
              <div class="d-flex flex-wrap gap-1 align-items-center" style="font-size: 0.75rem; color: var(--ink-60);">
                <span>Suggested:</span>
                <button type="button" class="badge border bg-light text-dark coupon-chip" data-code="MEDI10" style="cursor: pointer;">MEDI10 (10% OFF)</button>
                <button type="button" class="badge border bg-light text-dark coupon-chip" data-code="SAVE50" style="cursor: pointer;">SAVE50 (&#8377;50 OFF)</button>
              </div>
            <?php endif; ?>
          </div>

          <!-- Printable / Order Receipt Box -->
          <div class="mm-card p-3 mb-0" style="border-radius:var(--r-lg) var(--r-lg) 0 0;border-bottom:0">
            <p class="eyebrow mb-0">Order summary</p>
          </div>

          <div class="receipt">
            <div class="d-flex align-items-baseline justify-content-between">
              <h2 class="h5 mb-0">Payable now</h2>
              <span class="num" style="font-size:.72rem;color:var(--ink-40)"><?= date('d M Y') ?></span>
            </div>

            <div class="receipt__rows" id="cartReceiptRows">
              <div class="receipt__row">
                <span>Items</span>
                <span class="num" id="receiptItemCount"><?= $count ?></span>
              </div>
              <div class="receipt__row">
                <span>Units</span>
                <span class="num" id="cartUnits"><?= $units ?></span>
              </div>
              <div class="receipt__row">
                <span>Subtotal (MRP)</span>
                <span class="num" id="cartSubtotal">&#8377;<?= number_format($subtotal, 2) ?></span>
              </div>
              
              <!-- Coupon Discount Row -->
              <div class="receipt__row" id="cartDiscountRow" style="<?= $discountAmount > 0 ? '' : 'display:none;' ?>">
                <span class="text-success"><i class="fas fa-ticket me-1"></i>Coupon Discount</span>
                <span class="num text-success fw-bold" id="cartDiscount">-&#8377;<?= number_format($discountAmount, 2) ?></span>
              </div>

              <!-- Delivery Fee Row -->
              <div class="receipt__row">
                <span>Delivery</span>
                <span class="num" id="cartDelivery">
                  <?php if ($deliveryFee === 0.00): ?>
                    <span class="badge bg-success" style="font-size:0.65rem">FREE</span>
                  <?php else: ?>
                    &#8377;<?= number_format($deliveryFee, 2) ?>
                  <?php endif; ?>
                </span>
              </div>

              <!-- Grand Total Row -->
              <div class="receipt__row receipt__row--total">
                <span>Total Payable</span>
                <span class="num" id="cartTotal" style="color: var(--navy-deep, #1B3E5E);">&#8377;<?= number_format($grandTotal, 2) ?></span>
              </div>
            </div>

            <a class="btn-mm btn-mm-amber btn-mm-block btn-mm-lg" id="checkoutBtn" href="<?= url('checkout') ?>">
              <i class="fas fa-lock" aria-hidden="true"></i><span>Proceed to Checkout</span>
            </a>

            <hr class="label-rule">

            <ul class="list-unstyled m-0" style="font-size:.8rem;color:var(--ink-60);display:grid;gap:.5rem">
              <li><i class="fas fa-truck-medical me-2" style="color:var(--teal-700)" aria-hidden="true"></i>Free delivery over &#8377;499</li>
              <li><i class="fas fa-rotate-left me-2" style="color:var(--teal-700)" aria-hidden="true"></i>7-day returns on unopened packs</li>
              <li><i class="fas fa-shield-heart me-2" style="color:var(--teal-700)" aria-hidden="true"></i>Genuine stock, batch-tracked</li>
            </ul>

            <p class="receipt__note">
              Prescription medicines are verified by our licensed pharmacist before dispatch.
              You will be invited to upload your prescription if required.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State (shown when cart has no items) -->
    <div id="cartEmptyState" style="<?= !empty($items) ? 'display:none;' : '' ?>">
      <?php Controller::component('empty_state', [
          'title'      => 'Your cart is empty',
          'message'    => 'You haven\'t added any medicines or wellness items yet. Browse our full shelf and find what you need.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-cart-shopping',
      ]); ?>
    </div>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
