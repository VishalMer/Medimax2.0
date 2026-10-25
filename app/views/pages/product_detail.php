<?php
Controller::partial('layouts/header', ['pageTitle' => $pageTitle ?? ($product['name'] . ' - MediMax.com')]);

$id        = (int)($product['id'] ?? 0);
$name      = $product['name'] ?? 'Product Details';
$price     = (float)($product['price'] ?? 0);
$original  = (float)($product['original_price'] ?? 0);
$stock     = (int)($product['stock_quantity'] ?? 0);
$inStock   = $stock > 0;
$image     = $product['image'] ?? 'placeholder.png';
$category  = $product['category'] ?? 'Pharmacy';
$sku       = $product['sku'] ?? ('MM' . str_pad((string)$id, 3, '0', STR_PAD_LEFT));
$desc      = $product['description'] ?? 'No description available.';
$rx        = !empty($product['requires_prescription']);
$isFav     = class_exists('WishlistController') && WishlistController::has($id);

$savings   = ($original > $price) ? ($original - $price) : 0;
$discountPct = ($original > $price) ? round(($savings / $original) * 100) : 0;
?>

<div class="page">
  <div class="mm-container">

    <!-- Breadcrumb Navigation -->
    <nav class="mb-4" aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size: .84rem;">
        <li class="breadcrumb-item"><a href="<?= url() ?>" class="text-decoration-none text-muted"><i class="fas fa-house me-1"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="<?= url('products') ?>" class="text-decoration-none text-muted">Catalogue</a></li>
        <li class="breadcrumb-item"><a href="<?= url('products') ?>&amp;category=<?= urlencode($category) ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($category) ?></a></li>
        <li class="breadcrumb-item active text-truncate text-dark fw-semibold" aria-current="page" style="max-width: 320px;"><?= htmlspecialchars($name) ?></li>
      </ol>
    </nav>

    <!-- Main Product Showcase Grid -->
    <div class="row g-4 mb-5">

      <!-- Left Column: Product Visual Showcase & Trust Badges -->
      <div class="col-lg-5">
        <div class="mm-card p-3 p-md-4 position-relative">
          
          <!-- Badges overlay -->
          <div class="d-flex flex-column gap-2 position-absolute top-0 start-0 m-3" style="z-index: 5;">
            <?php if ($discountPct > 0): ?>
              <span class="badge px-3 py-2 text-white shadow-sm" style="background-color: var(--accent-orange, #E67834); font-size: .85rem; font-weight: 700; border-radius: var(--r-pill, 999px);">
                <?= $discountPct ?>% OFF
              </span>
            <?php endif; ?>
            <?php if ($rx): ?>
              <span class="badge bg-danger px-3 py-2 shadow-sm" style="font-size: .8rem; font-weight: 600; border-radius: var(--r-pill, 999px);">
                <i class="fas fa-file-prescription me-1"></i>Prescription Required
              </span>
            <?php endif; ?>
          </div>

          <?php if (!$inStock): ?>
            <div class="position-absolute top-0 end-0 m-3" style="z-index: 5;">
              <span class="badge bg-secondary px-3 py-2 shadow-sm" style="font-size: .8rem; border-radius: var(--r-pill, 999px);">
                <i class="fas fa-circle-xmark me-1"></i>Out of Stock
              </span>
            </div>
          <?php endif; ?>

          <!-- Product Media Container -->
          <div class="product-detail-media rounded-3 d-flex align-items-center justify-content-center p-4 mb-3"
               style="background: linear-gradient(170deg, #FFFFFF, var(--mist, #F4F6F8)); border: 1px solid var(--line, #E5E7EB); aspect-ratio: 1 / 1; overflow: hidden;">
            <img src="<?= asset('images/' . $image) ?>"
                 alt="<?= htmlspecialchars($name) ?>"
                 id="mainProductImage"
                 class="img-fluid"
                 style="max-height: 88%; max-width: 88%; object-fit: contain; transition: transform .3s ease; <?= !$inStock ? 'filter: grayscale(80%); opacity: 0.85;' : '' ?>"
                 data-fallback="<?= asset('images/assets/MediMax_Logo.png') ?>"
                 loading="eager">
          </div>

          <!-- Trust Badges Strip -->
          <div class="row g-2 pt-2 text-center" style="font-size: .78rem; color: var(--ink-60, #4B5563);">
            <div class="col-6 col-sm-3">
              <div class="p-2 rounded-2" style="background: var(--mist, #F4F6F8); border: 1px solid var(--line, #E5E7EB);">
                <i class="fas fa-shield-halved text-success d-block mb-1" style="font-size: 1.1rem;"></i>
                <span class="fw-semibold">100% Genuine</span>
              </div>
            </div>
            <div class="col-6 col-sm-3">
              <div class="p-2 rounded-2" style="background: var(--mist, #F4F6F8); border: 1px solid var(--line, #E5E7EB);">
                <i class="fas fa-user-doctor text-primary d-block mb-1" style="font-size: 1.1rem;"></i>
                <span class="fw-semibold">Pharmacist Check</span>
              </div>
            </div>
            <div class="col-6 col-sm-3">
              <div class="p-2 rounded-2" style="background: var(--mist, #F4F6F8); border: 1px solid var(--line, #E5E7EB);">
                <i class="fas fa-box text-warning d-block mb-1" style="font-size: 1.1rem;"></i>
                <span class="fw-semibold">Tamper Proof</span>
              </div>
            </div>
            <div class="col-6 col-sm-3">
              <div class="p-2 rounded-2" style="background: var(--mist, #F4F6F8); border: 1px solid var(--line, #E5E7EB);">
                <i class="fas fa-truck-fast text-info d-block mb-1" style="font-size: 1.1rem;"></i>
                <span class="fw-semibold">Fast Dispatch</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Column: Product Details, Options, & Purchase Panel -->
      <div class="col-lg-7">
        <div class="mm-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
          
          <div>
            <!-- Label Header -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <span class="badge bg-light text-primary border px-3 py-1 font-monospace" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .08em;">
                <i class="fas fa-tag me-1"></i><?= htmlspecialchars($category) ?>
              </span>
              <span class="num text-muted" style="font-size: .82rem; font-family: var(--font-mono);">
                SKU: <strong class="text-dark"><?= htmlspecialchars($sku) ?></strong>
              </span>
            </div>

            <!-- Product Title -->
            <h1 class="title-page mb-2" style="font-size: clamp(1.4rem, 2.5vw, 1.95rem); color: var(--primary-dark, #26547C); line-height: 1.3;">
              <?= htmlspecialchars($name) ?>
            </h1>

            <!-- Ratings & Verification Banner -->
            <div class="d-flex align-items-center flex-wrap gap-3 mb-3 pb-3 border-bottom" style="font-size: .85rem;">
              <div class="d-flex align-items-center text-warning gap-1">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-stroke"></i>
                <strong class="text-dark ms-1">4.8</strong>
              </div>
              <span class="text-muted">&bull;</span>
              <span class="text-muted"><i class="fas fa-comments me-1"></i>124 Verified Reviews</span>
              <span class="text-muted">&bull;</span>
              <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="fas fa-check-double me-1"></i>Licensed Chemist Dispensed
              </span>
            </div>

            <!-- Price Display Area -->
            <div class="p-3 rounded-3 mb-4" style="background: rgba(38, 84, 124, 0.04); border: 1px solid rgba(38, 84, 124, 0.1);">
              <div class="d-flex align-items-baseline gap-2 flex-wrap mb-1">
                <span class="eyebrow text-muted me-1 mb-0">MRP Price:</span>
                <span class="num text-dark fw-bold" id="displayProductPrice" style="font-size: 2rem; color: var(--navy, #1B3E5E);">
                  &#8377;<?= number_format($price, 2) ?>
                </span>
                <?php if ($original > $price): ?>
                  <del class="text-muted num" id="displayOriginalPrice" style="font-size: 1.15rem;">
                    &#8377;<?= number_format($original, 2) ?>
                  </del>
                  <span class="badge bg-success px-2 py-1" id="displaySavingsBadge" style="font-size: .8rem;">
                    Save &#8377;<?= number_format($savings, 2) ?> (<?= $discountPct ?>% OFF)
                  </span>
                <?php endif; ?>
              </div>
              <p class="mb-0 text-muted" style="font-size: .78rem;">
                <i class="fas fa-circle-info me-1 text-primary"></i>Inclusive of all applicable taxes &middot; Free delivery on orders above &#8377;499
              </p>
            </div>

            <!-- Product Options: Pack Size / Form Selector -->
            <div class="mb-4">
              <label class="mm-label mb-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                <span><i class="fas fa-boxes-packing me-1 text-primary"></i>Select Packaging Option:</span>
                <span class="text-muted small fw-normal">Select variant below</span>
              </label>
              <div class="row g-2" id="packSizeOptions">
                <div class="col-sm-4">
                  <div class="pack-option-card active p-2 rounded-2 text-center border"
                       data-multiplier="1"
                       data-pack-title="Standard Pack (1 Unit)"
                       style="cursor: pointer; background: var(--paper, #fff); border-color: var(--teal-700, #2C7A80) !important; border-width: 2px !important;">
                    <div class="fw-bold" style="font-size: .86rem;">Single Pack</div>
                    <div class="text-muted small">Standard Dispense</div>
                    <div class="text-success fw-bold small mt-1">&#8377;<?= number_format($price) ?></div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="pack-option-card p-2 rounded-2 text-center border"
                       data-multiplier="2"
                       data-pack-title="Twin Saver Pack (2 Units - Extra 5% Off)"
                       style="cursor: pointer; background: var(--mist, #F4F6F8); border-color: var(--line, #E5E7EB);">
                    <div class="fw-bold" style="font-size: .86rem;">Twin Pack (2x)</div>
                    <div class="badge bg-success-subtle text-success" style="font-size: .68rem;">Save 5% Extra</div>
                    <div class="text-success fw-bold small mt-1">&#8377;<?= number_format($price * 2 * 0.95) ?></div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="pack-option-card p-2 rounded-2 text-center border"
                       data-multiplier="3"
                       data-pack-title="Family Economy Pack (3 Units - Extra 10% Off)"
                       style="cursor: pointer; background: var(--mist, #F4F6F8); border-color: var(--line, #E5E7EB);">
                    <div class="fw-bold" style="font-size: .86rem;">Family Pack (3x)</div>
                    <div class="badge bg-success-subtle text-success" style="font-size: .68rem;">Save 10% Extra</div>
                    <div class="text-success fw-bold small mt-1">&#8377;<?= number_format($price * 3 * 0.90) ?></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stock Status and Delivery Estimator -->
            <div class="row g-3 align-items-center mb-4">
              <div class="col-sm-6">
                <span class="mm-label mb-1">Availability</span>
                <?php if ($inStock): ?>
                  <div class="text-success fw-semibold d-flex align-items-center gap-2" style="font-size: .92rem;">
                    <i class="fas fa-circle-check"></i>
                    <span>In Stock <span class="text-muted fw-normal">(<?= $stock ?> units available)</span></span>
                  </div>
                <?php else: ?>
                  <div class="text-danger fw-semibold d-flex align-items-center gap-2" style="font-size: .92rem;">
                    <i class="fas fa-circle-xmark"></i>
                    <span>Currently Out of Stock</span>
                  </div>
                <?php endif; ?>
              </div>
              <div class="col-sm-6">
                <span class="mm-label mb-1">Fast Delivery to Pincode</span>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" id="deliveryPincode" placeholder="e.g. 380015" maxlength="6" value="380015">
                  <button class="btn btn-outline-secondary" type="button" id="checkPincodeBtn">Check</button>
                </div>
                <div class="text-muted mt-1" id="pincodeMessage" style="font-size: .74rem;">
                  <i class="fas fa-bolt text-warning me-1"></i>Express dispatch available. Estimated delivery in 24–48 hrs.
                </div>
              </div>
            </div>

            <!-- Doctor Prescription Notice if required -->
            <?php if ($rx): ?>
              <div class="alert alert-warning d-flex align-items-start gap-2 py-2 px-3 mb-4 rounded-3 shadow-sm" style="font-size: .84rem;">
                <i class="fas fa-file-prescription text-danger fs-5 mt-1"></i>
                <div>
                  <strong>Prescription Medicine:</strong> As per drug regulations, a valid prescription from a registered medical practitioner is required. You can upload it during checkout or show it to our delivery partner.
                </div>
              </div>
            <?php endif; ?>

          </div>

          <!-- Quantity Stepper & Cart / Buy Action Buttons -->
          <div class="border-top pt-4 mt-2">
            
            <?php if ($inStock): ?>
              <div class="row g-3 align-items-center">
                <!-- Quantity Stepper -->
                <div class="col-sm-4 col-md-3">
                  <label class="mm-label mb-1">Quantity</label>
                  <div class="input-group">
                    <button class="btn btn-outline-secondary px-2" type="button" id="btnQtyMinus" style="border-radius: var(--r-md) 0 0 var(--r-md);">
                      <i class="fas fa-minus" style="font-size: .75rem;"></i>
                    </button>
                    <input type="number" id="productDetailQty" class="form-control text-center fw-bold num"
                           value="1" min="1" max="<?= max(1, $stock) ?>" readonly
                           style="background: #fff; width: 45px;">
                    <button class="btn btn-outline-secondary px-2" type="button" id="btnQtyPlus" style="border-radius: 0 var(--r-md) var(--r-md) 0;">
                      <i class="fas fa-plus" style="font-size: .75rem;"></i>
                    </button>
                  </div>
                </div>

                <!-- Add to Cart & Buy Now Buttons -->
                <div class="col-sm-8 col-md-9">
                  <label class="mm-label mb-1 d-none d-sm-block">&nbsp;</label>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    
                    <button type="button"
                            class="btn-mm btn-mm-primary btn-mm-lg flex-grow-1"
                            id="detailAddToCartBtn"
                            data-act="cart"
                            data-id="<?= (int)$id ?>"
                            data-name="<?= htmlspecialchars($name) ?>"
                            data-stock="<?= $stock ?>"
                            data-qty="1">
                      <i class="fas fa-cart-plus me-1" aria-hidden="true"></i>
                      <span>Add to Cart</span>
                    </button>

                    <button type="button"
                            class="btn-mm btn-mm-accent btn-mm-lg flex-grow-1"
                            id="detailBuyNowBtn"
                            data-act="buy-now"
                            data-id="<?= (int)$id ?>"
                            data-name="<?= htmlspecialchars($name) ?>"
                            data-qty="1"
                            style="background-color: var(--navy, #1B3E5E); color: #fff;">
                      <i class="fas fa-bolt me-1" aria-hidden="true"></i>
                      <span>Buy Now</span>
                    </button>

                    <button type="button"
                            class="icon-btn <?= $isFav ? 'is-fav' : '' ?>"
                            id="detailWishlistBtn"
                            data-act="fav"
                            data-id="<?= (int)$id ?>"
                            data-name="<?= htmlspecialchars($name) ?>"
                            aria-pressed="<?= $isFav ? 'true' : 'false' ?>"
                            title="<?= $isFav ? 'Remove from wishlist' : 'Save to wishlist' ?>"
                            style="width: 48px; height: 48px; font-size: 1.15rem; flex-shrink: 0;">
                      <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart" aria-hidden="true"></i>
                    </button>

                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="d-flex gap-3 align-items-center">
                <button type="button" class="btn-mm btn-mm-primary btn-mm-lg w-100" disabled style="opacity: .6; cursor: not-allowed; background: var(--mist, #F4F6F8); color: var(--ink-40, #666); border: 1px solid var(--line, #ccc);">
                  <i class="fas fa-ban me-1"></i><span>Currently Out of Stock</span>
                </button>
                <button type="button"
                        class="icon-btn <?= $isFav ? 'is-fav' : '' ?>"
                        data-act="fav"
                        data-id="<?= (int)$id ?>"
                        data-name="<?= htmlspecialchars($name) ?>"
                        aria-pressed="<?= $isFav ? 'true' : 'false' ?>"
                        title="<?= $isFav ? 'Remove from wishlist' : 'Save to wishlist' ?>"
                        style="width: 48px; height: 48px; font-size: 1.15rem; flex-shrink: 0;">
                  <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart" aria-hidden="true"></i>
                </button>
              </div>
            <?php endif; ?>

          </div>

        </div>
      </div>

    </div>

    <!-- Product Information Tabs -->
    <div class="mm-card p-4 p-md-5 mb-5">
      <ul class="nav nav-tabs border-bottom mb-4" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-bold text-dark px-4 py-2" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
            <i class="fas fa-circle-info me-2 text-primary"></i>Description &amp; Uses
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-dark px-4 py-2" id="dosage-tab" data-bs-toggle="tab" data-bs-target="#dosage" type="button" role="tab" aria-controls="dosage" aria-selected="false">
            <i class="fas fa-pills me-2 text-primary"></i>Dosage &amp; Directions
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-dark px-4 py-2" id="composition-tab" data-bs-toggle="tab" data-bs-target="#composition" type="button" role="tab" aria-controls="composition" aria-selected="false">
            <i class="fas fa-flask me-2 text-primary"></i>Active Ingredients
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-dark px-4 py-2" id="safety-tab" data-bs-toggle="tab" data-bs-target="#safety" type="button" role="tab" aria-controls="safety" aria-selected="false">
            <i class="fas fa-triangle-exclamation me-2 text-primary"></i>Safety &amp; Warnings
          </button>
        </li>
      </ul>

      <div class="tab-content" id="productTabsContent">
        
        <!-- Tab 1: Description -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
          <h3 class="h6 fw-bold text-dark mb-2">Product Overview</h3>
          <p class="text-muted leading-relaxed" style="font-size: .94rem;">
            <?= nl2br(htmlspecialchars($desc)) ?>
          </p>
          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <div class="p-3 rounded-3" style="background: var(--mist, #F4F6F8);">
                <strong class="text-dark d-block mb-1"><i class="fas fa-check-circle text-success me-2"></i>Key Benefits</strong>
                <ul class="mb-0 text-muted ps-3" style="font-size: .88rem;">
                  <li>Formulated with high-potency ingredients for rapid absorption.</li>
                  <li>Manufactured in WHO-GMP certified pharmaceutical facilities.</li>
                  <li>Free from harmful contaminants and heavy metals.</li>
                  <li>Tested by accredited analytical laboratories for purity.</li>
                </ul>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-3" style="background: var(--mist, #F4F6F8);">
                <strong class="text-dark d-block mb-1"><i class="fas fa-box-open text-primary me-2"></i>Storage Guidelines</strong>
                <ul class="mb-0 text-muted ps-3" style="font-size: .88rem;">
                  <li>Store in a cool, dry place below 25&deg;C.</li>
                  <li>Protect from direct sunlight, moisture, and excessive heat.</li>
                  <li>Keep out of reach of children and domestic pets.</li>
                  <li>Do not use if blister seal or protective cap is compromised.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 2: Dosage -->
        <div class="tab-pane fade" id="dosage" role="tabpanel" aria-labelledby="dosage-tab">
          <h3 class="h6 fw-bold text-dark mb-2">Recommended Usage &amp; Instructions</h3>
          <p class="text-muted leading-relaxed" style="font-size: .94rem;">
            Take this medication as directed by your physician or according to the packaged dispensing leaflet. For oral solid forms, swallow whole with a glass of plain water. Do not crush, break, or chew unless expressly directed.
          </p>
          <div class="p-3 rounded-3 border-start border-4 border-primary" style="background: var(--mist, #F4F6F8); font-size: .88rem;">
            <strong class="text-dark d-block mb-1"><i class="fas fa-lightbulb text-warning me-1"></i>Pharmacist Advice</strong>
            Always maintain consistent timing each day to achieve maximum therapeutic effect. If a dose is missed, take it as soon as remembered unless it is almost time for the next scheduled dose. Never take a double dose to compensate.
          </div>
        </div>

        <!-- Tab 3: Composition -->
        <div class="tab-pane fade" id="composition" role="tabpanel" aria-labelledby="composition-tab">
          <h3 class="h6 fw-bold text-dark mb-2">Salt Composition &amp; Molecular Strength</h3>
          <div class="table-responsive">
            <table class="table table-bordered table-striped" style="font-size: .88rem;">
              <thead class="table-light">
                <tr>
                  <th>Component Name</th>
                  <th>Classification</th>
                  <th>Standard Strength</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="fw-semibold text-dark"><?= htmlspecialchars($name) ?> Active Complex</td>
                  <td>Active Pharmaceutical Ingredient (API)</td>
                  <td>Pharmacopoeial Grade (IP / USP)</td>
                </tr>
                <tr>
                  <td>Pharmaceutical Excipients (q.s.)</td>
                  <td>Stabilizers &amp; Bioavailability Enhancers</td>
                  <td>Standard Batch Formulation</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 4: Safety & Warnings -->
        <div class="tab-pane fade" id="safety" role="tabpanel" aria-labelledby="safety-tab">
          <h3 class="h6 fw-bold text-dark mb-3">Safety Warnings &amp; Precautions</h3>
          <div class="row g-3" style="font-size: .88rem;">
            <div class="col-md-6">
              <div class="border p-3 rounded-2">
                <strong class="text-danger d-block mb-1"><i class="fas fa-wine-glass text-danger me-1"></i>Alcohol Consumption</strong>
                <span class="text-muted">Consult your doctor before consuming alcohol with this medicine, as it may enhance sedation or affect metabolism.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border p-3 rounded-2">
                <strong class="text-warning d-block mb-1"><i class="fas fa-person-pregnant text-warning me-1"></i>Pregnancy &amp; Lactation</strong>
                <span class="text-muted">Safe use during pregnancy or breastfeeding should be determined exclusively by your consulting gynecologist.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border p-3 rounded-2">
                <strong class="text-info d-block mb-1"><i class="fas fa-car text-info me-1"></i>Driving &amp; Machinery</strong>
                <span class="text-muted">If you experience lightheadedness, blurred vision, or drowsiness after taking this product, refrain from driving.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border p-3 rounded-2">
                <strong class="text-primary d-block mb-1"><i class="fas fa-heart-pulse text-primary me-1"></i>Kidney &amp; Liver</strong>
                <span class="text-muted">Patients with chronic renal or hepatic impairment should have dosage adjusted under medical supervision.</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($relatedProducts)): ?>
      <div class="related-products-section mt-5 pt-3">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
          <div>
            <p class="eyebrow mb-1">Recommendations</p>
            <h2 class="title-page mb-0" style="font-size: clamp(1.3rem, 2.2vw, 1.75rem);">Related Products &amp; Alternatives</h2>
            <p class="text-muted mb-0" style="font-size: .84rem;">Frequently explored together in <?= htmlspecialchars($category) ?></p>
          </div>
          <a href="<?= url('products') ?>&amp;category=<?= urlencode($category) ?>" class="btn-mm btn-mm-ghost btn-mm-sm">
            <span>Explore all in <?= htmlspecialchars($category) ?></span><i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="product-grid">
          <?php foreach ($relatedProducts as $idx => $relProduct): ?>
            <?php Controller::component('product_card', ['product' => $relProduct, 'index' => $idx]); ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var basePrice = <?= (float)$price ?>;
  var baseOriginal = <?= (float)$original ?>;
  var maxStock = <?= max(1, $stock) ?>;
  var qtyInput = document.getElementById('productDetailQty');
  var btnMinus = document.getElementById('btnQtyMinus');
  var btnPlus = document.getElementById('btnQtyPlus');
  var addToCartBtn = document.getElementById('detailAddToCartBtn');
  var buyNowBtn = document.getElementById('detailBuyNowBtn');
  var priceDisplay = document.getElementById('displayProductPrice');
  var origDisplay = document.getElementById('displayOriginalPrice');
  var savingsDisplay = document.getElementById('displaySavingsBadge');

  var currentMultiplier = 1;

  function updatePrices() {
    var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
    var effectiveUnits = qty * currentMultiplier;

    // Apply pack discounts if multiplier > 1
    var discountFactor = 1.0;
    if (currentMultiplier === 2) discountFactor = 0.95;
    if (currentMultiplier === 3) discountFactor = 0.90;

    var totalPrice = (basePrice * effectiveUnits * discountFactor);
    var totalOriginal = (baseOriginal > 0 ? baseOriginal : basePrice) * effectiveUnits;
    var totalSavings = totalOriginal - totalPrice;
    var totalPct = totalOriginal > 0 ? Math.round((totalSavings / totalOriginal) * 100) : 0;

    if (priceDisplay) {
      priceDisplay.innerHTML = '&#8377;' + totalPrice.toFixed(2);
    }
    if (origDisplay) {
      origDisplay.innerHTML = '&#8377;' + totalOriginal.toFixed(2);
    }
    if (savingsDisplay) {
      if (totalSavings > 0) {
        savingsDisplay.innerHTML = 'Save &#8377;' + totalSavings.toFixed(2) + ' (' + totalPct + '% OFF)';
        savingsDisplay.style.display = 'inline-block';
      } else {
        savingsDisplay.style.display = 'none';
      }
    }

    if (addToCartBtn) {
      addToCartBtn.setAttribute('data-qty', effectiveUnits);
    }
    if (buyNowBtn) {
      buyNowBtn.setAttribute('data-qty', effectiveUnits);
    }
  }

  // Stepper handlers
  if (btnMinus && qtyInput) {
    btnMinus.addEventListener('click', function () {
      var val = parseInt(qtyInput.value, 10) || 1;
      if (val > 1) {
        qtyInput.value = val - 1;
        updatePrices();
      }
    });
  }

  if (btnPlus && qtyInput) {
    btnPlus.addEventListener('click', function () {
      var val = parseInt(qtyInput.value, 10) || 1;
      if (val < maxStock) {
        qtyInput.value = val + 1;
        updatePrices();
      } else if (window.MediMax && window.MediMax.toast) {
        window.MediMax.toast('Maximum available stock is ' + maxStock + ' units', 'fa-circle-exclamation');
      }
    });
  }

  // Pack size cards
  var packCards = document.querySelectorAll('.pack-option-card');
  packCards.forEach(function (card) {
    card.addEventListener('click', function () {
      packCards.forEach(function (c) {
        c.classList.remove('active');
        c.style.background = 'var(--mist, #F4F6F8)';
        c.style.borderColor = 'var(--line, #E5E7EB)';
        c.style.borderWidth = '1px';
      });
      card.classList.add('active');
      card.style.background = 'var(--paper, #fff)';
      card.style.borderColor = 'var(--teal-700, #2C7A80)';
      card.style.borderWidth = '2px';

      currentMultiplier = parseInt(card.getAttribute('data-multiplier'), 10) || 1;
      updatePrices();

      if (window.MediMax && window.MediMax.toast) {
        window.MediMax.toast('Selected ' + card.getAttribute('data-pack-title'), 'fa-boxes-packing');
      }
    });
  });

  // Pincode checker simulation
  var checkBtn = document.getElementById('checkPincodeBtn');
  var pinInput = document.getElementById('deliveryPincode');
  var pinMsg = document.getElementById('pincodeMessage');
  if (checkBtn && pinInput && pinMsg) {
    checkBtn.addEventListener('click', function () {
      var code = pinInput.value.trim();
      if (/^[1-9][0-9]{5}$/.test(code)) {
        pinMsg.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Delivery available to ' + code + '! Delivery within 24 to 48 hours.</span>';
      } else {
        pinMsg.innerHTML = '<span class="text-danger fw-semibold"><i class="fas fa-circle-exclamation me-1"></i>Please enter a valid 6-digit Indian pincode.</span>';
      }
    });
  }
});
</script>

<?php Controller::partial('layouts/footer'); ?>
