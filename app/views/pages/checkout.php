<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Checkout - MediMax.com']);

$items = $cartItems ?? [];
$count = count($items);
$units = 0;
foreach ($items as $it) { $units += (int) ($it['quantity'] ?? 1); }
$subtotal = (float)($subtotal ?? 0.0);
$deliveryFee = (float)($deliveryFee ?? 0.0);
$discountAmount = (float)($discountAmount ?? 0.0);
$grandTotal = (float)($grandTotal ?? 0.0);
$appliedCoupon = $appliedCoupon ?? ($_SESSION['applied_coupon'] ?? null);
?>

<div class="page bg-light pt-4 pb-5">
  <div class="mm-container">
    
    <div class="page-head mb-4">
      <h1 class="title-page">Secure Checkout</h1>
      <p class="lede mb-0">Complete your delivery and payment details below.</p>
    </div>

    <?php if (!empty($items)): ?>

      <div class="row g-4">
        <!-- Left Column: Forms -->
        <div class="col-lg-7 col-xl-8">
          
          <!-- Delivery Information -->
          <div class="mm-card mb-4 shadow-sm" style="border: none; border-radius: 12px; overflow: hidden;">
            <div class="p-3 text-white d-flex align-items-center gap-2" style="background-color: var(--primary-dark, #26547C);">
              <i class="fas fa-truck-fast"></i>
              <h4 class="mb-0 fs-5 fw-bold">1. Delivery Information</h4>
            </div>
            
            <div class="p-4">
              <!-- Saved Addresses (Mock) -->
              <div class="mb-4">
                <p class="eyebrow mb-2">Saved Addresses</p>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-outline-secondary text-start p-2 saved-addr-btn" 
                          data-name="Vishal Patel" data-mobile="9876543210" data-email="vishal@example.com" 
                          data-address="123 Example Street" data-apt="Apt 4B" data-area="Kuvadva Road" 
                          data-city="Rajkot" data-state="Gujarat" data-pincode="360001" style="font-size: 0.85rem; width: 220px;">
                    <div class="fw-bold text-dark"><i class="fas fa-home me-1"></i> Home</div>
                    Vishal Patel<br>Rajkot, Gujarat - 360001
                  </button>
                  <button type="button" class="btn btn-outline-secondary text-start p-2 saved-addr-btn" 
                          data-name="Demo User" data-mobile="9998887776" data-email="demo@example.com" 
                          data-address="456 Office Tower" data-apt="Floor 10" data-area="Business Hub" 
                          data-city="Ahmedabad" data-state="Gujarat" data-pincode="380001" style="font-size: 0.85rem; width: 220px;">
                    <div class="fw-bold text-dark"><i class="fas fa-building me-1"></i> Work</div>
                    Demo User<br>Ahmedabad, Gujarat - 380001
                  </button>
                  <button type="button" class="btn btn-outline-primary p-2 d-flex align-items-center justify-content-center" id="clearAddrBtn" style="width: 220px; font-size: 0.85rem; border-style: dashed;">
                    <i class="fas fa-plus me-1"></i> Add New Address
                  </button>
                </div>
              </div>

              <!-- Address Form -->
              <form id="checkoutForm" action="<?= url('checkout/place-order') ?>" method="POST">
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="UPI">
                <div class="row g-3">
                  <div class="col-md-6 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_name" id="delName" required value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>">
                  </div>
                  <div class="col-md-6 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Mobile Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="shipping_mobile" id="delMobile" required pattern="[0-9]{10}" placeholder="10 digit number">
                  </div>
                  
                  <div class="col-md-12 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="shipping_email" id="delEmail" required value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                  </div>

                  <div class="col-12 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Flat, House no., Building, Company, Apartment <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_address_line1" id="delAddress" required>
                  </div>

                  <div class="col-12 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Area, Street, Sector, Village</label>
                    <input type="text" class="form-control" name="shipping_address_line2" id="delArea">
                  </div>

                  <div class="col-md-4 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Town/City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_city" id="delCity" required>
                  </div>

                  <div class="col-md-4 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">State <span class="text-danger">*</span></label>
                    <select class="form-select" name="shipping_state" id="delState" required>
                      <option value="">Select State</option>
                      <option value="Gujarat" selected>Gujarat</option>
                      <option value="Maharashtra">Maharashtra</option>
                      <option value="Delhi">Delhi</option>
                      <option value="Karnataka">Karnataka</option>
                      <option value="Rajasthan">Rajasthan</option>
                    </select>
                  </div>

                  <div class="col-md-4 mm-field mb-3">
                    <label class="form-label fw-bold" style="font-size: 0.85rem;">Pincode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_pincode" id="delPincode" required pattern="[0-9]{6}" placeholder="6 digits">
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="mm-card mb-4 shadow-sm" style="border: none; border-radius: 12px; overflow: hidden;">
            <div class="p-3 text-white d-flex align-items-center gap-2" style="background-color: var(--primary-teal, #70ABAF);">
              <i class="fas fa-credit-card"></i>
              <h4 class="mb-0 fs-5 fw-bold">2. Payment Method</h4>
            </div>
            
            <div class="p-0">
              <div class="accordion" id="paymentAccordion">
                
                <!-- UPI -->
                <div class="accordion-item border-0 border-bottom">
                  <h2 class="accordion-header">
                    <button class="accordion-button bg-transparent shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payUPI" aria-expanded="false" onclick="selectPayment('UPI')">
                      <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input mt-0" type="radio" name="paymentMethodRadio" id="radioUPI" value="UPI">
                        <i class="fas fa-qrcode fs-4" style="color: var(--primary-dark)"></i>
                        <div>
                          <span class="fw-bold d-block">UPI Options</span>
                          <small class="text-muted">Pay via Google Pay, PhonePe, Paytm, etc.</small>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="payUPI" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                    <div class="accordion-body bg-light">
                      <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.85rem;">Enter your UPI ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="upiId" placeholder="username@upi">
                        <div class="form-text">A payment request will be sent to your UPI app.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Credit/Debit Card -->
                <div class="accordion-item border-0 border-bottom">
                  <h2 class="accordion-header">
                    <button class="accordion-button bg-transparent shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payCard" aria-expanded="false" onclick="selectPayment('CARD')">
                      <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input mt-0" type="radio" name="paymentMethodRadio" id="radioCard" value="CARD">
                        <i class="fas fa-credit-card fs-4" style="color: var(--primary-dark)"></i>
                        <div>
                          <span class="fw-bold d-block">Credit / Debit Card</span>
                          <small class="text-muted">Visa, Mastercard, RuPay, Maestro</small>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="payCard" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                    <div class="accordion-body bg-light">
                      <div class="row g-3">
                        <div class="col-12">
                          <label class="form-label fw-bold" style="font-size: 0.85rem;">Card Number <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-bold" style="font-size: 0.85rem;">Valid Through <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-bold" style="font-size: 0.85rem;">CVV <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="cardCvv" placeholder="123" maxlength="4">
                            <button class="btn btn-outline-secondary" type="button" id="toggleCvv"><i class="fas fa-eye"></i></button>
                          </div>
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-bold" style="font-size: 0.85rem;">Name on Card <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="cardName" placeholder="Cardholder Name">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Net Banking -->
                <div class="accordion-item border-0 border-bottom">
                  <h2 class="accordion-header">
                    <button class="accordion-button bg-transparent shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payNetbank" aria-expanded="false" onclick="selectPayment('NETBANK')">
                      <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input mt-0" type="radio" name="paymentMethodRadio" id="radioNetbank" value="NETBANK">
                        <i class="fas fa-building-columns fs-4" style="color: var(--primary-dark)"></i>
                        <div>
                          <span class="fw-bold d-block">Net Banking</span>
                          <small class="text-muted">Select from all major banks</small>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="payNetbank" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                    <div class="accordion-body bg-light">
                      <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.85rem;">Select Bank <span class="text-danger">*</span></label>
                        <select class="form-select" id="bankSelect">
                          <option value="">Choose your bank...</option>
                          <option value="SBI">State Bank of India</option>
                          <option value="HDFC">HDFC Bank</option>
                          <option value="ICICI">ICICI Bank</option>
                          <option value="AXIS">Axis Bank</option>
                          <option value="KOTAK">Kotak Mahindra Bank</option>
                          <option value="BOB">Bank of Baroda</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Cash on Delivery -->
                <div class="accordion-item border-0">
                  <h2 class="accordion-header">
                    <button class="accordion-button bg-transparent shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payCOD" aria-expanded="false" onclick="selectPayment('COD')">
                      <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input mt-0" type="radio" name="paymentMethodRadio" id="radioCOD" value="COD">
                        <i class="fas fa-money-bill-wave fs-4" style="color: var(--primary-dark)"></i>
                        <div>
                          <span class="fw-bold d-block">Cash on Delivery</span>
                          <small class="text-muted">Pay when your order arrives.</small>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="payCOD" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                    <div class="accordion-body bg-light">
                      <p class="mb-0 text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Cash on Delivery is available for this order. No extra charges apply.</p>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          
          <div class="d-flex align-items-center justify-content-center gap-2 mb-4 text-muted" style="font-size: 0.8rem;">
            <i class="fas fa-lock"></i> 100% Demo Payment Environment. Secure Checkout.
          </div>
          
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-5 col-xl-4">
          <div class="mm-card sticky-top shadow-sm" style="top: 100px; border-radius: 12px; overflow: hidden; border: none;">
            <div class="p-3 text-white" style="background-color: var(--primary-dark, #26547C);">
              <h4 class="mb-0 fs-5 fw-bold">Order Summary</h4>
            </div>
            
            <div class="p-3 bg-light border-bottom" style="max-height: 250px; overflow-y: auto;">
              <?php foreach ($items as $item): ?>
                <?php
                  $iName  = $item['name'] ?? 'Product';
                  $iPrice = (float) ($item['price'] ?? 0);
                  $iQty   = (int) ($item['quantity'] ?? 1);
                  $iImg   = $item['image'] ?? 'placeholder.png';
                ?>
                <div class="d-flex gap-2 mb-2 pb-2 border-bottom">
                  <img src="<?= asset('images/' . $iImg) ?>" alt="<?= htmlspecialchars($iName) ?>" 
                       style="width: 50px; height: 50px; object-fit: contain;"
                       data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>">
                  <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.85rem; color: var(--primary-dark)"><?= htmlspecialchars($iName) ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="text-muted" style="font-size: 0.8rem;">Qty: <?= $iQty ?></span>
                      <span class="fw-bold" style="font-size: 0.85rem;">&#8377;<?= number_format($iPrice * $iQty, 2) ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="p-3">
              <div class="d-flex justify-content-between mb-2" style="font-size: 0.9rem;">
                <span class="text-muted">Subtotal (MRP)</span>
                <span class="fw-bold" id="chkSubtotal">&#8377;<?= number_format($subtotal, 2) ?></span>
              </div>
              <?php if ($discountAmount > 0): ?>
              <div class="d-flex justify-content-between mb-2 text-success" style="font-size: 0.9rem;">
                <span><i class="fas fa-ticket me-1"></i>Coupon Discount (<?= htmlspecialchars($appliedCoupon['code'] ?? '') ?>)</span>
                <span class="fw-bold" id="chkDiscount">-&#8377;<?= number_format($discountAmount, 2) ?></span>
              </div>
              <?php endif; ?>
              <div class="d-flex justify-content-between mb-3" style="font-size: 0.9rem;">
                <span class="text-muted">Delivery</span>
                <span class="fw-bold" id="chkDelivery">
                  <?php if ($deliveryFee === 0.00): ?>
                    &#8377;0.00 <span class="badge bg-success" style="font-size:0.65rem">FREE</span>
                  <?php else: ?>
                    &#8377;<?= number_format($deliveryFee, 2) ?>
                  <?php endif; ?>
                </span>
              </div>

              <hr>

              <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="fs-5 fw-bold" style="color: var(--primary-dark)">Total Payable</span>
                <span class="fs-4 fw-bold" id="chkTotal" style="color: var(--accent-orange, rgb(230, 120, 52))">&#8377;<?= number_format($grandTotal, 2) ?></span>
              </div>

              <button type="submit" form="checkoutForm" id="placeOrderBtn" class="btn btn-mm-amber btn-lg w-100 fw-bold d-flex justify-content-center align-items-center shadow">
                <i class="fas fa-lock me-2"></i><span id="placeOrderBtnText">Place Order &amp; Pay &#8377;<?= number_format($grandTotal, 2) ?></span>
              </button>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>

      <?php Controller::component('empty_state', [
          'title'      => 'Your cart is empty',
          'message'    => 'You need to add products to your cart before checking out.',
          'buttonText' => 'Continue Shopping',
          'buttonLink' => url('products'),
          'icon'       => 'fa-cart-shopping',
      ]); ?>

    <?php endif; ?>

  </div>
</div>

<script>
window.selectPayment = function (method) {
  var hidden = document.getElementById('selectedPaymentMethod');
  if (hidden) hidden.value = method;
  var radio = document.getElementById('radio' + method);
  if (radio) radio.checked = true;
};

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.saved-addr-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.saved-addr-btn').forEach(function(b){ b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-secondary'); });
      btn.classList.add('active', 'btn-primary');
      btn.classList.remove('btn-outline-secondary');
      if (document.getElementById('delName')) document.getElementById('delName').value = btn.dataset.name || '';
      if (document.getElementById('delMobile')) document.getElementById('delMobile').value = btn.dataset.mobile || '';
      if (document.getElementById('delEmail')) document.getElementById('delEmail').value = btn.dataset.email || '';
      if (document.getElementById('delAddress')) document.getElementById('delAddress').value = (btn.dataset.address || '') + (btn.dataset.apt ? ', ' + btn.dataset.apt : '');
      if (document.getElementById('delArea')) document.getElementById('delArea').value = btn.dataset.area || '';
      if (document.getElementById('delCity')) document.getElementById('delCity').value = btn.dataset.city || '';
      if (document.getElementById('delState')) document.getElementById('delState').value = btn.dataset.state || '';
      if (document.getElementById('delPincode')) document.getElementById('delPincode').value = btn.dataset.pincode || '';
    });
  });

  var clearBtn = document.getElementById('clearAddrBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      document.querySelectorAll('.saved-addr-btn').forEach(function(b){ b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-secondary'); });
      var form = document.getElementById('checkoutForm');
      if (form) form.reset();
    });
  }
});
</script>

<?php Controller::partial('layouts/footer'); ?>
