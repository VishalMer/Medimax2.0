<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Orders - MediMax.com']);

$rows = $orders ?? [];
$spent = 0.0;
$open  = 0;
foreach ($rows as $o) {
    $spent += (float) ($o['price'] ?? 0) * (int) ($o['quantity'] ?? 1);
    if (($o['delivery'] ?? '') !== 'Delivered') { $open++; }
}

/** Delivery stage → pill tone + icon. Processing → Shipped → Delivered. */
$deliveryTone = static function (string $s): array {
    return match ($s) {
        'Delivered' => ['ok',   'fa-circle-check'],
        'Shipped'   => ['info', 'fa-truck-fast'],
        default     => ['warn', 'fa-box-open'],
    };
};
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">Order history</p>
      <h1 class="title-page">My orders</h1>
      <p class="lede mb-0">
        <?php if ($rows): ?>
          <span class="num"><?= count($rows) ?></span> orders on record,
          <span class="num"><?= $open ?></span> still on the way.
          Payment and delivery stay visible here until the parcel arrives.
        <?php else: ?>
          Once you place an order, its payment and delivery status live on this page.
        <?php endif; ?>
      </p>
    </div>

    <?php if (!empty($rows)): ?>

      <div class="stat-grid mb-4">
        <article class="stat-card">
          <div>
            <p class="stat-card__label">Orders placed</p>
            <p class="stat-card__value"><?= count($rows) ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-receipt" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card stat-card--navy">
          <div>
            <p class="stat-card__label">In transit</p>
            <p class="stat-card__value"><?= $open ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-truck-fast" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card stat-card--amber">
          <div>
            <p class="stat-card__label">Total spent</p>
            <p class="stat-card__value">&#8377;<?= number_format($spent) ?></p>
          </div>
          <span class="stat-card__icon"><i class="fas fa-indian-rupee-sign" aria-hidden="true"></i></span>
        </article>
      </div>

      <div class="mm-table-wrap">
        <div class="mm-table-scroll">
          <table class="mm-table">
            <thead>
              <tr>
                <th scope="col">Order</th>
                <th scope="col">Product</th>
                <th scope="col">Qty</th>
                <th scope="col">Amount</th>
                <th scope="col">Payment</th>
                <th scope="col">Delivery</th>
                <th scope="col">Placed</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $order): ?>
                <?php
                  $oId    = $order['id'] ?? '';
                  $oName  = $order['name'] ?? 'Product';
                  $oQty   = (int) ($order['quantity'] ?? 1);
                  $oPrice = (float) ($order['price'] ?? 0);
                  $oImg   = $order['image'] ?? 'placeholder.png';
                  $pay    = $order['payment'] ?? 'Pending';
                  $del    = $order['delivery'] ?? 'Processing';
                  [$delTone, $delIcon] = $deliveryTone($del);
                  $stamp  = strtotime((string) ($order['placed_on'] ?? '')) ?: null;
                ?>
                <tr>
                  <td><span class="mm-table__id">#MX-<?= str_pad((string) $oId, 4, '0', STR_PAD_LEFT) ?></span></td>
                  <td>
                    <div class="cell-product">
                      <img class="mm-table__thumb" src="<?= asset('images/' . $oImg) ?>" alt="" loading="lazy"
                           data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
                      <div>
                        <strong><?= htmlspecialchars($oName) ?></strong>
                        <span>&#8377;<?= number_format($oPrice, 2) ?> each</span>
                      </div>
                    </div>
                  </td>
                  <td><span class="num">&times;<?= $oQty ?></span></td>
                  <td><span class="num">&#8377;<?= number_format($oPrice * $oQty, 2) ?></span></td>
                  <td>
                    <span class="pill pill--<?= $pay === 'Completed' ? 'ok' : 'warn' ?>">
                      <?= htmlspecialchars($pay) ?>
                    </span>
                  </td>
                  <td>
                    <span class="pill pill--<?= $delTone ?> pill--plain">
                      <i class="fas <?= $delIcon ?>" aria-hidden="true"></i><?= htmlspecialchars($del) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($stamp): ?>
                      <span class="num"><?= date('d M Y', $stamp) ?></span><br>
                      <span class="num" style="font-size:.7rem;color:var(--ink-40)"><?= date('H:i', $stamp) ?></span>
                    <?php else: ?>
                      <span style="color:var(--ink-40)">&mdash;</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <p class="mt-3 mb-0" style="font-size:.78rem;color:var(--ink-40)">
        Need a copy of an invoice, or a return picked up?
        <a href="<?= url('contact') ?>">Message the pharmacy</a> with the order number.
      </p>

    <?php else: ?>

      <?php Controller::component('empty_state', [
          'title'      => 'No orders yet',
          'message'    => 'Your order history is empty. Anything you buy shows up here with live payment and delivery status.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-receipt',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
