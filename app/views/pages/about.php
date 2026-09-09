<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'About Us - MediMax.com']);

$values = [
    ['icon' => 'fa-certificate',   'title' => 'Genuine stock, always',      'body' => 'Every pack is bought direct from the manufacturer or an authorised distributor, and stored at the temperature it was made for.'],
    ['icon' => 'fa-tag',           'title' => 'One price, printed',         'body' => 'The price on the card is the price you pay. No member tiers, no surge on essentials, no fees revealed at the last step.'],
    ['icon' => 'fa-user-doctor',   'title' => 'A pharmacist, not a bot',    'body' => 'A registered pharmacist checks prescription orders before dispatch and is on the phone during opening hours.'],
    ['icon' => 'fa-box-archive',   'title' => 'Batch and expiry tracked',   'body' => 'We dispense from the front of the shelf, so what arrives has the same dating you would get across a counter.'],
];

$shelves = ['Vitamins', 'Supplements', 'Skincare', 'Hair Care', 'First Aid', 'Baby Care', 'Personal Care', 'Beverages'];
?>

<section class="hero">
  <div class="mm-container">
    <div class="hero__grid">

      <div class="hero__copy">
        <p class="eyebrow rise" data-step="1">About MediMax &middot; Licensed chemist</p>
        <h1 class="title-hero rise" data-step="2">A chemist that behaves like <em>your</em> chemist.</h1>
        <p class="lede rise" data-step="3">
          MediMax started as a single counter and now dispenses across the city from the same shelves.
          We are not a marketplace: every product listed here is stock we hold, checked by people who
          answer the phone when you call.
        </p>
        <div class="hero__actions rise" data-step="4">
          <a class="btn-mm btn-mm-primary btn-mm-lg" href="<?= url('products') ?>">
            <i class="fas fa-basket-shopping" aria-hidden="true"></i><span>Browse the shelf</span>
          </a>
          <a class="btn-mm btn-mm-ghost btn-mm-lg" href="<?= url('contact') ?>">
            <span>Talk to us</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="hero__figure rise" data-step="3">
        <div class="hero__frame">
          <img src="<?= asset('images/Medical.gif') ?>" alt="Illustration of pharmacy supplies and medical instruments">
        </div>
        <div class="hero__tag rise" data-step="5">
          <p class="eyebrow mb-2">Dispensing since</p>
          <span class="num">2019</span>
          <p>across 8 shelves and thousands of orders</p>
        </div>
      </div>

    </div>
  </div>
</section>

<hr class="capsule-rule my-2">

<!-- What we stand for ------------------------------------------------------ -->
<section class="section pb-0">
  <div class="mm-container">
    <div class="page-head reveal">
      <p class="eyebrow">How we work</p>
      <h2 class="title-section mt-2 mb-0">Four commitments we can be held to</h2>
    </div>

    <div class="action-grid reveal" data-delay="1" style="grid-template-columns:repeat(auto-fit,minmax(276px,1fr))">
      <?php foreach ($values as $v): ?>
        <article class="mm-card p-4">
          <span class="stat-card__icon mb-3"><i class="fas <?= $v['icon'] ?>" aria-hidden="true"></i></span>
          <h3 class="h6 mb-2"><?= htmlspecialchars($v['title']) ?></h3>
          <p class="mb-0" style="font-size:.87rem;color:var(--ink-60);line-height:1.65"><?= htmlspecialchars($v['body']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Mission + shelves ----------------------------------------------------- -->
<section class="section">
  <div class="mm-container">
    <div class="row g-4 align-items-start">

      <div class="col-lg-7">
        <div class="panel h-100">
          <div class="panel__head">
            <div>
              <p class="eyebrow mb-1">Our mission</p>
              <h2>Healthcare you can actually reach</h2>
            </div>
          </div>
          <div class="panel__body">
            <p style="font-size:.95rem;line-height:1.75">
              Getting hold of ordinary medicine should not require a day off work, a queue,
              or a guess about whether the price is fair. We put the whole shelf online with
              honest pricing, plain descriptions, and delivery that reaches the doorstep the
              same day in metro pin codes.
            </p>

            <hr class="label-rule">

            <h3 class="h6 mb-2">What we hold in stock</h3>
            <p class="mb-3" style="font-size:.88rem;color:var(--ink-60)">
              Eight shelves, restocked weekly. Tap one to jump straight to it.
            </p>
            <div class="chip-row">
              <?php foreach ($shelves as $s): ?>
                <a class="chip" href="<?= url('products') ?>&amp;cat=<?= urlencode($s) ?>"><?= htmlspecialchars($s) ?></a>
              <?php endforeach; ?>
            </div>

            <hr class="label-rule">

            <h3 class="h6 mb-2">Our story</h3>
            <p class="mb-0" style="font-size:.92rem;line-height:1.75;color:var(--ink-60)">
              One store, one dispensing counter, and a rule that nobody leaves without knowing
              how to take what they bought. That rule survived the move online. The catalogue is
              bigger and the deliveries reach further, but the person checking your order is still
              a pharmacist, and the stock is still ours.
            </p>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="mm-card p-4 p-lg-5 h-100">
          <p class="eyebrow">Practical details</p>
          <h2 class="title-section mt-2 mb-4" style="font-size:clamp(1.3rem,2vw,1.6rem)">Good to know</h2>

          <dl class="m-0" style="display:grid;gap:1.15rem">
            <div>
              <dt class="mm-label mb-1">Opening hours</dt>
              <dd class="m-0 num" style="font-size:.9rem">Mon&ndash;Sat &middot; 08:00&ndash;22:00 IST</dd>
            </div>
            <div>
              <dt class="mm-label mb-1">Delivery</dt>
              <dd class="m-0" style="font-size:.9rem">Same-day in metro pin codes, 2&ndash;4 days elsewhere. Free over <span class="num">&#8377;499</span>.</dd>
            </div>
            <div>
              <dt class="mm-label mb-1">Returns</dt>
              <dd class="m-0" style="font-size:.9rem">7 days on unopened packs. Medicines that need cold storage cannot be returned.</dd>
            </div>
            <div>
              <dt class="mm-label mb-1">Prescriptions</dt>
              <dd class="m-0" style="font-size:.9rem">Upload at checkout where required. A pharmacist verifies before dispatch.</dd>
            </div>
          </dl>

          <hr class="label-rule">

          <p class="mb-3" style="font-size:.88rem;color:var(--ink-60)">
            Questions about a product, an order, or a return?
          </p>
          <a class="btn-mm btn-mm-teal btn-mm-block" href="<?= url('contact') ?>">
            <i class="fas fa-headset" aria-hidden="true"></i><span>Contact the pharmacy</span>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<?php Controller::partial('layouts/footer'); ?>
