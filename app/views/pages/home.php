<?php Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Home - MediMax.com']);

// Shelves mirror the categories the catalogue actually stocks.
$shelves = [
    ['name' => 'Vitamins',      'icon' => 'fa-pills'],
    ['name' => 'Supplements',   'icon' => 'fa-dumbbell'],
    ['name' => 'Skincare',      'icon' => 'fa-droplet'],
    ['name' => 'Hair Care',     'icon' => 'fa-wind'],
    ['name' => 'First Aid',     'icon' => 'fa-kit-medical'],
    ['name' => 'Baby Care',     'icon' => 'fa-baby'],
    ['name' => 'Personal Care', 'icon' => 'fa-leaf'],
    ['name' => 'Beverages',     'icon' => 'fa-mug-hot'],
];
?>

<!-- Hero: the shelf you actually walk up to ------------------------------- -->
<section class="hero">
  <div class="mm-container">
    <div class="hero__grid">

      <div class="hero__copy">
        <p class="eyebrow rise" data-step="1">Licensed chemist &middot; Since 2019</p>
        <h1 class="title-hero rise" data-step="2">Everything your medicine cabinet needs, <em>delivered</em>.</h1>
        <p class="lede rise" data-step="3">
          Browse the full shelf, check what you are actually buying, and order in a few taps.
          Every product is dispensed from our own stock and priced in plain rupees &mdash; no surprises at checkout.
        </p>
        <div class="hero__actions rise" data-step="4">
          <a class="btn-mm btn-mm-primary btn-mm-lg" href="<?= url('products') ?>">
            <i class="fas fa-basket-shopping" aria-hidden="true"></i><span>Browse products</span>
          </a>
          <a class="btn-mm btn-mm-ghost btn-mm-lg" href="<?= url('about') ?>">
            <span>Our story</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="hero__figure rise" data-step="3">
        <div class="hero__frame">
          <img src="<?= asset('images/MediMax-BG.jpeg') ?>" alt="Medicines and instruments laid out on a pharmacy counter">
        </div>
        <div class="hero__tag rise" data-step="5">
          <p class="eyebrow mb-2">In stock today</p>
          <span class="num">25 products</span>
          <p>across 8 shelves, ready to dispatch</p>
        </div>
      </div>

    </div>

    <div class="assurance rise" data-step="5">
      <div class="assurance__item">
        <i class="fas fa-truck-medical" aria-hidden="true"></i>
        <div><strong>Free delivery over &#8377;499</strong><span>Same-day in metro pin codes</span></div>
      </div>
      <div class="assurance__item">
        <i class="fas fa-shield-heart" aria-hidden="true"></i>
        <div><strong>Genuine stock only</strong><span>Sourced direct from manufacturers</span></div>
      </div>
      <div class="assurance__item">
        <i class="fas fa-rotate-left" aria-hidden="true"></i>
        <div><strong>7-day returns</strong><span>Unopened packs, no questions</span></div>
      </div>
      <div class="assurance__item">
        <i class="fas fa-headset" aria-hidden="true"></i>
        <div><strong>Pharmacist on call</strong><span>Mon&ndash;Sat, 08:00&ndash;22:00 IST</span></div>
      </div>
    </div>
  </div>
</section>

<hr class="capsule-rule my-2">

<!-- Shelves --------------------------------------------------------------- -->
<section class="section pb-0">
  <div class="mm-container">
    <div class="page-head reveal">
      <p class="eyebrow">Shop by shelf</p>
      <h2 class="title-section mt-2 mb-0">Find it the way a chemist files it</h2>
    </div>
    <div class="product-grid reveal" data-delay="1" style="grid-template-columns:repeat(auto-fill,minmax(178px,1fr))">
      <?php foreach ($shelves as $shelf): ?>
        <a class="action-tile" href="<?= url('products') ?>&amp;cat=<?= urlencode($shelf['name']) ?>">
          <i class="fas <?= $shelf['icon'] ?> action-tile__ico" aria-hidden="true"></i>
          <strong><?= htmlspecialchars($shelf['name']) ?></strong>
          <i class="fas fa-arrow-right action-tile__arrow" aria-hidden="true"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Best sellers ---------------------------------------------------------- -->
<section class="section">
  <div class="mm-container">
    <div class="admin-section-head reveal">
      <div>
        <p class="eyebrow">Moving fastest this week</p>
        <h2 class="title-section mt-2 mb-0">Best sellers</h2>
      </div>
      <a class="btn-mm btn-mm-ghost btn-mm-sm" href="<?= url('products') ?>">
        <span>See all 25</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>

    <?php if (!empty($products)): ?>
      <div class="product-grid">
        <?php foreach ($products as $i => $product): ?>
          <div class="reveal" data-delay="<?= min($i + 1, 5) ?>">
            <?php Controller::component('product_card', ['product' => $product, 'index' => $i]); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?php Controller::component('empty_state', [
          'title'      => 'The shelf is being restocked',
          'message'    => 'No products to show right now. Try the full catalogue.',
          'buttonText' => 'Browse products',
          'buttonLink' => url('products'),
          'icon'       => 'fa-boxes-stacked',
      ]); ?>
    <?php endif; ?>
  </div>
</section>

<!-- How ordering works: a real sequence, so it earns its numbers ---------- -->
<section class="section pt-0">
  <div class="mm-container">
    <div class="panel reveal">
      <div class="row g-0 align-items-stretch">
        <div class="col-lg-5">
          <div class="h-100 d-flex align-items-center justify-content-center p-4" style="background:linear-gradient(170deg,var(--mist),#fff)">
            <img src="<?= asset('images/Medical.gif') ?>" alt="" style="max-height:300px;border-radius:var(--r-md)">
          </div>
        </div>
        <div class="col-lg-7">
          <div class="p-4 p-lg-5">
            <p class="eyebrow">Three steps, start to doorstep</p>
            <h2 class="title-section mt-2 mb-4">Ordering, without the queue</h2>

            <ol class="list-unstyled m-0">
              <li class="d-flex gap-3 pb-4">
                <span class="num flex-shrink-0" style="color:var(--amber);font-size:.8rem;padding-top:.25rem">01</span>
                <div>
                  <h3 class="h6 mb-1">Search the catalogue</h3>
                  <p class="mb-0" style="font-size:.89rem;color:var(--ink-60)">Filter by shelf or search by name. Prices and pack details are on the card &mdash; no digging.</p>
                </div>
              </li>
              <li class="d-flex gap-3 pb-4">
                <span class="num flex-shrink-0" style="color:var(--amber);font-size:.8rem;padding-top:.25rem">02</span>
                <div>
                  <h3 class="h6 mb-1">Add to your cart</h3>
                  <p class="mb-0" style="font-size:.89rem;color:var(--ink-60)">Adjust quantities, save the rest to your wishlist, and review the total before you commit.</p>
                </div>
              </li>
              <li class="d-flex gap-3">
                <span class="num flex-shrink-0" style="color:var(--amber);font-size:.8rem;padding-top:.25rem">03</span>
                <div>
                  <h3 class="h6 mb-1">Track it to your door</h3>
                  <p class="mb-0" style="font-size:.89rem;color:var(--ink-60)">Payment and delivery status stay visible on your orders page until the parcel arrives.</p>
                </div>
              </li>
            </ol>

            <a class="btn-mm btn-mm-teal mt-4" href="<?= url('orders') ?>">
              <i class="fas fa-box-open" aria-hidden="true"></i><span>View my orders</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php Controller::partial('layouts/footer'); ?>
