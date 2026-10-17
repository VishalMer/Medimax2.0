<?php
ob_start();

$rows = $users ?? [];
$byRole = [];
$activeCount = 0;
foreach ($rows as $u) {
    $r = $u['role'] ?? 'customer';
    $byRole[$r] = ($byRole[$r] ?? 0) + 1;
    $isActive = isset($u['is_active']) ? ((int)$u['is_active'] === 1) : (($u['status'] ?? 'active') === 'active');
    if ($isActive) { $activeCount++; }
}

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
      <span class="num"><?= count($rows) ?></span> accounts (<span class="num"><?= $activeCount ?></span> active)
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
    <table class="mm-table" id="usersTable">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Person</th>
          <th scope="col">Email</th>
          <th scope="col">Role</th>
          <th scope="col">Status</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $user): ?>
            <?php
              $uId      = (int)($user['id'] ?? 0);
              $uName    = $user['name'] ?? '';
              $uRole    = $user['role'] ?? 'customer';
              $uImage   = $user['image'] ?? '';
              $isActive = isset($user['is_active']) ? ((int)$user['is_active'] === 1) : (($user['status'] ?? 'active') === 'active');
            ?>
            <tr id="user-row-<?= $uId ?>" class="<?= $isActive ? '' : 'is-row-inactive' ?>">
              <td><span class="mm-table__id">U<?= str_pad((string) $uId, 3, '0', STR_PAD_LEFT) ?></span></td>
              <td>
                <div class="cell-product">
                  <?php if (!empty($uImage)): ?>
                    <img class="mm-table__thumb" style="border-radius:50%" src="<?= asset('images/' . $uImage) ?>"
                         alt="" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
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
              <td>
                <button type="button" 
                        class="status-toggle-btn <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                        data-type="user" 
                        data-id="<?= $uId ?>"
                        title="<?= $isActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate' ?>">
                  <span class="status-dot"></span>
                  <span class="status-text"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                </button>
              </td>
              <td class="is-actions text-end">
                <a class="btn-mm btn-mm-light btn-mm-sm" href="<?= url('admin-edit-user') ?>&amp;id=<?= $uId ?>">
                  <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Edit</span>
                </a>
                <button type="button" 
                        class="btn-mm <?= $isActive ? 'btn-mm-danger' : 'btn-mm-teal' ?> btn-mm-sm action-soft-toggle"
                        data-type="user"
                        data-id="<?= $uId ?>" 
                        data-action="<?= $isActive ? 'deactivate' : 'activate' ?>"
                        data-name="<?= htmlspecialchars($uName) ?>">
                  <i class="fa-solid <?= $isActive ? 'fa-user-slash' : 'fa-user-check' ?>" aria-hidden="true"></i>
                  <span><?= $isActive ? 'Deactivate' : 'Activate' ?></span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="mm-table__empty">
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
  <i class="fa-solid fa-circle-info me-1"></i> Deactivating a user preserves all order history and database relations while preventing login. Use the status toggle button or action button to activate or deactivate instantly.
</p>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function handleStatusToggle(type, id, action, callback) {
    var url = '<?= url("admin-toggle-status") ?>&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id) + '&action=' + encodeURIComponent(action) + '&ajax=1';
    
    fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success && typeof callback === 'function') {
        callback(data);
      } else if (!data.success) {
        alert(data.message || 'Operation failed.');
      }
    })
    .catch(function (err) {
      console.error(err);
      window.location.reload();
    });
  }

  // Handle direct click on Status pill button
  document.querySelectorAll('#usersTable .status-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var row = document.getElementById('user-row-' + id);
      var actionBtn = row ? row.querySelector('.action-soft-toggle') : null;

      handleStatusToggle(type, id, 'toggle', function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.classList.toggle('is-active', isNowActive);
        btn.classList.toggle('is-inactive', !isNowActive);
        btn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
        btn.setAttribute('title', isNowActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate');
        
        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (actionBtn) {
          actionBtn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
          actionBtn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
          actionBtn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-user-slash' : 'fa-user-check');
          actionBtn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';
        }
      });
    });
  });

  // Handle Deactivate / Activate action button with confirmation
  document.querySelectorAll('#usersTable .action-soft-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-id');
      var type = this.getAttribute('data-type');
      var action = this.getAttribute('data-action');
      var name = this.getAttribute('data-name');
      var row = document.getElementById('user-row-' + id);
      var statusBtn = row ? row.querySelector('.status-toggle-btn') : null;

      var promptMsg = (action === 'deactivate') 
        ? "Deactivate user '" + name + "'? Their account will be set to Inactive (soft-deleted) and they will be barred from logging in."
        : "Reactivate user '" + name + "'? Their account will be set to Active.";

      if (!confirm(promptMsg)) return;

      handleStatusToggle(type, id, action, function (data) {
        var isNowActive = (parseInt(data.is_active, 10) === 1);
        
        btn.setAttribute('data-action', isNowActive ? 'deactivate' : 'activate');
        btn.className = 'btn-mm ' + (isNowActive ? 'btn-mm-danger' : 'btn-mm-teal') + ' btn-mm-sm action-soft-toggle';
        btn.querySelector('i').className = 'fa-solid ' + (isNowActive ? 'fa-user-slash' : 'fa-user-check');
        btn.querySelector('span').textContent = isNowActive ? 'Deactivate' : 'Activate';

        if (row) {
          row.classList.toggle('is-row-inactive', !isNowActive);
        }
        if (statusBtn) {
          statusBtn.classList.toggle('is-active', isNowActive);
          statusBtn.classList.toggle('is-inactive', !isNowActive);
          statusBtn.querySelector('.status-text').textContent = isNowActive ? 'Active' : 'Inactive';
          statusBtn.setAttribute('title', isNowActive ? 'Active: Click to Deactivate' : 'Inactive: Click to Activate');
        }
      });
    });
  });
});
</script>

<?php
$pageContent = ob_get_clean();
require APP_PATH . '/views/layouts/admin_layout.php';
?>
