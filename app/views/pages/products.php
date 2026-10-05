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
                <!-- Product 1 -->
        <article class="product-card position-relative" data-category="Vitamins" data-price="299" data-name="Vitamin C Tablets" data-index="0">
          <div class="product-card__label">
            <span class="product-card__cat">Vitamins</span>
            <span class="product-card__sku">MM001</span>
          </div>
          <div class="product-card__media">
            <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;">14% OFF</span>
            <img src="<?= asset('images/Vitamin C.jpeg') ?>" alt="Vitamin C Tablets" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Vitamin C Tablets">Vitamin C Tablets</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;349</span>
                  <span class="num">&#8377;299</span>
                </div>
                <span style="color: var(--primary-teal, #70ABAF); font-size: 0.75rem; font-weight: bold;">Save &#8377;50</span>
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Vitamin C Tablets" aria-label="Add Vitamin C Tablets to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Vitamin C Tablets" aria-pressed="false" aria-label="Save Vitamin C Tablets to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 2 -->
        <article class="product-card position-relative" data-category="Supplements" data-price="680" data-name="Fish Oil" data-index="1">
          <div class="product-card__label">
            <span class="product-card__cat">Supplements</span>
            <span class="product-card__sku">MM002</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Fish Oil.jpg') ?>" alt="Fish Oil" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Fish Oil">Fish Oil</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;680</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Fish Oil" aria-label="Add Fish Oil to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Fish Oil" aria-pressed="false" aria-label="Save Fish Oil to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 3 -->
        <article class="product-card position-relative" data-category="Supplements" data-price="1250" data-name="Creatine" data-index="2">
          <div class="product-card__label">
            <span class="product-card__cat">Supplements</span>
            <span class="product-card__sku">MM003</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Creatine.jpeg') ?>" alt="Creatine" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Creatine">Creatine</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;1,250</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Creatine" aria-label="Add Creatine to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Creatine" aria-pressed="false" aria-label="Save Creatine to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 4 -->
        <article class="product-card position-relative" data-category="Supplements" data-price="1899" data-name="Protein Shake" data-index="3">
          <div class="product-card__label">
            <span class="product-card__cat">Supplements</span>
            <span class="product-card__sku">MM004</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Protein Shake.jpeg') ?>" alt="Protein Shake" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Protein Shake">Protein Shake</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;1,899</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Protein Shake" aria-label="Add Protein Shake to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Protein Shake" aria-pressed="false" aria-label="Save Protein Shake to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 5 -->
        <article class="product-card position-relative" data-category="Supplements" data-price="2100" data-name="Multisource Protein" data-index="4">
          <div class="product-card__label">
            <span class="product-card__cat">Supplements</span>
            <span class="product-card__sku">MM005</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Multisource Protein.jpeg') ?>" alt="Multisource Protein" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Multisource Protein">Multisource Protein</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;2,100</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Multisource Protein" aria-label="Add Multisource Protein to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Multisource Protein" aria-pressed="false" aria-label="Save Multisource Protein to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 6 -->
        <article class="product-card position-relative" data-category="Hair Care" data-price="599" data-name="Hair Vitamins" data-index="5">
          <div class="product-card__label">
            <span class="product-card__cat">Hair Care</span>
            <span class="product-card__sku">MM006</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Hair Vitamins.jpg') ?>" alt="Hair Vitamins" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Hair Vitamins">Hair Vitamins</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;599</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Hair Vitamins" aria-label="Add Hair Vitamins to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Hair Vitamins" aria-pressed="false" aria-label="Save Hair Vitamins to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 7 -->
        <article class="product-card position-relative" data-category="Vitamins" data-price="320" data-name="Calcium Syrup" data-index="6">
          <div class="product-card__label">
            <span class="product-card__cat">Vitamins</span>
            <span class="product-card__sku">MM007</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Calcium Syrup.jpeg') ?>" alt="Calcium Syrup" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Calcium Syrup">Calcium Syrup</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;320</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Calcium Syrup" aria-label="Add Calcium Syrup to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Calcium Syrup" aria-pressed="false" aria-label="Save Calcium Syrup to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 8 -->
        <article class="product-card position-relative" data-category="Vitamins" data-price="275" data-name="Turmeric Supplement" data-index="7">
          <div class="product-card__label">
            <span class="product-card__cat">Vitamins</span>
            <span class="product-card__sku">MM008</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Turmeric.jpg') ?>" alt="Turmeric Supplement" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Turmeric Supplement">Turmeric Supplement</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;275</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Turmeric Supplement" aria-label="Add Turmeric Supplement to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Turmeric Supplement" aria-pressed="false" aria-label="Save Turmeric Supplement to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 9 -->
        <article class="product-card position-relative" data-category="Skincare" data-price="399" data-name="Acne Control Gel" data-index="8">
          <div class="product-card__label">
            <span class="product-card__cat">Skincare</span>
            <span class="product-card__sku">MM009</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Acne Control Gel.jpeg') ?>" alt="Acne Control Gel" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Acne Control Gel">Acne Control Gel</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;399</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Acne Control Gel" aria-label="Add Acne Control Gel to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Acne Control Gel" aria-pressed="false" aria-label="Save Acne Control Gel to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 10 -->
        <article class="product-card position-relative" data-category="Hair Care" data-price="550" data-name="Argan Oil" data-index="9">
          <div class="product-card__label">
            <span class="product-card__cat">Hair Care</span>
            <span class="product-card__sku">MM010</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Argan oil.jpeg') ?>" alt="Argan Oil" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Argan Oil">Argan Oil</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;550</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Argan Oil" aria-label="Add Argan Oil to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Argan Oil" aria-pressed="false" aria-label="Save Argan Oil to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 11 -->
        <article class="product-card position-relative" data-category="Skincare" data-price="485" data-name="Nourishing Cream" data-index="10">
          <div class="product-card__label">
            <span class="product-card__cat">Skincare</span>
            <span class="product-card__sku">MM011</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Nourishing cream.jpeg') ?>" alt="Nourishing Cream" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Nourishing Cream">Nourishing Cream</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;485</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Nourishing Cream" aria-label="Add Nourishing Cream to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Nourishing Cream" aria-pressed="false" aria-label="Save Nourishing Cream to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 12 -->
        <article class="product-card position-relative" data-category="Skincare" data-price="780" data-name="Floslek Suncare" data-index="11">
          <div class="product-card__label">
            <span class="product-card__cat">Skincare</span>
            <span class="product-card__sku">MM012</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Floslek sun care.jpeg') ?>" alt="Floslek Suncare" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Floslek Suncare">Floslek Suncare</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;780</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Floslek Suncare" aria-label="Add Floslek Suncare to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Floslek Suncare" aria-pressed="false" aria-label="Save Floslek Suncare to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 13 -->
        <article class="product-card position-relative" data-category="First Aid" data-price="120" data-name="Bandage Roll" data-index="12">
          <div class="product-card__label">
            <span class="product-card__cat">First Aid</span>
            <span class="product-card__sku">MM013</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Bandage Roll.jpg') ?>" alt="Bandage Roll" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Bandage Roll">Bandage Roll</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;120</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Bandage Roll" aria-label="Add Bandage Roll to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Bandage Roll" aria-pressed="false" aria-label="Save Bandage Roll to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 14 -->
        <article class="product-card position-relative" data-category="First Aid" data-price="85" data-name="Bandage" data-index="13">
          <div class="product-card__label">
            <span class="product-card__cat">First Aid</span>
            <span class="product-card__sku">MM014</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Bandage.jpg') ?>" alt="Bandage" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Bandage">Bandage</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;85</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Bandage" aria-label="Add Bandage to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Bandage" aria-pressed="false" aria-label="Save Bandage to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 15 -->
        <article class="product-card position-relative" data-category="First Aid" data-price="195" data-name="Dettol" data-index="14">
          <div class="product-card__label">
            <span class="product-card__cat">First Aid</span>
            <span class="product-card__sku">MM015</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/dettol.jpg') ?>" alt="Dettol" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Dettol">Dettol</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;195</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Dettol" aria-label="Add Dettol to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Dettol" aria-pressed="false" aria-label="Save Dettol to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 16 -->
        <article class="product-card position-relative" data-category="Baby Care" data-price="350" data-name="Diaper Care Cream" data-index="15">
          <div class="product-card__label">
            <span class="product-card__cat">Baby Care</span>
            <span class="product-card__sku">MM016</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Diaper Care Cream.jpg') ?>" alt="Diaper Care Cream" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Diaper Care Cream">Diaper Care Cream</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;350</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Diaper Care Cream" aria-label="Add Diaper Care Cream to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Diaper Care Cream" aria-pressed="false" aria-label="Save Diaper Care Cream to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 17 -->
        <article class="product-card position-relative" data-category="Hair Care" data-price="420" data-name="Conditioner" data-index="16">
          <div class="product-card__label">
            <span class="product-card__cat">Hair Care</span>
            <span class="product-card__sku">MM017</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Conditioner.jpg') ?>" alt="Conditioner" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Conditioner">Conditioner</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;420</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Conditioner" aria-label="Add Conditioner to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Conditioner" aria-pressed="false" aria-label="Save Conditioner to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 18 -->
        <article class="product-card position-relative" data-category="Hair Care" data-price="375" data-name="Dove Shampoo" data-index="17">
          <div class="product-card__label">
            <span class="product-card__cat">Hair Care</span>
            <span class="product-card__sku">MM018</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/dove-shampoo.jpg') ?>" alt="Dove Shampoo" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Dove Shampoo">Dove Shampoo</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;375</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Dove Shampoo" aria-label="Add Dove Shampoo to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Dove Shampoo" aria-pressed="false" aria-label="Save Dove Shampoo to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 19 -->
        <article class="product-card position-relative" data-category="Beverages" data-price="299" data-name="Green Tea" data-index="18">
          <div class="product-card__label">
            <span class="product-card__cat">Beverages</span>
            <span class="product-card__sku">MM019</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/green-tea.jpg') ?>" alt="Green Tea" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Green Tea">Green Tea</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;299</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Green Tea" aria-label="Add Green Tea to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Green Tea" aria-pressed="false" aria-label="Save Green Tea to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 20 -->
        <article class="product-card position-relative" data-category="Beverages" data-price="450" data-name="Nescafe Coffee" data-index="19">
          <div class="product-card__label">
            <span class="product-card__cat">Beverages</span>
            <span class="product-card__sku">MM020</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/nescafe-cofee.jpg') ?>" alt="Nescafe Coffee" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Nescafe Coffee">Nescafe Coffee</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;450</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Nescafe Coffee" aria-label="Add Nescafe Coffee to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Nescafe Coffee" aria-pressed="false" aria-label="Save Nescafe Coffee to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 21 -->
        <article class="product-card position-relative" data-category="Personal Care" data-price="310" data-name="Peppermint Oil" data-index="20">
          <div class="product-card__label">
            <span class="product-card__cat">Personal Care</span>
            <span class="product-card__sku">MM021</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/Peperment.jpg') ?>" alt="Peppermint Oil" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Peppermint Oil">Peppermint Oil</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;310</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Peppermint Oil" aria-label="Add Peppermint Oil to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Peppermint Oil" aria-pressed="false" aria-label="Save Peppermint Oil to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 22 -->
        <article class="product-card position-relative" data-category="Skincare" data-price="220" data-name="Rose Water" data-index="21">
          <div class="product-card__label">
            <span class="product-card__cat">Skincare</span>
            <span class="product-card__sku">MM022</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/rose-water.jpg') ?>" alt="Rose Water" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Rose Water">Rose Water</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;220</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Rose Water" aria-label="Add Rose Water to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Rose Water" aria-pressed="false" aria-label="Save Rose Water to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 23 -->
        <article class="product-card position-relative" data-category="Personal Care" data-price="340" data-name="Olive Oil" data-index="22">
          <div class="product-card__label">
            <span class="product-card__cat">Personal Care</span>
            <span class="product-card__sku">MM023</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/olive-oil.jpg') ?>" alt="Olive Oil" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Olive Oil">Olive Oil</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;340</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Olive Oil" aria-label="Add Olive Oil to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Olive Oil" aria-pressed="false" aria-label="Save Olive Oil to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 24 -->
        <article class="product-card position-relative" data-category="Skincare" data-price="465" data-name="Simple Face Wash" data-index="23">
          <div class="product-card__label">
            <span class="product-card__cat">Skincare</span>
            <span class="product-card__sku">MM024</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/simple-facewash.jpg') ?>" alt="Simple Face Wash" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Simple Face Wash">Simple Face Wash</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;465</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Simple Face Wash" aria-label="Add Simple Face Wash to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Simple Face Wash" aria-pressed="false" aria-label="Save Simple Face Wash to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 25 -->
        <article class="product-card position-relative" data-category="Personal Care" data-price="290" data-name="OHBT Spray" data-index="24">
          <div class="product-card__label">
            <span class="product-card__cat">Personal Care</span>
            <span class="product-card__sku">MM025</span>
          </div>
          <div class="product-card__media">
            
            <img src="<?= asset('images/OHBT-spray.jpg') ?>" alt="OHBT Spray" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="OHBT Spray">OHBT Spray</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  
                  <span class="num">&#8377;290</span>
                </div>
                
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="OHBT Spray" aria-label="Add OHBT Spray to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="OHBT Spray" aria-pressed="false" aria-label="Save OHBT Spray to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 26 -->
        <article class="product-card position-relative" data-category="First Aid" data-price="99" data-name="Paracetamol 500mg" data-index="25">
          <div class="product-card__label">
            <span class="product-card__cat">First Aid</span>
            <span class="product-card__sku">MM026</span>
          </div>
          <div class="product-card__media">
            <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;">18% OFF</span>
            <img src="<?= asset('images/placeholder.png') ?>" alt="Paracetamol 500mg" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Paracetamol 500mg">Paracetamol 500mg</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;120</span>
                  <span class="num">&#8377;99</span>
                </div>
                <span style="color: var(--primary-teal, #70ABAF); font-size: 0.75rem; font-weight: bold;">Save &#8377;21</span>
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Paracetamol 500mg" aria-label="Add Paracetamol 500mg to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Paracetamol 500mg" aria-pressed="false" aria-label="Save Paracetamol 500mg to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 27 -->
        <article class="product-card position-relative" data-category="Medical Devices" data-price="1699" data-name="Blood Pressure Monitor" data-index="26">
          <div class="product-card__label">
            <span class="product-card__cat">Medical Devices</span>
            <span class="product-card__sku">MM027</span>
          </div>
          <div class="product-card__media">
            <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;">15% OFF</span>
            <img src="<?= asset('images/placeholder.png') ?>" alt="Blood Pressure Monitor" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="Blood Pressure Monitor">Blood Pressure Monitor</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;1,999</span>
                  <span class="num">&#8377;1,699</span>
                </div>
                <span style="color: var(--primary-teal, #70ABAF); font-size: 0.75rem; font-weight: bold;">Save &#8377;300</span>
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="Blood Pressure Monitor" aria-label="Add Blood Pressure Monitor to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="Blood Pressure Monitor" aria-pressed="false" aria-label="Save Blood Pressure Monitor to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>
        <!-- Product 28 -->
        <article class="product-card position-relative" data-category="First Aid" data-price="649" data-name="First Aid Kit" data-index="27">
          <div class="product-card__label">
            <span class="product-card__cat">First Aid</span>
            <span class="product-card__sku">MM028</span>
          </div>
          <div class="product-card__media">
            <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;">19% OFF</span>
            <img src="<?= asset('images/placeholder.png') ?>" alt="First Aid Kit" loading="lazy" data-fallback="<?= asset('images/MediMax_Logo.png') ?>">
          </div>
          <div class="product-card__body">
            <h3 class="product-card__name" title="First Aid Kit">First Aid Kit</h3>
          </div>
          <div class="product-card__foot flex-wrap">
            <div class="product-card__price" style="min-width: 0;">
              <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
              <div class="d-flex flex-column">
                <div class="d-flex align-items-baseline gap-1 flex-wrap">
                  <span class="text-muted text-decoration-line-through num" style="font-size: 0.8em;">&#8377;799</span>
                  <span class="num">&#8377;649</span>
                </div>
                <span style="color: var(--primary-teal, #70ABAF); font-size: 0.75rem; font-weight: bold;">Save &#8377;150</span>
              </div>
            </div>
            <div class="product-card__acts flex-shrink-0">
              <button class="icon-btn" type="button" data-act="cart" data-name="First Aid Kit" aria-label="Add First Aid Kit to cart" title="Add to cart">
                <i class="fas fa-cart-plus" aria-hidden="true"></i>
              </button>
              <button class="icon-btn" type="button" data-act="fav" data-name="First Aid Kit" aria-pressed="false" aria-label="Save First Aid Kit to wishlist" title="Save to wishlist">
                <i class="far fa-heart" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </article>

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

