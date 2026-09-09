<?php
ob_start();

$shortcuts = [
    ['page' => 'admin-products',        'icon' => 'fa-boxes-stacked', 'label' => 'Products list',    'hint' => 'Edit or retire a product'],
    ['page' => 'admin-add-product',     'icon' => 'fa-circle-plus',   'label' => 'Add a product',    'hint' => 'Put new stock on the shelf'],
    ['page' => 'admin-orders',          'icon' => 'fa-truck-fast',    'label' => 'Orders',           'hint' => 'Mark payment and delivery'],
    ['page' => 'admin-users',           'icon' => 'fa-address-book',  'label' => 'Users list',       'hint' => 'Roles and contact details'],
    ['page' => 'admin-add-user',        'icon' => 'fa-user-plus',     'label' => 'Add a user',       'hint' => 'Invite staff or a customer'],
    ['page' => 'admin-update-profile',  'icon' => 'fa-id-card',       'label' => 'Update profile',   'hint' => 'Your name, email, photo'],
];

// Display-only read of data the app already exposes; nothing is written here.
$orders    = $orders ?? (class_exists('DummyData') ? DummyData::getOrders() : []);
$pending   = 0;
$inTransit = 0;
foreach ($orders as $o) {
    if (($o['payment']  ?? '') !== 'Completed') { $pending++; }
    if (($o['delivery'] ?? '') !== 'Delivered') { $inTransit++; }
}
?>

<p class="lede mb-4" style="max-width:62ch">
  Everything that needs a decision today, in one screen.
  <?php if ($pending || $inTransit): ?>
    <strong><span class="num"><?= $pending ?></span> payments</strong> are unconfirmed and
    <strong><span class="num"><?= $inTransit ?></span> parcels</strong> are still moving.
  <?php else: ?>
    Nothing is waiting on you.
  <?php endif; ?>
</p>

<!-- Numbers ------------------------------------------------------------- -->
<section class="mb-5">
  <div class="admin-section-head">
    <div>
      <p class="eyebrow">This month</p>
      <h2 class="title-section mt-2 mb-0">Overview</h2>
    </div>
    <a class="btn-mm btn-mm-ghost btn-mm-sm" href="<?= url('admin-orders') ?>">
      <span>Open orders</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>

  <div class="stat-grid">
    <?php Controller::component('stat_card', [
        'title' => 'Total orders',
        'value' => $stats['total_orders'] ?? 0,
        'icon'  => 'fas fa-cart-arrow-down',
        'link'  => url('admin-orders'),
    ]); ?>
    <?php Controller::component('stat_card', [
        'title' => 'Total customers',
        'value' => $stats['total_customers'] ?? 0,
        'icon'  => 'fas fa-users',
        'tone'  => 'navy',
        'link'  => url('admin-users'),
    ]); ?>
    <?php Controller::component('stat_card', [
        'title' => 'Total turnover',
        'value' => '₹' . number_format($stats['total_turnover'] ?? 0, 2),
        'icon'  => 'fas fa-indian-rupee-sign',
        'tone'  => 'amber',
    ]); ?>
    <?php Controller::component('stat_card', [
        'title' => 'Awaiting payment',
        'value' => $pending,
        'icon'  => 'fas fa-hourglass-half',
        'tone'  => 'amber',
        'link'  => url('admin-orders'),
    ]); ?>
  </div>
</section>

<!-- Needs attention ----------------------------------------------------- -->
<section class="mb-5">
  <div class="admin-section-head">
    <div>
      <p class="eyebrow">Queue</p>
      <h2 class="title-section mt-2 mb-0">Needs attention</h2>
      <p>Orders where payment or delivery is not finished yet.</p>
    </div>
  </div>

  <div class="mm-table-wrap">
    <div class="mm-table-scroll">
      <table class="mm-table">
        <thead>
          <tr>
            <th scope="col">Order</th>
            <th scope="col">Product</th>
            <th scope="col">Qty</th>
            <th scope="col">Payment</th>
            <th scope="col">Delivery</th>
            <th scope="col">Placed</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $queue = array_filter($orders, static function ($o) {
              return ($o['payment'] ?? '') !== 'Completed' || ($o['delivery'] ?? '') !== 'Delivered';
          });
          ?>
          <?php if ($queue): ?>
            <?php foreach ($queue as $o): ?>
              <?php $stamp = strtotime((string) ($o['placed_on'] ?? '')) ?: null; ?>
              <tr>
                <td><span class="mm-table__id">#MX-<?= str_pad((string) ($o['id'] ?? 0), 4, '0', STR_PAD_LEFT) ?></span></td>
                <td>
                  <div class="cell-product">
                    <img class="mm-table__thumb" src="<?= asset('images/' . ($o['image'] ?? '')) ?>" alt="" loading="lazy"
                         data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
                    <div>
                      <strong><?= htmlspecialchars($o['name'] ?? 'Product') ?></strong>
                      <span>user <?= htmlspecialchars((string) ($o['user_id'] ?? '—')) ?></span>
                    </div>
                  </div>
                </td>
                <td><span class="num">&times;<?= (int) ($o['quantity'] ?? 1) ?></span></td>
                <td>
                  <span class="pill pill--<?= ($o['payment'] ?? '') === 'Completed' ? 'ok' : 'warn' ?>">
                    <?= htmlspecialchars($o['payment'] ?? 'Pending') ?>
                  </span>
                </td>
                <td>
                  <span class="pill pill--<?= ($o['delivery'] ?? '') === 'Delivered' ? 'ok' : (($o['delivery'] ?? '') === 'Shipped' ? 'info' : 'warn') ?>">
                    <?= htmlspecialchars($o['delivery'] ?? 'Processing') ?>
                  </span>
                </td>
                <td><span class="num"><?= $stamp ? date('d M', $stamp) : '—' ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="mm-table__empty">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                Every order is paid and delivered.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Shortcuts ----------------------------------------------------------- -->
<section>
  <div class="admin-section-head">
    <div>
      <p class="eyebrow">Jump to</p>
      <h2 class="title-section mt-2 mb-0">Common tasks</h2>
    </div>
  </div>

  <div class="action-grid">
    <?php foreach ($shortcuts as $s): ?>
      <a class="action-tile" href="<?= url($s['page']) ?>">
        <i class="fas <?= $s['icon'] ?> action-tile__ico" aria-hidden="true"></i>
        <div>
          <strong><?= htmlspecialchars($s['label']) ?></strong>
          <span><?= htmlspecialchars($s['hint']) ?></span>
        </div>
        <i class="fas fa-arrow-right action-tile__arrow" aria-hidden="true"></i>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
