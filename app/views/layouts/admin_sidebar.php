<?php
$currentPage = $_GET['page'] ?? 'admin';
$adminUser   = $adminUser ?? ['name' => 'Admin User', 'role' => 'Administrator'];
$firstLetter = strtoupper(substr((string) $adminUser['name'], 0, 1));

// Grouped so the rail reads as a workspace, not a flat list of links.
$groups = [
    'Overview' => [
        ['page' => 'admin',                 'icon' => 'fa-chart-pie',   'label' => 'Dashboard'],
    ],
    'Catalogue' => [
        ['page' => 'admin-products',        'icon' => 'fa-boxes-stacked', 'label' => 'Products'],
        ['page' => 'admin-add-product',     'icon' => 'fa-plus',          'label' => 'Add product'],
    ],
    'Fulfilment' => [
        ['page' => 'admin-orders',          'icon' => 'fa-truck-fast',    'label' => 'Orders'],
    ],
    'People' => [
        ['page' => 'admin-users',           'icon' => 'fa-users',         'label' => 'Users'],
        ['page' => 'admin-add-user',        'icon' => 'fa-user-plus',     'label' => 'Add user'],
    ],
    'Your account' => [
        ['page' => 'admin-update-profile',  'icon' => 'fa-id-card',       'label' => 'Update profile'],
        ['page' => 'admin-update-password', 'icon' => 'fa-key',           'label' => 'Change password'],
    ],
];
?>
<a class="admin-rail__brand" href="<?= url('admin') ?>">
  <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
  <span>MediMax<small>Console</small></span>
</a>

<div class="admin-rail__user">
  <span class="avatar" aria-hidden="true"><?= $firstLetter ?></span>
  <div>
    <strong><?= htmlspecialchars($adminUser['name']) ?></strong>
    <span><?= htmlspecialchars($adminUser['role'] ?? 'staff') ?></span>
  </div>
</div>

<?php foreach ($groups as $groupLabel => $links): ?>
  <div class="admin-rail__group">
    <p><?= htmlspecialchars($groupLabel) ?></p>
    <nav aria-label="<?= htmlspecialchars($groupLabel) ?>">
      <?php foreach ($links as $link): ?>
        <a class="rail-link<?= isActive($link['page'], $currentPage) ? ' is-active' : '' ?>"
           href="<?= url($link['page']) ?>"
           <?= isActive($link['page'], $currentPage) ? 'aria-current="page"' : '' ?>>
          <i class="fas <?= $link['icon'] ?>" aria-hidden="true"></i><?= htmlspecialchars($link['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
<?php endforeach; ?>

<div class="admin-rail__foot">
  <nav aria-label="Leave the console">
    <a class="rail-link" href="<?= url() ?>"><i class="fas fa-globe" aria-hidden="true"></i>Go to the shop</a>
    <a class="rail-link is-exit" href="<?= url('login') ?>"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>Log out</a>
  </nav>
</div>
