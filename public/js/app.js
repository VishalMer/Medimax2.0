(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.addEventListener('DOMContentLoaded', function () {

    /* --- Navbar: condense once the page scrolls & back-to-top animation --- */
    var nav = document.querySelector('.mm-nav');
    var toTop = document.querySelector('.to-top');

    function onScroll() {
      var y = window.scrollY;
      if (nav) nav.classList.toggle('is-stuck', y > 12);
      if (toTop) toTop.classList.toggle('is-visible', y > 520);
    }
    if (nav || toTop) {
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }
    if (toTop) {
      toTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      });
    }

    /* --- Profile menu toggle ------------------------------------------- */
    var profileBtn = document.getElementById('profileOptionsBtn');
    var profileMenu = document.getElementById('profileOptions');

    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isHidden = profileMenu.classList.contains('d-none');
        profileMenu.classList.toggle('d-none');
        profileBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
      });

      document.addEventListener('click', function (e) {
        if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
          profileMenu.classList.add('d-none');
          profileBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }

    /* --- Password visibility toggler ---------------------------------- */
    document.querySelectorAll('.pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.getAttribute('data-pw-toggle') || 'password';
        var input = document.getElementById(targetId);
        if (!input) return;
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
          }
        } else {
          input.type = 'password';
          if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
          }
        }
      });
    });

    /* --- Scroll reveal ------------------------------------------------- */
    var revealables = document.querySelectorAll('.reveal');
    if (revealables.length) {
      if (reduceMotion || !('IntersectionObserver' in window)) {
        revealables.forEach(function (el) { el.classList.add('is-in'); });
      } else {
        var io = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-in');
              io.unobserve(entry.target);
            }
          });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
        revealables.forEach(function (el) { io.observe(el); });
      }
    }

    /* --- Shared jQuery form validation ------------------------------- */
    if (window.jQuery) {
      (function ($) {
        function validateField(input) {
          var field = $(input);
          var value = field.val() ? String(field.val()).trim() : '';
          var errorSpan = $('#' + (field.attr('id') || field.attr('name')) + 'Error');
          if (!errorSpan.length) errorSpan = field.siblings('.validation-error').first();
          var validationType = field.data('validation') || '';
          var minLength = field.data('min') || 0;
          var maxLength = field.data('max') || 9999;
          var filesize = field.data('filesize') || 0;
          var fileTypes = String(field.data('filetypes') || '').split(',').filter(Boolean);
          var errorMessage = '';

          if (!validationType) return true;

          if (validationType.includes('required') && value === '') {
            errorMessage = 'This field is required.';
          }
          if (!errorMessage && value !== '' && validationType.includes('email') &&
              !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)) {
            errorMessage = 'Please enter a valid email address.';
          }
          if (!errorMessage && value !== '' && validationType.includes('strongPassword') &&
              !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,25}$/.test(value)) {
            errorMessage = 'Password must be at least 8 characters, include uppercase, lowercase, number, and special character.';
          }
          if (!errorMessage && validationType.includes('confirmPassword')) {
            var password = $('#' + field.data('password-id')).val().trim();
            if (value !== password) errorMessage = 'Passwords do not match.';
          }
          if (!errorMessage && validationType.includes('terms') && !field.is(':checked')) {
            errorMessage = 'You must agree to the Terms & Conditions.';
          }
          if (!errorMessage && value !== '' && validationType.includes('alpha') && !/^[A-Za-z\s]+$/.test(value)) {
            errorMessage = 'Only letters are allowed.';
          }
          if (!errorMessage && value !== '' && validationType.includes('numeric') && !/^[0-9]+$/.test(value)) {
            errorMessage = 'Only numbers are allowed.';
          }
          if (!errorMessage && value !== '' && validationType.includes('min') && value.length < minLength) {
            errorMessage = 'Must be at least ' + minLength + ' characters.';
          }
          if (!errorMessage && value !== '' && validationType.includes('max') && value.length > maxLength) {
            errorMessage = 'Must be less than ' + maxLength + ' characters.';
          }
          if (!errorMessage && validationType.includes('file')) {
            if (input.files && input.files.length > 0) {
              var file = input.files[0];
              var extension = file.name.split('.').pop().toLowerCase();
              if (fileTypes.length && fileTypes.indexOf(extension) === -1) {
                errorMessage = 'Only JPG, JPEG, or PNG files are allowed.';
              } else if (validationType.includes('filesize') && file.size / 1024 > filesize) {
                errorMessage = 'File size must be less than ' + filesize + ' KB.';
              }
            } else if (validationType.includes('required')) {
              errorMessage = 'Please upload a file.';
            }
          }
          if (!errorMessage && field.is('select') && validationType.includes('required') &&
              (value === '' || field.find('option:selected').index() === 0)) {
            errorMessage = 'Please select an option.';
          }

          if (errorMessage) {
            errorSpan.text(errorMessage).show();
            field.addClass('is-invalid').removeClass('is-valid').attr('aria-invalid', 'true');
          } else {
            errorSpan.text('').hide();
            field.removeClass('is-invalid').addClass('is-valid').attr('aria-invalid', 'false');
          }
          return !errorMessage;
        }

        $('input, textarea, select').each(function () {
          var field = $(this);
          if (!field.data('validation')) return;
          if (!field.siblings('.validation-error').length &&
              !$('#' + (field.attr('id') || field.attr('name')) + 'Error').length) {
            $('<span class="validation-error error-text" role="alert"></span>').insertAfter(field);
          }
        });

        $(document).on('input change blur', 'input, textarea, select', function () {
          if ($(this).data('validation')) validateField(this);
        });

        $('form').on('submit', function (e) {
          var form = this;
          var isValid = true;
          $(form).find('input, textarea, select').each(function () {
            if ($(this).data('validation') && !validateField(this)) isValid = false;
          });
          if (!isValid) {
            e.preventDefault();
            $(form).find('.is-invalid').first().trigger('focus');
          }
        });
      })(window.jQuery);
    }

    /* --- Universal data-confirm handler ------------------------------ */
    document.addEventListener('click', function (e) {
      var confirmEl = e.target.closest('[data-confirm]');
      if (!confirmEl) return;
      var promptMsg = confirmEl.getAttribute('data-confirm');
      if (promptMsg && !window.confirm(promptMsg)) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
    }, true);

    /* --- Stepper buttons (- / +) ------------------------------------- */
    document.addEventListener('click', function (e) {
      var stepBtn = e.target.closest('.stepper button[data-step]');
      if (!stepBtn) return;
      var delta = parseInt(stepBtn.getAttribute('data-step'), 10) || 0;
      var stepper = stepBtn.closest('.stepper');
      if (!stepper) return;
      var input = stepper.querySelector('input[type="number"]');
      if (!input) return;
      var curVal = parseInt(input.value, 10) || 1;
      var minVal = parseInt(input.getAttribute('min'), 10) || 1;
      var maxVal = parseInt(input.getAttribute('data-max') || input.getAttribute('max'), 10) || 99;
      var newVal = Math.max(minVal, Math.min(maxVal, curVal + delta));
      input.value = newVal;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    /* --- Wishlist Heart Toggle Handler (data-act="fav") --------------- */
    function updateHeaderWishlistBadge(count) {
      var badge = document.getElementById('headerWishlistCount');
      if (!badge) return;
      badge.textContent = count;
      if (count > 0) {
        badge.style.display = '';
      } else {
        badge.style.display = 'none';
      }
    }

    function updateHeaderCartBadge(count) {
      var badge = document.getElementById('headerCartCount');
      if (!badge) return;
      badge.textContent = count;
      if (count > 0) {
        badge.style.display = '';
      } else {
        badge.style.display = 'none';
      }
    }

    document.addEventListener('click', function (e) {
      var favBtn = e.target.closest('[data-act="fav"]');
      if (!favBtn) return;
      e.preventDefault();

      var productId = favBtn.getAttribute('data-id');
      var productName = favBtn.getAttribute('data-name') || 'Product';
      if (!productId) return;

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=wishlist/toggle';

      favBtn.disabled = true;

      var fd = new FormData();
      fd.append('product_id', productId);
      fd.append('ajax', '1');

      fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
      })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP error ' + res.status);
        return res.json();
      })
      .then(function (data) {
        favBtn.disabled = false;
        if (data && data.success) {
          var allSameBtns = document.querySelectorAll('[data-act="fav"][data-id="' + productId + '"]');
          allSameBtns.forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (data.in_wishlist) {
              btn.classList.add('is-fav');
              btn.setAttribute('aria-pressed', 'true');
              btn.setAttribute('title', 'Remove from wishlist');
              btn.setAttribute('aria-label', 'Remove ' + productName + ' from wishlist');
              if (icon) {
                icon.className = 'fas fa-heart';
              }
            } else {
              btn.classList.remove('is-fav');
              btn.setAttribute('aria-pressed', 'false');
              btn.setAttribute('title', 'Save to wishlist');
              btn.setAttribute('aria-label', 'Save ' + productName + ' to wishlist');
              if (icon) {
                icon.className = 'far fa-heart';
              }
            }
          });

          updateHeaderWishlistBadge(data.count);

          if (window.MediMax && window.MediMax.toast) {
            if (data.in_wishlist) {
              window.MediMax.toast('Saved "' + productName + '" to wishlist', 'fa-heart');
            } else {
              window.MediMax.toast('Removed "' + productName + '" from wishlist', 'fa-heart-crack');
            }
          }
        } else {
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast((data && data.message) || 'Could not update wishlist', 'fa-circle-exclamation');
          }
        }
      })
      .catch(function (err) {
        favBtn.disabled = false;
        console.error('Wishlist toggle error:', err);
        if (window.MediMax && window.MediMax.toast) {
          window.MediMax.toast('Network error updating wishlist', 'fa-circle-exclamation');
        }
      });
    });

    /* --- Format Indian currency helper ------------------------------ */
    function formatINR(val) {
      return parseFloat(val || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    /* --- Add to Cart Handler (data-act="cart") on Product Cards ------- */
    document.addEventListener('click', function (e) {
      var cartBtn = e.target.closest('[data-act="cart"]');
      if (!cartBtn) return;
      var productId = cartBtn.getAttribute('data-id');
      var productName = cartBtn.getAttribute('data-name') || 'Product';
      if (!productId) return;

      e.preventDefault();
      cartBtn.disabled = true;

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/add';

      var fd = new FormData();
      fd.append('product_id', productId);
      fd.append('quantity', '1');
      fd.append('ajax', '1');

      fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        cartBtn.disabled = false;
        if (data && data.success) {
          updateHeaderCartBadge(data.units !== undefined ? data.units : data.count);
          cartBtn.classList.add('is-done');
          setTimeout(function () { cartBtn.classList.remove('is-done'); }, 1200);
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast('Added "' + productName + '" to cart', 'fa-cart-plus');
          }
        } else {
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast((data && data.message) || 'Could not add product to cart', 'fa-circle-exclamation');
          }
        }
      })
      .catch(function (err) {
        cartBtn.disabled = false;
        console.error('Cart add error:', err);
        if (window.MediMax && window.MediMax.toast) {
          window.MediMax.toast('Network error adding to cart', 'fa-circle-exclamation');
        }
      });
    });

    /* --- Live Cart Receipt & Summary Updater ------------------------- */
    function updateCartReceipt(data) {
      if (!data) return;

      // Update Header badge
      if (data.units !== undefined) {
        updateHeaderCartBadge(data.units);
      } else if (data.count !== undefined) {
        updateHeaderCartBadge(data.count);
      }

      // Check if cart is now empty
      if (data.is_empty || (data.count !== undefined && data.count === 0)) {
        var cartMainRow = document.getElementById('cartMainRow');
        if (cartMainRow) cartMainRow.style.display = 'none';

        var cartEmptyState = document.getElementById('cartEmptyState');
        if (cartEmptyState) cartEmptyState.style.display = '';

        var cartLede = document.getElementById('cartLede');
        if (cartLede) cartLede.textContent = 'Nothing here yet. Add medicines or wellness products from the shelf and they will land here.';
        return;
      }

      // Summary counts in header
      var countSummary = document.getElementById('cartCountSummary');
      if (countSummary && data.count !== undefined) countSummary.textContent = data.count;

      var countPlural = document.getElementById('cartCountPlural');
      if (countPlural && data.count !== undefined) countPlural.textContent = (data.count === 1 ? 'line' : 'lines');

      var unitsSummary = document.getElementById('cartUnitsSummary');
      if (unitsSummary && data.units !== undefined) unitsSummary.textContent = data.units;

      var unitsPlural = document.getElementById('cartUnitsPlural');
      if (unitsPlural && data.units !== undefined) unitsPlural.textContent = (data.units === 1 ? 'unit' : 'units');

      var panelUnitsBadge = document.getElementById('panelUnitsBadge');
      if (panelUnitsBadge && data.units !== undefined) panelUnitsBadge.textContent = data.units + ' units';

      var receiptItemCount = document.getElementById('receiptItemCount');
      if (receiptItemCount && data.count !== undefined) receiptItemCount.textContent = data.count;

      var cartUnits = document.getElementById('cartUnits');
      if (cartUnits && data.units !== undefined) cartUnits.textContent = data.units;

      // Subtotal
      var cartSubtotal = document.getElementById('cartSubtotal');
      if (cartSubtotal && data.subtotal !== undefined) cartSubtotal.innerHTML = '&#8377;' + formatINR(data.subtotal);

      // Delivery Banner & Row
      var cartDelivery = document.getElementById('cartDelivery');
      var freeDeliveryBanner = document.getElementById('freeDeliveryBanner');
      var freeDeliveryText = document.getElementById('freeDeliveryText');
      var freeDeliveryProgressBar = document.getElementById('freeDeliveryProgressBar');

      if (data.deliveryFee === 0 || data.isFreeDelivery) {
        if (cartDelivery) cartDelivery.innerHTML = '<span class="badge bg-success" style="font-size:0.65rem">FREE</span>';
        if (freeDeliveryBanner) {
          freeDeliveryBanner.style.background = '#E4F3EC';
          freeDeliveryBanner.style.borderColor = 'rgba(31,122,85,.3)';
          freeDeliveryBanner.style.color = '#1F7A55';
        }
        if (freeDeliveryText) {
          freeDeliveryText.innerHTML = '<strong>Congratulations!</strong> Your order qualifies for <strong>FREE Delivery</strong> across India.';
        }
        if (freeDeliveryProgressBar) {
          freeDeliveryProgressBar.style.width = '100%';
          freeDeliveryProgressBar.style.background = '#1F7A55';
        }
      } else {
        if (cartDelivery) cartDelivery.innerHTML = '&#8377;' + formatINR(data.deliveryFee);
        if (freeDeliveryBanner) {
          freeDeliveryBanner.style.background = '#E5F0F8';
          freeDeliveryBanner.style.borderColor = 'rgba(44,110,155,.2)';
          freeDeliveryBanner.style.color = '#2C6E9B';
        }
        if (freeDeliveryText) {
          freeDeliveryText.innerHTML = 'Add <strong>&#8377;' + formatINR(data.freeDeliveryRemaining) + '</strong> more to unlock <strong>FREE Delivery</strong>!';
        }
        if (freeDeliveryProgressBar) {
          freeDeliveryProgressBar.style.width = (data.freeDeliveryPercent || 0) + '%';
          freeDeliveryProgressBar.style.background = 'var(--teal-700, #2C7A80)';
        }
      }

      // Coupon Discount row
      var cartDiscountRow = document.getElementById('cartDiscountRow');
      var cartDiscount = document.getElementById('cartDiscount');
      if (data.discountAmount && data.discountAmount > 0) {
        if (cartDiscountRow) cartDiscountRow.style.display = '';
        if (cartDiscount) cartDiscount.innerHTML = '-&#8377;' + formatINR(data.discountAmount);
      } else {
        if (cartDiscountRow) cartDiscountRow.style.display = 'none';
      }

      // Grand Total
      var cartTotal = document.getElementById('cartTotal');
      if (cartTotal && data.grandTotal !== undefined) {
        cartTotal.innerHTML = '&#8377;' + formatINR(data.grandTotal);
      }
    }

    /* --- Live Cart Quantity Stepper & Change Debounce ---------------- */
    var cartUpdateTimeout = null;

    function triggerCartQuantityUpdate(input) {
      if (!input) return;
      var cartId = input.getAttribute('data-cart-id');
      var newQty = parseInt(input.value, 10) || 1;
      var lineItem = input.closest('.line-item');
      var maxStock = parseInt(lineItem ? lineItem.getAttribute('data-max-stock') : 10, 10) || 10;

      if (newQty > maxStock) {
        newQty = maxStock;
        input.value = newQty;
        if (window.MediMax && window.MediMax.toast) {
          window.MediMax.toast('Maximum available stock is ' + maxStock + ' units.', 'fa-triangle-exclamation');
        }
      } else if (newQty < 1) {
        newQty = 1;
        input.value = newQty;
      }

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/update';

      var fd = new FormData();
      fd.append('cart_id', cartId);
      fd.append('quantity', newQty);
      fd.append('ajax', '1');

      if (cartUpdateTimeout) clearTimeout(cartUpdateTimeout);
      cartUpdateTimeout = setTimeout(function () {
        fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data && data.success) {
            var lineSubEl = document.getElementById('lineSubtotal-' + cartId);
            if (lineSubEl && data.line_subtotal !== undefined) {
              lineSubEl.innerHTML = '&#8377;' + formatINR(data.line_subtotal);
            }
            updateCartReceipt(data);
          } else if (data && !data.success) {
            if (window.MediMax && window.MediMax.toast) {
              window.MediMax.toast(data.message || 'Could not update quantity', 'fa-circle-exclamation');
            }
          }
        })
        .catch(function (err) {
          console.error('Cart quantity update error:', err);
        });
      }, 250);
    }

    document.addEventListener('change', function (e) {
      if (e.target && e.target.classList.contains('cart-qty-input')) {
        triggerCartQuantityUpdate(e.target);
      }
    });

    document.addEventListener('click', function (e) {
      var stepBtn = e.target.closest('.line-item .stepper button[data-step]');
      if (!stepBtn) return;
      var input = stepBtn.closest('.stepper').querySelector('.cart-qty-input');
      if (input) {
        // Handled by earlier stepper listener, trigger AJAX shortly after
        setTimeout(function () {
          triggerCartQuantityUpdate(input);
        }, 50);
      }
    });

    /* --- Live Cart Item Removal Handler (data-act="remove-cart") ------ */
    document.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-act="remove-cart"]');
      if (!removeBtn) return;
      e.preventDefault();

      var lineItem = removeBtn.closest('.line-item');
      var cartId = removeBtn.getAttribute('data-cart-id');
      var href = removeBtn.getAttribute('href');
      var productName = (lineItem && lineItem.getAttribute('data-name')) || 'Product';

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/remove';

      var fd = new FormData();
      fd.append('cart_id', cartId);
      fd.append('id', cartId);
      fd.append('ajax', '1');

      fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          if (lineItem) {
            lineItem.style.transition = 'all 0.3s ease-out';
            lineItem.style.opacity = '0';
            lineItem.style.transform = 'translateX(20px)';
            setTimeout(function () {
              lineItem.remove();
              updateCartReceipt(data);
            }, 300);
          } else {
            updateCartReceipt(data);
          }

          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast('Removed "' + productName + '" from cart', 'fa-trash-can');
          }
        } else {
          window.location.href = href;
        }
      })
      .catch(function () {
        window.location.href = href;
      });
    });

    /* --- Move to Wishlist from Cart (data-act="cart-to-wishlist") ----- */
    document.addEventListener('click', function (e) {
      var moveBtn = e.target.closest('[data-act="cart-to-wishlist"]');
      if (!moveBtn) return;
      e.preventDefault();

      var lineItem = moveBtn.closest('.line-item');
      var cartId = moveBtn.getAttribute('data-cart-id');
      var productId = moveBtn.getAttribute('data-product-id');
      var productName = moveBtn.getAttribute('data-name') || 'Product';

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/move-to-wishlist';

      var fd = new FormData();
      fd.append('cart_id', cartId);
      fd.append('product_id', productId);
      fd.append('ajax', '1');

      moveBtn.disabled = true;

      fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        moveBtn.disabled = false;
        if (data && data.success) {
          if (data.wishlist_count !== undefined) {
            updateHeaderWishlistBadge(data.wishlist_count);
          }
          if (lineItem) {
            lineItem.style.transition = 'all 0.3s ease-out';
            lineItem.style.opacity = '0';
            lineItem.style.transform = 'translateX(20px)';
            setTimeout(function () {
              lineItem.remove();
              updateCartReceipt(data);
            }, 300);
          }

          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast('Saved "' + productName + '" to your wishlist', 'fa-heart');
          }
        } else {
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast((data && data.message) || 'Could not move product', 'fa-circle-exclamation');
          }
        }
      })
      .catch(function (err) {
        moveBtn.disabled = false;
        console.error('Move to wishlist error:', err);
      });
    });

    /* --- Coupon Form & Quick Chips Handler ---------------------------- */
    document.addEventListener('click', function (e) {
      var chip = e.target.closest('.coupon-chip');
      if (!chip) return;
      var code = chip.getAttribute('data-code');
      var input = document.getElementById('couponInput');
      if (input && code) {
        input.value = code;
        var form = document.getElementById('couponForm');
        if (form) {
          form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
      }
    });

    document.addEventListener('submit', function (e) {
      var couponForm = e.target.closest('#couponForm');
      if (!couponForm) return;
      e.preventDefault();

      var input = document.getElementById('couponInput');
      var code = input ? input.value.trim().toUpperCase() : '';
      if (!code) return;

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/apply-coupon';

      var fd = new FormData();
      fd.append('coupon_code', code);
      fd.append('ajax', '1');

      fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          updateCartReceipt(data);
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast(data.message, 'fa-ticket');
          }
          // Reload page to display applied coupon banner cleanly or refresh UI
          setTimeout(function () { window.location.reload(); }, 600);
        } else {
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast((data && data.message) || 'Invalid coupon code', 'fa-circle-exclamation');
          }
        }
      })
      .catch(function (err) {
        console.error('Coupon apply error:', err);
      });
    });

    document.addEventListener('click', function (e) {
      var removeCouponBtn = e.target.closest('#removeCouponBtn');
      if (!removeCouponBtn) return;
      e.preventDefault();

      var baseUrl = window.BASE_URL || '/MediMax/';
      var url = baseUrl + '?page=cart/remove-coupon';

      fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          updateCartReceipt(data);
          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast('Coupon removed', 'fa-info-circle');
          }
          setTimeout(function () { window.location.reload(); }, 600);
        }
      })
      .catch(function (err) {
        console.error('Coupon remove error:', err);
      });
    });

    /* --- Wishlist Page: Live Item Removal Handler --------------------- */
    document.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-act="remove-wishlist"]');
      if (!removeBtn) return;
      e.preventDefault();

      var lineItem = removeBtn.closest('.line-item');
      var href = removeBtn.getAttribute('href');
      var productName = (lineItem && lineItem.getAttribute('data-name')) || 'Product';

      fetch(href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          if (lineItem) {
            lineItem.style.transition = 'all 0.3s ease-out';
            lineItem.style.opacity = '0';
            lineItem.style.transform = 'translateX(20px)';
            setTimeout(function () {
              lineItem.remove();

              var remaining = document.querySelectorAll('#wishlistBody .line-item');
              var newCount = remaining.length;
              var newSaved = 0;
              remaining.forEach(function (el) {
                newSaved += parseFloat(el.getAttribute('data-price') || 0);
              });

              updateHeaderWishlistBadge(data.count !== undefined ? data.count : newCount);

              var panelCountEl = document.getElementById('wishlistPanelCount');
              if (panelCountEl) panelCountEl.textContent = newCount + ' saved';

              var summaryCountEl = document.getElementById('wishlistCountSummary');
              if (summaryCountEl) summaryCountEl.textContent = newCount;

              var pluralEl = document.getElementById('wishlistCountPlural');
              if (pluralEl) pluralEl.textContent = newCount === 1 ? 'product' : 'products';

              var totalSavedEl = document.getElementById('wishlistTotalSaved');
              if (totalSavedEl) totalSavedEl.innerHTML = '&#8377;' + newSaved.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

              if (newCount === 0) {
                var panel = document.getElementById('wishlistPanel');
                if (panel) panel.style.display = 'none';

                var emptyState = document.getElementById('wishlistEmptyState');
                if (emptyState) emptyState.style.display = '';

                var lede = document.getElementById('wishlistLede');
                if (lede) lede.textContent = 'Tap the heart on any product to keep it here without buying it yet.';
              }
            }, 300);
          }

          if (window.MediMax && window.MediMax.toast) {
            window.MediMax.toast('Removed "' + productName + '" from wishlist', 'fa-trash-can');
          }
        } else {
          window.location.href = href;
        }
      })
      .catch(function () {
        // Fallback to browser navigation if AJAX fails
        window.location.href = href;
      });
    });

  });

  /* --- Global MediMax helpers --- */
  window.MediMax = window.MediMax || {
    toast: function (msg, icon) {
      icon = icon || 'fa-info-circle';
      var container = document.querySelector('.toast-stack');
      if (!container) {
        container = document.createElement('div');
        container.className = 'toast-stack position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
      }
      var toastEl = document.createElement('div');
      toastEl.className = 'alert alert-dark d-flex align-items-center gap-2 shadow-sm rounded-3 mb-2';
      toastEl.style.cssText = 'background: #1B3E5E; color: #fff; border: 0; padding: .65rem 1rem; font-size: .88rem;';
      toastEl.innerHTML = '<i class="fas ' + icon + '"></i><span>' + msg + '</span>';
      container.appendChild(toastEl);
      setTimeout(function () {
        toastEl.remove();
      }, 3000);
    }
  };
})();
