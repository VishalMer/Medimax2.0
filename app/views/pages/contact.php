<?php Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Contact Us - MediMax.com']); ?>

<div class="page">
  <div class="mm-container">

    <div class="page-head">
      <p class="eyebrow">Contact</p>
      <h1 class="title-page">Talk to the pharmacy</h1>
      <p class="lede mb-0">
        A registered pharmacist reads every message. Ask about a product, an order,
        a substitution, or a return &mdash; we reply within one working day.
      </p>
    </div>

    <div class="row g-4">

      <div class="col-lg-7">
        <div class="panel">
          <div class="panel__head">
            <div>
              <p class="eyebrow mb-1">Message us</p>
              <h2>Send a question</h2>
            </div>
          </div>

          <div class="panel__body">
            <form action="" method="post" novalidate>
              <div class="row">
                <div class="col-md-6">
                  <div class="mm-field">
                    <label for="name">Your name</label>
                    <input class="mm-input" type="text" id="name" name="name" placeholder="Priya Patel" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mm-field">
                    <label for="email">Email</label>
                    <input class="mm-input" type="email" id="email" name="email" placeholder="you@example.com" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mm-field">
                    <label for="phone">Phone <span style="color:var(--ink-40);font-weight:500">(optional)</span></label>
                    <input class="mm-input" type="tel" id="phone" name="phone" placeholder="+91 12345 67890">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mm-field">
                    <label for="subject">Subject</label>
                    <input class="mm-input" type="text" id="subject" name="subject" placeholder="Order MX-0003">
                  </div>
                </div>
              </div>

              <div class="mm-field">
                <label for="message">Message</label>
                <textarea class="mm-input" id="message" name="message" rows="5"
                          placeholder="Tell us what you need. Include an order number if your question is about a delivery."></textarea>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn-mm btn-mm-primary btn-mm-lg btn-mm-block">
                  <i class="fas fa-paper-plane" aria-hidden="true"></i><span>Send message</span>
                </button>
              </div>

              <p class="mt-3 mb-0" style="font-size:.76rem;color:var(--ink-40)">
                We use your details only to answer this message. Do not send prescription images here &mdash;
                upload them at checkout instead.
              </p>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="mm-card p-4 p-lg-5 mb-4">
          <p class="eyebrow">Faster than a form</p>
          <h2 class="title-section mt-2 mb-4" style="font-size:clamp(1.3rem,2vw,1.6rem)">Reach a person</h2>

          <ul class="list-unstyled m-0" style="display:grid;gap:1.15rem">
            <li class="d-flex gap-3">
              <span class="stat-card__icon"><i class="fas fa-phone" aria-hidden="true"></i></span>
              <div>
                <p class="mm-label mb-1">Dispensary line</p>
                <a class="num" href="tel:+911234567890" style="font-size:.95rem;color:var(--navy);text-decoration:none">+91 12345 67890</a>
                <p class="mb-0" style="font-size:.78rem;color:var(--ink-40)">Mon&ndash;Sat, 08:00&ndash;22:00 IST</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <span class="stat-card__icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
              <div>
                <p class="mm-label mb-1">Email</p>
                <a href="mailto:support@medimax.com" style="font-size:.92rem;color:var(--navy);text-decoration:none">support@medimax.com</a>
                <p class="mb-0" style="font-size:.78rem;color:var(--ink-40)">Replies within one working day</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <span class="stat-card__icon"><i class="fas fa-location-dot" aria-hidden="true"></i></span>
              <div>
                <p class="mm-label mb-1">Counter</p>
                <p class="mb-0" style="font-size:.92rem">MediMax Chemist, Ring Road,<br>Rajkot, Gujarat 360001</p>
              </div>
            </li>
          </ul>

          <hr class="label-rule">

          <p class="mb-0" style="font-size:.82rem;color:var(--ink-60)">
            <i class="fas fa-triangle-exclamation me-2" style="color:var(--amber-700)" aria-hidden="true"></i>
            For a medical emergency, call your local emergency number instead of writing to us.
          </p>
        </div>

        <div class="mm-card p-4">
          <p class="eyebrow mb-3">Common questions</p>
          <div class="accordion accordion-flush" id="contactFaq">
            <?php
            $faq = [
                ['q' => 'Where is my order?',              'a' => 'Every order shows its live payment and delivery stage on your orders page. Processing means we are packing it; Shipped means it has left the counter.'],
                ['q' => 'Do you need a prescription?',     'a' => 'Only for prescription-only medicines. You will be asked to upload one at checkout, and a pharmacist verifies it before dispatch.'],
                ['q' => 'Can I return an opened pack?',    'a' => 'No. For safety we can only accept unopened, in-date packs within 7 days. Tell us the order number and we arrange pickup.'],
            ];
            foreach ($faq as $i => $f):
                $id = 'faq' . $i;
            ?>
              <div class="accordion-item" style="background:transparent;border-color:var(--line)">
                <h3 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                          data-bs-target="#<?= $id ?>" aria-expanded="false" aria-controls="<?= $id ?>"
                          style="background:transparent;font-size:.9rem;font-weight:700;color:var(--ink);box-shadow:none;padding-left:0;padding-right:0">
                    <?= htmlspecialchars($f['q']) ?>
                  </button>
                </h3>
                <div class="accordion-collapse collapse" id="<?= $id ?>" data-bs-parent="#contactFaq">
                  <div class="accordion-body" style="font-size:.85rem;color:var(--ink-60);padding-left:0;padding-right:0">
                    <?= htmlspecialchars($f['a']) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
