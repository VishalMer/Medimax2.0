<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'My Orders - MediMax.com']);

$rows = $orders ?? [];
$spent = 0.0;
$open = 0;

foreach ($rows as $o) {
    $spent += (float)($o['total_amount'] ?? $o['price'] ?? 0);
    $del = $o['delivery_status'] ?? $o['delivery'] ?? 'Processing';
    if (!in_array($del, ['Delivered', 'Cancelled'], true)) {
        $open++;
    }
}

/** Delivery stage → pill tone + icon */
$deliveryMeta = static function (string $status): array {
    return match ($status) {
        'Delivered' => ['tone' => 'ok',   'icon' => 'fa-circle-check', 'step' => 4],
        'Shipped'   => ['tone' => 'info', 'icon' => 'fa-truck-fast',   'step' => 3],
        'Cancelled' => ['tone' => 'danger','icon' => 'fa-circle-xmark', 'step' => 0],
        default     => ['tone' => 'warn', 'icon' => 'fa-box-open',     'step' => 2], // Processing
    };
};
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">Order history &amp; Tracking</p>
      <h1 class="title-page">My orders</h1>
      <p class="lede mb-0">
        <?php if (!empty($rows)): ?>
          <span class="num"><?= count($rows) ?></span> orders on record,
          <span class="num"><?= $open ?></span> currently in transit.
          Live dispensing updates, invoices, and delivery tracking are shown below.
        <?php else: ?>
          Once you place an order, its real-time dispensing status, tracking, and receipts live here.
        <?php endif; ?>
      </p>
    </div>

    <?php if (!empty($rows)): ?>

      <!-- Live KPI stats -->
      <div class="stat-grid mb-4">
        <article class="stat-card">
          <div>
            <p class="stat-card__label">Orders placed</p>
            <p class="stat-card__value num"><?= count($rows) ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-receipt" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card stat-card--navy">
          <div>
            <p class="stat-card__label">In transit / Active</p>
            <p class="stat-card__value num"><?= $open ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-truck-fast" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card stat-card--amber">
          <div>
            <p class="stat-card__label">Total spent</p>
            <p class="stat-card__value num">&#8377;<?= number_format($spent, 2) ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-indian-rupee-sign" aria-hidden="true"></i></span>
        </article>
      </div>

      <!-- Real Orders Cards Stack -->
      <div class="d-flex flex-column gap-4">
        <?php foreach ($rows as $order): ?>
          <?php
            $oId        = $order['id'] ?? 0;
            $orderNum   = $order['order_number'] ?? ('MED-2026-' . str_pad((string)$oId, 5, '0', STR_PAD_LEFT));
            $totalAmt   = (float)($order['total_amount'] ?? $order['price'] ?? 0);
            $subtotal   = (float)($order['subtotal'] ?? $totalAmt);
            $discount   = (float)($order['discount_amount'] ?? 0);
            $delFee     = (float)($order['delivery_fee'] ?? 0);
            $payStatus  = $order['payment_status'] ?? $order['payment'] ?? 'Pending';
            $payMethod  = strtoupper($order['payment_method'] ?? 'COD');
            $delStatus  = $order['delivery_status'] ?? $order['delivery'] ?? 'Processing';
            $placedDate = !empty($order['placed_at']) ? strtotime($order['placed_at']) : (!empty($order['placed_on']) ? strtotime($order['placed_on']) : null);
            $meta       = $deliveryMeta($delStatus);
            $items      = $order['items'] ?? [];
            $itemCount  = count($items);
          ?>
          <article class="mm-card p-0 overflow-hidden shadow-sm border" style="border-radius: var(--r-md, 12px);">
            
            <!-- Card Header: Order Number, Date, Status Badges -->
            <div class="p-3 p-md-4 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <span class="num fw-bold" style="font-size: 1.05rem; color: var(--primary-dark, #26547C);">
                    #<?= htmlspecialchars($orderNum) ?>
                  </span>
                  <span class="badge bg-secondary-subtle text-secondary" style="font-size: .75rem;">
                    <?= $itemCount ?> <?= $itemCount === 1 ? 'item' : 'items' ?>
                  </span>
                </div>
                <div class="text-muted" style="font-size: .8rem;">
                  <i class="far fa-calendar-alt me-1"></i>
                  Placed on <?= $placedDate ? date('d M Y, h:i A', $placedDate) : 'Recently' ?>
                </div>
              </div>

              <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Payment Badge -->
                <?php if ($payStatus === 'Completed'): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:.78rem;">
                    <i class="fas fa-check-circle me-1"></i>Paid (<?= htmlspecialchars($payMethod) ?>)
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size:.78rem;">
                    <i class="fas fa-hourglass-half me-1"></i>Payment on Delivery (<?= htmlspecialchars($payMethod) ?>)
                  </span>
                <?php endif; ?>

                <!-- Delivery Badge -->
                <span class="pill pill--<?= $meta['tone'] ?> pill--plain mb-0" style="font-size:.78rem; padding: 4px 10px;">
                  <i class="fas <?= $meta['icon'] ?>" aria-hidden="true"></i><?= htmlspecialchars($delStatus) ?>
                </span>
              </div>
            </div>

            <!-- Delivery Progress Stepper (Only for active / non-cancelled orders) -->
            <?php if ($delStatus !== 'Cancelled'): ?>
              <div class="px-3 px-md-4 pt-3 pb-2 border-bottom bg-white">
                <div class="d-flex justify-content-between align-items-center position-relative my-2 mx-2 mx-md-4" style="z-index: 1;">
                  <div class="position-absolute w-100" style="height: 3px; background-color: #e9ecef; top: 12px; transform: translateY(-50%); z-index: -1;"></div>
                  <?php
                    $progWidth = match($meta['step']) {
                      2 => '33%',
                      3 => '66%',
                      4 => '100%',
                      default => '0%'
                    };
                  ?>
                  <div class="position-absolute" style="height: 3px; background-color: #28a745; width: <?= $progWidth ?>; top: 12px; transform: translateY(-50%); z-index: -1;"></div>

                  <div class="text-center bg-white px-1">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto" style="width: 22px; height: 22px; font-size: .65rem;">
                      <i class="fas fa-check"></i>
                    </div>
                    <span style="font-size: .72rem;" class="fw-semibold text-success mt-1 d-block">Placed</span>
                  </div>

                  <div class="text-center bg-white px-1">
                    <div class="rounded-circle <?= $meta['step'] >= 2 ? 'bg-success text-white' : 'bg-light border text-muted' ?> d-flex align-items-center justify-content-center mx-auto" style="width: 22px; height: 22px; font-size: .65rem;">
                      <?= $meta['step'] >= 2 ? '<i class="fas fa-check"></i>' : '2' ?>
                    </div>
                    <span style="font-size: .72rem;" class="<?= $meta['step'] >= 2 ? 'fw-semibold text-success' : 'text-muted' ?> mt-1 d-block">Processing</span>
                  </div>

                  <div class="text-center bg-white px-1">
                    <div class="rounded-circle <?= $meta['step'] >= 3 ? 'bg-success text-white' : 'bg-light border text-muted' ?> d-flex align-items-center justify-content-center mx-auto" style="width: 22px; height: 22px; font-size: .65rem;">
                      <?= $meta['step'] >= 3 ? '<i class="fas fa-check"></i>' : '3' ?>
                    </div>
                    <span style="font-size: .72rem;" class="<?= $meta['step'] >= 3 ? 'fw-semibold text-success' : 'text-muted' ?> mt-1 d-block">Shipped</span>
                  </div>

                  <div class="text-center bg-white px-1">
                    <div class="rounded-circle <?= $meta['step'] >= 4 ? 'bg-success text-white' : 'bg-light border text-muted' ?> d-flex align-items-center justify-content-center mx-auto" style="width: 22px; height: 22px; font-size: .65rem;">
                      <?= $meta['step'] >= 4 ? '<i class="fas fa-check"></i>' : '4' ?>
                    </div>
                    <span style="font-size: .72rem;" class="<?= $meta['step'] >= 4 ? 'fw-semibold text-success' : 'text-muted' ?> mt-1 d-block">Delivered</span>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <!-- Line Items Table -->
            <div class="p-3 p-md-4">
              <?php if (!empty($items)): ?>
                <div class="table-responsive">
                  <table class="table align-middle mb-0" style="font-size: .88rem;">
                    <thead class="text-muted" style="font-size: .75rem; text-transform: uppercase;">
                      <tr>
                        <th style="min-width: 220px;">Product</th>
                        <th class="text-center">SKU</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($items as $item): ?>
                        <?php
                          $iPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                          $iQty   = (int)($item['quantity'] ?? 1);
                          $iSub   = (float)($item['subtotal'] ?? ($iPrice * $iQty));
                          $iImg   = $item['product_image'] ?? $item['image'] ?? 'placeholder.png';
                          $iName  = $item['product_name'] ?? $item['name'] ?? 'Product';
                          $iSku   = $item['product_sku'] ?? ('MM' . str_pad((string)($item['product_id'] ?? 1), 3, '0', STR_PAD_LEFT));
                        ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center gap-3">
                              <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>"
                                   style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; background: #fafafa; border: 1px solid #eee;"
                                   data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>" loading="lazy">
                              <div>
                                <strong class="d-block text-dark"><?= htmlspecialchars($iName) ?></strong>
                                <span class="text-muted" style="font-size: .75rem;">100% Genuine Pharmacy Stock</span>
                              </div>
                            </div>
                          </td>
                          <td class="text-center num text-muted" style="font-size: .8rem;"><?= htmlspecialchars($iSku) ?></td>
                          <td class="text-center num">&#8377;<?= number_format($iPrice, 2) ?></td>
                          <td class="text-center num fw-bold">&times;<?= $iQty ?></td>
                          <td class="text-end num fw-semibold" style="color: var(--primary-dark, #26547C);">&#8377;<?= number_format($iSub, 2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="d-flex align-items-center gap-3 py-2">
                  <i class="fas fa-box text-muted fs-3"></i>
                  <div>
                    <strong>Order items recorded</strong>
                    <div class="text-muted" style="font-size: .82rem;">Direct delivery package dispatched from pharmacy shelves.</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Card Footer: Shipping Details & Financial Breakdown -->
            <div class="p-3 p-md-4 bg-light border-top">
              <div class="row g-3 align-items-center justify-content-between">
                
                <!-- Left: Shipping Address & Notes -->
                <div class="col-md-7">
                  <p class="eyebrow mb-1" style="font-size:.7rem;">Delivering to</p>
                  <p class="mb-1 fw-semibold text-dark" style="font-size: .86rem;">
                    <?= htmlspecialchars($order['shipping_name'] ?? 'Recipient') ?> &middot;
                    <span class="text-muted num" style="font-weight: normal;"><?= htmlspecialchars($order['shipping_mobile'] ?? '') ?></span>
                  </p>
                  <p class="mb-0 text-muted" style="font-size: .82rem;">
                    <?= htmlspecialchars($order['shipping_address_line1'] ?? '') ?>
                    <?php if (!empty($order['shipping_address_line2'])): ?>
                      , <?= htmlspecialchars($order['shipping_address_line2']) ?>
                    <?php endif; ?>
                    , <?= htmlspecialchars($order['shipping_city'] ?? '') ?>
                    <?php if (!empty($order['shipping_state'])): ?>
                      , <?= htmlspecialchars($order['shipping_state']) ?>
                    <?php endif; ?>
                    <?php if (!empty($order['shipping_pincode'])): ?>
                      - <span class="num"><?= htmlspecialchars($order['shipping_pincode']) ?></span>
                    <?php endif; ?>
                  </p>
                </div>

                <!-- Right: Financial Breakdown -->
                <div class="col-md-5 text-md-end">
                  <div class="d-inline-block text-start" style="min-width: 220px;">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: .82rem;">
                      <span>Subtotal:</span>
                      <span class="num">&#8377;<?= number_format($subtotal, 2) ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                      <div class="d-flex justify-content-between text-success mb-1" style="font-size: .82rem;">
                        <span>
                          <i class="fas fa-ticket me-1"></i>Coupon Discount
                          <?php if (!empty($order['coupon_code'])): ?>
                            (<strong><?= htmlspecialchars($order['coupon_code']) ?></strong>)
                          <?php endif; ?>:
                        </span>
                        <span class="num fw-bold">&minus;&#8377;<?= number_format($discount, 2) ?></span>
                      </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: .82rem;">
                      <span>Delivery Fee:</span>
                      <span class="num"><?= $delFee > 0 ? ('&#8377;' . number_format($delFee, 2)) : '<strong class="text-success">FREE</strong>' ?></span>
                    </div>

                    <div class="d-flex justify-content-between pt-1 border-top mt-1" style="font-size: .95rem;">
                      <span class="fw-bold">Total Paid:</span>
                      <span class="num fw-bold" style="color: var(--accent-orange, rgb(230, 120, 52));">&#8377;<?= number_format($totalAmt, 2) ?></span>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Helpful action links -->
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 mt-3 border-top" style="font-size: .8rem;">
                <span class="text-muted">
                  <i class="fas fa-shield-halved text-success me-1"></i>Protected by MediMax 7-day genuine returns policy
                </span>
                <a class="text-decoration-none fw-semibold" style="color: var(--primary-dark, #26547C);"
                   href="<?= url('contact') ?>&amp;subject=<?= urlencode('Question regarding Order #' . $orderNum) ?>">
                  <i class="fas fa-headset me-1"></i>Contact Pharmacy about this order &rarr;
                </a>
              </div>

            </div>

          </article>
        <?php endforeach; ?>
      </div>

    <?php else: ?>

      <!-- Clean Empty State Component -->
      <?php Controller::component('empty_state', [
          'title'      => 'No orders placed yet',
          'message'    => 'Your order history is currently empty. Once you purchase genuine medicines or health essentials from our shelves, your orders and live tracking appear here.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-receipt',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
