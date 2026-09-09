</main>

<footer class="mm-footer">
  <div class="mm-container">
    <div class="mm-footer__grid">

      <div>
        <a class="mm-footer__brand" href="<?= url() ?>">
          <img src="<?= asset('images/MediMax_Logo.png') ?>" alt="">
          MediMax
        </a>
        <p class="mt-3" style="max-width:34ch">
          A licensed online chemist for everyday medicine, supplements and personal care —
          dispatched from our own shelves, never a marketplace.
        </p>
        <div class="mm-footer__social">
          <a href="#" aria-label="MediMax on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
          <a href="#" aria-label="MediMax on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
          <a href="#" aria-label="MediMax on X"><i class="fab fa-x-twitter" aria-hidden="true"></i></a>
          <a href="#" aria-label="MediMax on LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
        </div>
      </div>

      <nav aria-labelledby="footShop">
        <h2 id="footShop">Shop</h2>
        <ul>
          <li><a href="<?= url('products') ?>">All products</a></li>
          <li><a href="<?= url('products') ?>&amp;cat=Vitamins">Vitamins</a></li>
          <li><a href="<?= url('products') ?>&amp;cat=Supplements">Supplements</a></li>
          <li><a href="<?= url('products') ?>&amp;cat=<?= urlencode('First Aid') ?>">First aid</a></li>
          <li><a href="<?= url('wishlist') ?>">Wishlist</a></li>
        </ul>
      </nav>

      <div>
        <h2>Reach us</h2>
        <ul>
          <li><a href="tel:+911234567890"><i class="fas fa-phone me-2" aria-hidden="true"></i>+91 12345 67890</a></li>
          <li><a href="mailto:support@medimax.com"><i class="fas fa-envelope me-2" aria-hidden="true"></i>support@medimax.com</a></li>
          <li><a href="<?= url('contact') ?>"><i class="fas fa-headset me-2" aria-hidden="true"></i>Contact the pharmacy</a></li>
          <li><a href="<?= url('about') ?>"><i class="fas fa-circle-info me-2" aria-hidden="true"></i>About MediMax</a></li>
        </ul>
        <p class="mt-3 num" style="font-size:.72rem;color:rgba(255,255,255,.5)">Mon&ndash;Sat&nbsp;·&nbsp;08:00&ndash;22:00 IST</p>
      </div>

    </div>

    <div class="mm-footer__bar">
      <span>&copy; <?= date('Y') ?> MediMax &middot; All rights reserved</span>
      <span class="d-flex gap-3">
        <a href="#">Privacy policy</a>
        <a href="#">Terms of use</a>
      </span>
    </div>
  </div>
</footer>

<button class="to-top" type="button" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- MediMax behaviour -->
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
