<?php
/**
 * Stat card — renders bare (no grid column) so the parent .stat-grid owns layout.
 * $tone: '' | 'amber' | 'navy' rotates the accent bar and icon chip.
 */
$title = $title ?? 'Stat';
$value = $value ?? '0';
$icon  = $icon  ?? 'fas fa-circle-info';
$link  = $link  ?? null;
$tone  = $tone  ?? '';
$toneClass = $tone !== '' ? ' stat-card--' . $tone : '';
$tag = $link ? 'a' : 'article';
?>
<<?= $tag ?> class="stat-card<?= $toneClass ?>"<?= $link ? ' href="' . htmlspecialchars($link) . '"' : '' ?>>
  <div>
    <p class="stat-card__label"><?= htmlspecialchars($title) ?></p>
    <p class="stat-card__value"><?= htmlspecialchars((string) $value) ?></p>
  </div>
  <span class="stat-card__icon"><i class="<?= htmlspecialchars($icon) ?>" aria-hidden="true"></i></span>
</<?= $tag ?>>
