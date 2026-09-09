<?php
/**
 * Empty state — a screen with nothing in it is an invitation to act,
 * so it always carries one clear next step.
 */
$title      = $title      ?? null;
$message    = $message    ?? 'Nothing to show here.';
$buttonText = $buttonText ?? 'Back to home';
$buttonLink = $buttonLink ?? url();
$image      = $image      ?? null;
$icon       = $icon       ?? 'fa-box-open';
?>
<div class="empty-state">
  <?php if ($image): ?>
    <img src="<?= asset('images/' . $image) ?>" alt="" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
  <?php else: ?>
    <i class="fas <?= htmlspecialchars($icon) ?> empty-state__icon" aria-hidden="true"></i>
  <?php endif; ?>

  <?php if ($title): ?>
    <h3><?= htmlspecialchars($title) ?></h3>
    <p><?= htmlspecialchars($message) ?></p>
  <?php else: ?>
    <h3><?= htmlspecialchars($message) ?></h3>
  <?php endif; ?>

  <a class="btn-mm btn-mm-teal" href="<?= htmlspecialchars($buttonLink) ?>">
    <span><?= htmlspecialchars($buttonText) ?></span>
    <i class="fas fa-arrow-right" aria-hidden="true"></i>
  </a>
</div>
