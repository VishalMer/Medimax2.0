<?php
ob_start();

$rows = $orders ?? [];
$revenue = 0.0;
$awaitingPay = 0;
$awaitingDelivery = 0;
foreach ($rows as $o) {
    $revenue += (float) ($o['price'] ?? 0) * (int) ($o['quantity'] ?? 1);
    if (($o['payment']  ?? '') !== 'Completed') { $awaitingPay++; }
    if (($o['delivery'] ?? '') !== 'Delivered') { $awaitingDelivery++; }
}
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">Fulfilment</p>
    <h2 class="title-section mt-2 mb-0">All orders</h2>
    <p><span class="num"><?= count($rows) ?></span> orders &middot; <span class="num">&#8377;<?= number_format($revenue, 2) ?></span> booked</p>
  </div>
</div>

<div class="stat-grid mb-4">
  <?php Controller::component('stat_card', [
      'title' => 'Awaiting payment', 'value' => $awaitingPay, 'icon' => 'fas fa-hourglass-half', 'tone' => 'amber',
  ]); ?>
  <?php Controller::component('stat_card', [
      'title' => 'Still in transit', 'value' => $awaitingDelivery, 'icon' => 'fas fa-truck-fast', 'tone' => 'navy',
  ]); ?>
  <?php Controller::component('stat_card', [
      'title' => 'Booked value', 'value' => '₹' . number_format($revenue, 2), 'icon' => 'fas fa-indian-rupee-sign',
  ]); ?>
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
          <th scope="col" class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $order): ?>
            <?php
              $oId   = $order['id'] ?? '';
              $oName = $order['name'] ?? 'Product';
              $oQty  = (int) ($order['quantity'] ?? 1);
              $oAmt  = (float) ($order['price'] ?? 0) * $oQty;
              $pay   = $order['payment']  ?? 'Pending';
              $del   = $order['delivery'] ?? 'Processing';
              $stamp = strtotime((string) ($order['placed_on'] ?? '')) ?: null;
              $delTone = $del === 'Delivered' ? 'ok' : ($del === 'Shipped' ? 'info' : 'warn');
            ?>
            <tr>
              <td>
                <span class="mm-table__id">#MX-<?= str_pad((string) $oId, 4, '0', STR_PAD_LEFT) ?></span><br>
                <span style="font-size:.7rem;color:var(--ink-40)">user <?= htmlspecialchars((string) ($order['user_id'] ?? '—')) ?></span>
              </td>
              <td>
                <div class="cell-product">
                  <img class="mm-table__thumb" src="<?= asset('images/' . ($order['image'] ?? '')) ?>" alt="" loading="lazy"
                       data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
                  <div>
                    <strong><?= htmlspecialchars($oName) ?></strong>
                    <span>&#8377;<?= number_format((float) ($order['price'] ?? 0), 2) ?> each</span>
                  </div>
                </div>
              </td>
              <td><span class="num">&times;<?= $oQty ?></span></td>
              <td><span class="num">&#8377;<?= number_format($oAmt, 2) ?></span></td>
              <td>
                <span class="pill pill--<?= $pay === 'Completed' ? 'ok' : 'warn' ?>"><?= htmlspecialchars($pay) ?></span>
                <?php if ($pay !== 'Completed'): ?>
                  <button type="button" class="btn-mm btn-mm-light btn-mm-sm mt-2" data-mark="payment" data-order="<?= $oId ?>">
                    <i class="fas fa-check" aria-hidden="true"></i><span>Mark paid</span>
                  </button>
                <?php endif; ?>
              </td>
              <td>
                <span class="pill pill--<?= $delTone ?>"><?= htmlspecialchars($del) ?></span>
                <?php if ($del !== 'Delivered'): ?>
                  <button type="button" class="btn-mm btn-mm-light btn-mm-sm mt-2" data-mark="delivery" data-order="<?= $oId ?>">
                    <i class="fas fa-check" aria-hidden="true"></i><span>Mark delivered</span>
                  </button>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($stamp): ?>
                  <span class="num"><?= date('d M Y', $stamp) ?></span><br>
                  <span class="num" style="font-size:.7rem;color:var(--ink-40)"><?= date('H:i', $stamp) ?></span>
                <?php else: ?>
                  <span style="color:var(--ink-40)">&mdash;</span>
                <?php endif; ?>
              </td>
              <td class="is-actions text-end">
                <button type="button" class="btn-mm btn-mm-danger btn-mm-sm"
                        data-remove-order="<?= $oId ?>">
                  <i class="fa-solid fa-trash" aria-hidden="true"></i><span>Remove</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="mm-table__empty">
              <i class="fas fa-receipt" aria-hidden="true"></i>
              No orders yet.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="mt-3 mb-0" style="font-size:.78rem;color:var(--ink-40)">
  Status changes and removals are not wired to routes in this build &mdash; the buttons confirm the intent and report it.
</p>

<script>
// Prototype stubs: report the intent rather than faking a saved change.
(function () {
  var code = function (id) { return '#MX-' + ('000' + id).slice(-4); };

  document.querySelectorAll('[data-mark]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var what = btn.getAttribute('data-mark') === 'payment' ? 'Payment' : 'Delivery';
      if (window.MediMax) {
        window.MediMax.toast(what + ' update queued for ' + code(btn.getAttribute('data-order')), 'fa-check');
      }
    });
  });

  document.querySelectorAll('[data-remove-order]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-remove-order');
      if (!window.confirm('Remove order ' + code(id) + ' from the list?')) return;
      if (window.MediMax) window.MediMax.toast('Removal requested for ' + code(id), 'fa-trash');
    });
  });
})();
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
