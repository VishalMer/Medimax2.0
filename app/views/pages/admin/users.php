<?php
ob_start();

$rows = $users ?? [];
$byRole = [];
foreach ($rows as $u) { $byRole[$u['role'] ?? 'customer'] = ($byRole[$u['role'] ?? 'customer'] ?? 0) + 1; }

$roleTone = static function (string $role): string {
    return match ($role) {
        'owner' => 'danger',
        'admin' => 'info',
        default => 'neutral',
    };
};
?>

<div class="admin-section-head">
  <div>
    <p class="eyebrow">People</p>
    <h2 class="title-section mt-2 mb-0">Users</h2>
    <p>
      <span class="num"><?= count($rows) ?></span> accounts
      <?php if ($byRole): ?>
        &middot;
        <?php $bits = []; foreach ($byRole as $r => $n) { $bits[] = $n . ' ' . $r; } echo htmlspecialchars(implode(', ', $bits)); ?>
      <?php endif; ?>
    </p>
  </div>
  <a class="btn-mm btn-mm-amber btn-mm-sm" href="<?= url('admin-add-user') ?>">
    <i class="fas fa-user-plus" aria-hidden="true"></i><span>Add user</span>
  </a>
</div>

<div class="mm-table-wrap">
  <div class="mm-table-scroll">
    <table class="mm-table">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Person</th>
          <th scope="col">Email</th>
          <th scope="col">Role</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $user): ?>
            <?php
              $uId    = $user['id'] ?? '';
              $uName  = $user['name'] ?? '';
              $uRole  = $user['role'] ?? 'customer';
              $uImage = $user['image'] ?? '';
            ?>
            <tr>
              <td><span class="mm-table__id">U<?= str_pad((string) $uId, 3, '0', STR_PAD_LEFT) ?></span></td>
              <td>
                <div class="cell-product">
                  <?php if ($uImage !== ''): ?>
                    <img class="mm-table__thumb" style="border-radius:50%" src="<?= asset('images/' . $uImage) ?>"
                         alt="" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
                  <?php else: ?>
                    <span class="avatar" data-avatar-tint="<?= htmlspecialchars($uName) ?>" aria-hidden="true">
                      <?= strtoupper(substr((string) $uName, 0, 1)) ?>
                    </span>
                  <?php endif; ?>
                  <div>
                    <strong><?= htmlspecialchars($uName) ?></strong>
                    <span><?= htmlspecialchars($uRole) ?></span>
                  </div>
                </div>
              </td>
              <td>
                <a href="mailto:<?= htmlspecialchars($user['email'] ?? '') ?>" style="font-size:.86rem;color:var(--navy);text-decoration:none">
                  <?= htmlspecialchars($user['email'] ?? '') ?>
                </a>
              </td>
              <td><span class="pill pill--<?= $roleTone($uRole) ?>"><?= htmlspecialchars(ucfirst($uRole)) ?></span></td>
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-user') ?>&amp;id=<?= $uId ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" class="btn-mm btn-mm-danger btn-mm-sm"
                        data-delete-user="<?= $uId ?>" data-user-name="<?= htmlspecialchars($uName) ?>">
                  <i class="fa-solid fa-trash" aria-hidden="true"></i><span>Delete</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="mm-table__empty">
              <i class="fas fa-users" aria-hidden="true"></i>
              No users yet. <a href="<?= url('admin-add-user') ?>">Add the first one</a>.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="mt-3 mb-0" style="font-size:.78rem;color:var(--ink-40)">
  Deleting is not wired to a route in this build &mdash; the button confirms, then reports what would be removed.
</p>

<script>
document.querySelectorAll('[data-delete-user]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var name = btn.getAttribute('data-user-name') || 'this user';
    if (!window.confirm('Delete the account for ' + name + '? This cannot be undone.')) return;
    if (window.MediMax) window.MediMax.toast('Delete requested for ' + name, 'fa-trash');
  });
});
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
