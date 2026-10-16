<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Products - MediMax.com']);
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
        <button class="chip is-active" type="button">
          All shelves <span class="chip__n">10</span>
        </button>
        <button class="chip" type="button">
          Vitamins <span class="chip__n">2</span>
        </button>
        <button class="chip" type="button">
          Supplements <span class="chip__n">3</span>
        </button>
        <button class="chip" type="button">
          Skincare <span class="chip__n">2</span>
        </button>
        <button class="chip" type="button">
          Hair Care <span class="chip__n">2</span>
        </button>
        <button class="chip" type="button">
          First Aid <span class="chip__n">1</span>
        </button>
      </div>

      <hr class="label-rule">
      <p class="mb-0" style="font-size:.8rem;color:var(--ink-60)">
        Showing <span class="num">10</span> of <span class="num">10</span> products
      </p>
    </div>

    <!-- Product Grid: 10 Product Cards (No loops) -->
    <div class="product-grid" id="catalogue">

      <!-- Product 1 -->
      <article class="product-card position-relative" data-category="Vitamins" data-price="299" data-name="Vitamin C Tablets">
        <div class="product-card__label">
          <span class="product-card__cat">Vitamins</span>
          <span class="product-card__sku">MM001</span>
        </div>
        <div class="product-card__media">
          <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--accent-orange, rgb(230, 120, 52)); z-index: 10; font-size: 0.8rem;">14% OFF</span>
          <img src="<?= asset('images/Vitamin C.jpeg') ?>" alt="Vitamin C Tablets" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
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
      <article class="product-card position-relative" data-category="Supplements" data-price="680" data-name="Fish Oil">
        <div class="product-card__label">
          <span class="product-card__cat">Supplements</span>
          <span class="product-card__sku">MM002</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Fish Oil.jpg') ?>" alt="Fish Oil" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Fish Oil">Fish Oil Omega-3</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;680</span>
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
      <article class="product-card position-relative" data-category="Supplements" data-price="1250" data-name="Creatine">
        <div class="product-card__label">
          <span class="product-card__cat">Supplements</span>
          <span class="product-card__sku">MM003</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Creatine.jpeg') ?>" alt="Creatine" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Creatine">Creatine Monohydrate</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;1,250</span>
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
      <article class="product-card position-relative" data-category="Supplements" data-price="1899" data-name="Protein Shake">
        <div class="product-card__label">
          <span class="product-card__cat">Supplements</span>
          <span class="product-card__sku">MM004</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Protein Shake.jpeg') ?>" alt="Protein Shake" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Protein Shake">Protein Shake 1kg</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;1,899</span>
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
      <article class="product-card position-relative" data-category="Hair Care" data-price="599" data-name="Hair Vitamins">
        <div class="product-card__label">
          <span class="product-card__cat">Hair Care</span>
          <span class="product-card__sku">MM006</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Hair Vitamins.jpg') ?>" alt="Hair Vitamins" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Hair Vitamins">Hair Vitamins Gummies</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;599</span>
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

      <!-- Product 6 -->
      <article class="product-card position-relative" data-category="Vitamins" data-price="320" data-name="Calcium Syrup">
        <div class="product-card__label">
          <span class="product-card__cat">Vitamins</span>
          <span class="product-card__sku">MM007</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Calcium Syrup.jpeg') ?>" alt="Calcium Syrup" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Calcium Syrup">Calcium Syrup 200ml</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;320</span>
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

      <!-- Product 7 -->
      <article class="product-card position-relative" data-category="Skincare" data-price="399" data-name="Acne Control Gel">
        <div class="product-card__label">
          <span class="product-card__cat">Skincare</span>
          <span class="product-card__sku">MM009</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Acne Control Gel.jpeg') ?>" alt="Acne Control Gel" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Acne Control Gel">Acne Control Gel</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;399</span>
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

      <!-- Product 8 -->
      <article class="product-card position-relative" data-category="Hair Care" data-price="550" data-name="Argan Oil">
        <div class="product-card__label">
          <span class="product-card__cat">Hair Care</span>
          <span class="product-card__sku">MM010</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/Argan oil.jpeg') ?>" alt="Argan Oil" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Argan Oil">Pure Moroccan Argan Oil</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;550</span>
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

      <!-- Product 9 -->
      <article class="product-card position-relative" data-category="First Aid" data-price="195" data-name="Dettol">
        <div class="product-card__label">
          <span class="product-card__cat">First Aid</span>
          <span class="product-card__sku">MM015</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/dettol.jpg') ?>" alt="Dettol" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Dettol">Dettol Antiseptic Liquid</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;195</span>
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

      <!-- Product 10 -->
      <article class="product-card position-relative" data-category="Skincare" data-price="465" data-name="Simple Face Wash">
        <div class="product-card__label">
          <span class="product-card__cat">Skincare</span>
          <span class="product-card__sku">MM024</span>
        </div>
        <div class="product-card__media">
          <img src="<?= asset('images/simple-facewash.jpg') ?>" alt="Simple Face Wash" loading="lazy" data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name" title="Simple Face Wash">Simple Refreshing Face Wash</h3>
        </div>
        <div class="product-card__foot flex-wrap">
          <div class="product-card__price" style="min-width: 0;">
            <span class="eyebrow eyebrow--plain eyebrow--muted">Price</span>
            <div class="d-flex flex-column">
              <span class="num">&#8377;465</span>
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

    </div>

  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
