<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Products - MediMax.com']);

$productsList = $products ?? [];
$catCounts = [];
foreach ($productsList as $p) {
    $c = $p['category'] ?? 'Pharmacy';
    $catCounts[$c] = ($catCounts[$c] ?? 0) + 1;
}
$totalCount = count($productsList);
$selectedCat = $_GET['cat'] ?? 'all';
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">The full shelf</p>
      <h1 class="title-page">All products</h1>
      <p class="lede mb-0">Everything MediMax dispenses, in one place. Authentic medicines, wellness, and personal care.</p>
    </div>

    <!-- Filter bar -->
    <div class="mm-card p-3 p-md-4 mb-4">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <p class="eyebrow mb-0">Browse by shelf</p>
      </div>

      <div class="chip-row">
        <button class="chip <?= ($selectedCat === 'all' || empty($selectedCat)) ? 'is-active' : '' ?>" type="button" data-filter="all">
          All shelves <span class="chip__n"><?= $totalCount ?></span>
        </button>
        <?php foreach ($catCounts as $catName => $count): ?>
          <button class="chip <?= (strcasecmp($selectedCat, $catName) === 0) ? 'is-active' : '' ?>" type="button" data-filter="<?= htmlspecialchars($catName) ?>">
            <?= htmlspecialchars($catName) ?> <span class="chip__n"><?= $count ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <hr class="label-rule">
      <p class="mb-0" style="font-size:.8rem;color:var(--ink-60)">
        Showing <span class="num" id="visibleCount"><?= $totalCount ?></span> of <span class="num"><?= $totalCount ?></span> products
      </p>
    </div>

    <!-- Product Grid: Dynamic Active Products from Database -->
    <div class="product-grid" id="catalogue">
      <?php if (!empty($productsList)): ?>
        <?php foreach ($productsList as $i => $product): ?>
          <?php Controller::component('product_card', ['product' => $product, 'index' => $i]); ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <i class="fas fa-boxes-stacked fa-3x mb-3 text-muted" aria-hidden="true"></i>
          <p class="text-muted">No active products currently found on the shelf.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var chips = document.querySelectorAll('.chip[data-filter]');
  var cards = document.querySelectorAll('#catalogue .product-card');
  var countEl = document.getElementById('visibleCount');

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) { c.classList.remove('is-active'); });
      chip.classList.add('is-active');

      var filter = chip.getAttribute('data-filter');
      var visible = 0;

      cards.forEach(function (card) {
        var cardCat = card.getAttribute('data-category');
        if (filter === 'all' || cardCat.toLowerCase() === filter.toLowerCase()) {
          card.style.display = '';
          visible++;
        } else {
          card.style.display = 'none';
        }
      });

      if (countEl) {
        countEl.textContent = visible;
      }
    });
  });

  // Check URL query param ?cat=...
  var urlParams = new URLSearchParams(window.location.search);
  var initialCat = urlParams.get('cat');
  if (initialCat) {
    var matchChip = Array.from(chips).find(function (c) {
      return c.getAttribute('data-filter').toLowerCase() === initialCat.toLowerCase();
    });
    if (matchChip) {
      matchChip.click();
    }
  }
});
</script>

<?php Controller::partial('layouts/footer'); ?>
