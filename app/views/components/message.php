<?php
/**
 * Flash messages — a live region so screen readers hear them, and each toast
 * dismisses on click (app.js also retires them on a timer).
 */
$messages = $messages ?? [];
?>
<?php if (!empty($messages) && is_array($messages)): ?>
<div class="toast-stack" role="status" aria-live="polite">
  <?php foreach ($messages as $msg): ?>
    <div class="toast-msg" tabindex="0">
      <i class="fas fa-circle-check" aria-hidden="true"></i>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
