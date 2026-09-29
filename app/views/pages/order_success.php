<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? 'Order Successful - MediMax.com']);
?>

<div class="page pt-5 pb-5 bg-light min-vh-100 d-flex align-items-center">
  <div class="mm-container w-100">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        
        <div class="mm-card text-center p-5 shadow" style="border-radius: 16px; border: none; border-top: 6px solid #28a745;">
          
          <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow" style="width: 80px; height: 80px;">
              <i class="fas fa-check fs-1"></i>
            </div>
          </div>

          <h2 class="fw-bold" style="color: var(--primary-dark)">Order Placed Successfully!</h2>
          <p class="text-muted mb-4">Thank you for shopping with MediMax. Your order has been received and is being processed.</p>
          
          <div class="bg-light p-3 rounded mb-4 text-start border shadow-sm">
            <div class="row">
              <div class="col-6 mb-2">
                <span class="text-muted d-block" style="font-size: 0.8rem;">Order ID</span>
                <span class="fw-bold"><?= htmlspecialchars($orderId) ?></span>
              </div>
              <div class="col-6 mb-2">
                <span class="text-muted d-block" style="font-size: 0.8rem;">Estimated Delivery</span>
                <span class="fw-bold text-success">2–4 Business Days</span>
              </div>
              <div class="col-6 mb-2">
                <span class="text-muted d-block" style="font-size: 0.8rem;">Order Total</span>
                <span class="fw-bold" style="color: var(--accent-orange, rgb(230, 120, 52))">&#8377;<?= htmlspecialchars($amount) ?></span>
              </div>
              <div class="col-6 mb-2">
                <span class="text-muted d-block" style="font-size: 0.8rem;">Payment Method</span>
                <span class="fw-bold"><?= htmlspecialchars(strtoupper($method)) ?></span>
              </div>
              <div class="col-12 mt-2">
                <span class="text-muted d-block" style="font-size: 0.8rem;">Payment Status</span>
                <?php if ($method === 'COD'): ?>
                  <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Payment on Delivery</span>
                <?php else: ?>
                  <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Order Status Timeline (Static Demo) -->
          <div class="d-flex justify-content-between align-items-center position-relative mb-5 mt-4 mx-3" style="z-index: 1;">
            <div class="position-absolute w-100" style="height: 4px; background-color: #e9ecef; top: 50%; transform: translateY(-50%); z-index: -1;"></div>
            <div class="position-absolute" style="height: 4px; background-color: #28a745; width: 25%; top: 50%; transform: translateY(-50%); z-index: -1;"></div>
            
            <div class="text-center bg-white">
              <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto" style="width: 24px; height: 24px; font-size: 0.7rem;"><i class="fas fa-check"></i></div>
              <span style="font-size: 0.7rem;" class="fw-bold text-success mt-1 d-block">Placed</span>
            </div>
            <div class="text-center bg-white">
              <div class="rounded-circle bg-light border border-2 d-flex justify-content-center align-items-center mx-auto" style="width: 24px; height: 24px; font-size: 0.7rem;"></div>
              <span style="font-size: 0.7rem;" class="text-muted mt-1 d-block">Confirmed</span>
            </div>
            <div class="text-center bg-white">
              <div class="rounded-circle bg-light border border-2 d-flex justify-content-center align-items-center mx-auto" style="width: 24px; height: 24px; font-size: 0.7rem;"></div>
              <span style="font-size: 0.7rem;" class="text-muted mt-1 d-block">Packed</span>
            </div>
            <div class="text-center bg-white">
              <div class="rounded-circle bg-light border border-2 d-flex justify-content-center align-items-center mx-auto" style="width: 24px; height: 24px; font-size: 0.7rem;"></div>
              <span style="font-size: 0.7rem;" class="text-muted mt-1 d-block">Shipped</span>
            </div>
            <div class="text-center bg-white">
              <div class="rounded-circle bg-light border border-2 d-flex justify-content-center align-items-center mx-auto" style="width: 24px; height: 24px; font-size: 0.7rem;"></div>
              <span style="font-size: 0.7rem;" class="text-muted mt-1 d-block">Delivered</span>
            </div>
          </div>

          <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="<?= url('orders') ?>" class="btn btn-outline-primary px-4"><i class="fas fa-box-open me-2"></i>View Order</a>
            <a href="<?= url('products') ?>" class="btn" style="background-color: var(--primary-teal, #70ABAF); color: white;"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php Controller::partial('layouts/footer'); ?>
