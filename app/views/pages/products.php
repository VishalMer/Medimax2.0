<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Products - MediMax.com']);

$searchQuery = $searchQuery ?? ($_GET['q'] ?? '');
$total       = is_array($products ?? null) ? count($products) : 0;

// Shelves present in the current result set, so a chip never leads to an empty grid.
$shelves = [];
foreach (($products ?? []) as $p) {
    $c = $p['category'] ?? 'Pharmacy';
    $shelves[$c] = ($shelves[$c] ?? 0) + 1;
}
ksort($shelves);
?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <?php if ($searchQuery !== ''): ?>
        <p class="eyebrow">Search results</p>
        <h1 class="title-page">&ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;</h1>
        <p class="lede mb-0">
          <span class="num"><?= $total ?></span> <?= $total === 1 ? 'product' : 'products' ?> matched.
          <a href="<?= url('products') ?>">Clear the search</a>
        </p>
      <?php else: ?>
        <p class="eyebrow">The full shelf</p>
        <h1 class="title-page">All products</h1>
        <p class="lede mb-0">Everything MediMax dispenses, in one place. Filter by shelf or sort by price.</p>
      <?php endif; ?>
    </div>

    <?php if (!empty($products)): ?>

      <!-- Filter bar -->
      <div class="mm-card p-3 p-md-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <p class="eyebrow mb-0">Filter by shelf</p>
          <div class="d-flex align-items-center gap-2">
            <label class="mm-label mb-0" for="catalogueSort">Sort</label>
            <select class="mm-input" id="catalogueSort" style="width:auto;padding:.5rem .8rem;font-size:.85rem">
              <option value="default">Featured</option>
              <option value="price-asc">Price: low to high</option>
              <option value="price-desc">Price: high to low</option>
              <option value="name-asc">Name: A to Z</option>
            </select>
          </div>
        </div>

        <div class="chip-row">
          <button class="chip is-active" type="button" data-filter="all" aria-pressed="true">
            All shelves <span class="chip__n"><?= $total ?></span>
          </button>
          <?php foreach ($shelves as $name => $count): ?>
            <button class="chip" type="button" data-filter="<?= htmlspecialchars($name) ?>" aria-pressed="false">
              <?= htmlspecialchars($name) ?> <span class="chip__n"><?= $count ?></span>
            </button>
          <?php endforeach; ?>
        </div>

        <hr class="label-rule">
        <p class="mb-0" style="font-size:.8rem;color:var(--ink-60)">
          Showing <span class="num" id="catalogueCount"><?= $total ?></span> of <span class="num"><?= $total ?></span> products
        </p>
      </div>

      <div class="product-grid" id="catalogue">
        <?php foreach ($products as $i => $product): ?>
          <?php Controller::component('product_card', ['product' => $product, 'index' => $i]); ?>
        <?php endforeach; ?>
      </div>

      <div id="catalogueEmpty" hidden class="mt-4">
        <?php Controller::component('empty_state', [
            'title'      => 'Nothing on that shelf',
            'message'    => 'This shelf is empty in the current result set. Pick another one.',
            'buttonText' => 'Show all products',
            'buttonLink' => url('products'),
            'icon'       => 'fa-layer-group',
        ]); ?>
      </div>

    <?php else: ?>

      <?php Controller::component('empty_state', [
          'title'      => 'No match on our shelves',
          'message'    => 'We could not find a product with that name. Try a shorter term, or browse the full catalogue.',
          'buttonText' => 'Show all products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-magnifying-glass',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
