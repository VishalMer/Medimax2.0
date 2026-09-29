/* --- Checkout & Mock Payment System --- */

document.addEventListener('DOMContentLoaded', function() {
    // Only execute if on the checkout page (indicated by presence of #chkSubtotal)
    if (!document.getElementById('chkSubtotal')) return;

    let selectedPaymentMethod = null;
    let finalPayableAmount = 0;

    // --- Saved Addresses Logic ---
    document.querySelectorAll('.saved-addr-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delName').value = this.dataset.name || '';
            document.getElementById('delMobile').value = this.dataset.mobile || '';
            document.getElementById('delEmail').value = this.dataset.email || '';
            document.getElementById('delAddress').value = this.dataset.address + ' ' + (this.dataset.apt || '');
            document.getElementById('delArea').value = this.dataset.area || '';
            document.getElementById('delCity').value = this.dataset.city || '';
            document.getElementById('delState').value = this.dataset.state || '';
            document.getElementById('delPincode').value = this.dataset.pincode || '';
            
            // Remove active class from all, add to this
            document.querySelectorAll('.saved-addr-btn').forEach(b => b.classList.remove('btn-primary', 'text-white'));
            document.querySelectorAll('.saved-addr-btn').forEach(b => b.classList.add('btn-outline-secondary'));
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'text-white');
        });
    });

    const clearAddrBtn = document.getElementById('clearAddrBtn');
    if (clearAddrBtn) {
        clearAddrBtn.addEventListener('click', function() {
            document.getElementById('checkoutForm').reset();
            document.querySelectorAll('.saved-addr-btn').forEach(b => {
                b.classList.remove('btn-primary', 'text-white');
                b.classList.add('btn-outline-secondary');
            });
        });
    }

    // --- Payment Selection Logic ---
    window.selectPayment = function(method) {
        selectedPaymentMethod = method;
        document.getElementById('radio' + method).checked = true;
        updateCTA();
    };

    // Make accordion radio buttons sync properly if clicked directly
    document.querySelectorAll('input[name="paymentMethodRadio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            selectedPaymentMethod = this.value;
            // Also open the correct accordion pane if radio was clicked directly
            let targetPane = document.getElementById('pay' + this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase());
            if (this.value === 'UPI') targetPane = document.getElementById('payUPI');
            else if (this.value === 'CARD') targetPane = document.getElementById('payCard');
            else if (this.value === 'NETBANK') targetPane = document.getElementById('payNetbank');
            else if (this.value === 'COD') targetPane = document.getElementById('payCOD');
            
            // Close others and open target using Bootstrap collapse API if loaded
            if (window.bootstrap && targetPane && !targetPane.classList.contains('show')) {
               const bscollapse = new bootstrap.Collapse(targetPane);
               bscollapse.show();
            }
            updateCTA();
        });
    });

    // --- Pricing Calculation (Reusing Cart Discount logic safely) ---
    function updateCheckoutUI() {
        if (!window.mediMaxCartData) return;
        const data = window.mediMaxCartData;
        let appliedCoupon = sessionStorage.getItem('appliedCoupon') ? JSON.parse(sessionStorage.getItem('appliedCoupon')) : null;

        function getProductOriginalPrice(productId) {
            const prod = data.products.find(p => p.id == productId);
            return (prod && prod.original_price && prod.original_price > prod.price) ? parseFloat(prod.original_price) : parseFloat(prod ? prod.price : 0);
        }

        let originalSubtotal = 0;
        let actualSubtotal = 0;
        data.items.forEach(item => {
            const qty = parseInt(item.quantity, 10);
            originalSubtotal += getProductOriginalPrice(item.id) * qty;
            actualSubtotal += parseFloat(item.price) * qty;
        });

        let couponDiscount = 0;
        if (appliedCoupon) {
            let applicableAmount = 0;
            if (appliedCoupon.applicableCategory && appliedCoupon.applicableCategory !== 'All') {
                data.items.forEach(item => {
                    const prod = data.products.find(p => p.id == item.id);
                    if (prod && prod.category === appliedCoupon.applicableCategory) {
                        applicableAmount += parseFloat(item.price) * parseInt(item.quantity, 10);
                    }
                });
            } else {
                applicableAmount = actualSubtotal;
            }

            if (applicableAmount > 0) {
                if (appliedCoupon.discountType === 'percentage') {
                    couponDiscount = applicableAmount * (parseFloat(appliedCoupon.discountValue) / 100);
                    if (appliedCoupon.maximumDiscount && couponDiscount > parseFloat(appliedCoupon.maximumDiscount)) {
                        couponDiscount = parseFloat(appliedCoupon.maximumDiscount);
                    }
                } else {
                    couponDiscount = parseFloat(appliedCoupon.discountValue);
                    if (couponDiscount > applicableAmount) couponDiscount = applicableAmount;
                }
            }
        }

        const productDiscount = originalSubtotal - actualSubtotal;
        const subtotalAfterCoupons = actualSubtotal - couponDiscount;
        const deliveryFee = subtotalAfterCoupons >= 999 ? 0 : 49;
        finalPayableAmount = subtotalAfterCoupons + deliveryFee;
        const totalSavings = productDiscount + couponDiscount;

        // Render to UI
        document.getElementById('chkSubtotal').innerHTML = `&#8377;${originalSubtotal.toFixed(2)}`;
        
        if (productDiscount > 0) {
            document.getElementById('chkProductDiscountRow').classList.remove('d-none');
            document.getElementById('chkProductDiscount').innerHTML = `-&#8377;${productDiscount.toFixed(2)}`;
        }

        if (couponDiscount > 0 && appliedCoupon) {
            document.getElementById('chkCouponDiscountRow').classList.remove('d-none');
            document.getElementById('chkCouponDiscount').innerHTML = `-&#8377;${couponDiscount.toFixed(2)}`;
            document.getElementById('chkAppliedCouponCode').innerText = appliedCoupon.code;
        }

        document.getElementById('chkDelivery').innerHTML = deliveryFee === 0 ? '<span class="text-success fw-bold">FREE</span>' : `&#8377;${deliveryFee.toFixed(2)}`;
        document.getElementById('chkTotal').innerHTML = `&#8377;${finalPayableAmount.toFixed(2)}`;

        if (totalSavings > 0) {
            document.getElementById('chkSavingsSection').classList.remove('d-none');
            document.getElementById('chkTotalSavingsValue').innerHTML = `&#8377;${totalSavings.toFixed(2)}`;
        }
        
        updateCTA();
    }

    function updateCTA() {
        const btnText = document.getElementById('placeOrderBtnText');
        if (!selectedPaymentMethod) {
            btnText.innerText = `Place Order (Select Payment)`;
        } else if (selectedPaymentMethod === 'COD') {
            btnText.innerText = `Place Order — ₹${finalPayableAmount.toFixed(2)}`;
        } else {
            btnText.innerText = `Place Order & Pay ₹${finalPayableAmount.toFixed(2)}`;
        }
    }

    // --- Validation Logic ---
    function validateAddress() {
        const form = document.getElementById('checkoutForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        // Additional manual checks
        const mobile = document.getElementById('delMobile').value;
        if (!/^[0-9]{10}$/.test(mobile)) {
            showError("Please enter a valid 10-digit mobile number.");
            return false;
        }
        const pincode = document.getElementById('delPincode').value;
        if (!/^[0-9]{6}$/.test(pincode)) {
            showError("Please enter a valid 6-digit pincode.");
            return false;
        }
        return true;
    }

    function validatePayment() {
        if (!selectedPaymentMethod) {
            showError("Please select a payment method to continue.");
            return false;
        }

        if (selectedPaymentMethod === 'UPI') {
            const upi = document.getElementById('upiId').value;
            if (!upi || !/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/.test(upi)) {
                showError("Please enter a valid UPI ID (e.g. username@upi).");
                return false;
            }
        }
        else if (selectedPaymentMethod === 'CARD') {
            const num = document.getElementById('cardNumber').value.replace(/\s+/g, '');
            if (!num || num.length < 15 || num.length > 19 || isNaN(num)) {
                showError("Please enter a valid Card Number.");
                return false;
            }
            const exp = document.getElementById('cardExpiry').value;
            if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(exp)) {
                showError("Please enter a valid Expiry Date (MM/YY).");
                return false;
            }
            const cvv = document.getElementById('cardCvv').value;
            if (!/^[0-9]{3,4}$/.test(cvv)) {
                showError("Please enter a valid CVV.");
                return false;
            }
            const name = document.getElementById('cardName').value;
            if (!name || name.trim().length < 3) {
                showError("Please enter the Name on Card.");
                return false;
            }
        }
        else if (selectedPaymentMethod === 'NETBANK') {
            const bank = document.getElementById('bankSelect').value;
            if (!bank) {
                showError("Please select a Bank for Net Banking.");
                return false;
            }
        }
        return true;
    }

    function showError(msg) {
        const errDiv = document.getElementById('checkoutErrorMsg');
        errDiv.innerText = msg;
        errDiv.classList.remove('d-none');
        // Scroll to error
        window.scrollTo({ top: errDiv.offsetTop - 100, behavior: 'smooth' });
    }

    function hideError() {
        document.getElementById('checkoutErrorMsg').classList.add('d-none');
    }

    // --- Card formatting UI ---
    document.getElementById('cardNumber').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        val = val.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = val;
    });

    document.getElementById('cardExpiry').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length >= 2) {
            val = val.substring(0,2) + '/' + val.substring(2,4);
        }
        e.target.value = val;
    });

    const toggleCvv = document.getElementById('toggleCvv');
    if(toggleCvv) {
        toggleCvv.addEventListener('click', function() {
            const cvvInput = document.getElementById('cardCvv');
            if (cvvInput.type === 'password') {
                cvvInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                cvvInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    }

    // --- Mock Payment Engine ---
    document.getElementById('placeOrderBtn').addEventListener('click', function() {
        hideError();
        
        if (!validateAddress()) return;
        if (!validatePayment()) return;

        const btn = document.getElementById('placeOrderBtn');
        const spinner = document.getElementById('placeOrderSpinner');
        const originalText = document.getElementById('placeOrderBtnText').innerText;
        
        // UI Loading state
        btn.disabled = true;
        document.getElementById('placeOrderBtnText').innerText = "Processing Payment...";
        spinner.classList.remove('d-none');

        // Simulate network delay
        setTimeout(() => {
            // Deterministic mock success/failure
            const randomChance = Math.random();
            // 90% success rate
            if (randomChance < 0.90 || selectedPaymentMethod === 'COD') {
                // Success
                btn.classList.remove('btn-mm-amber');
                btn.classList.add('btn-success');
                spinner.classList.add('d-none');
                document.getElementById('placeOrderBtnText').innerHTML = '<i class="fas fa-check-circle me-2"></i>Payment Successful';
                
                // Clear active coupon on successful order
                sessionStorage.removeItem('appliedCoupon');
                
                // Generate Order ID and redirect
                const orderId = 'MED-' + new Date().getFullYear() + '-' + Math.floor(10000 + Math.random() * 90000);
                setTimeout(() => {
                    const baseUrl = window.location.href.split('?')[0];
                    window.location.href = `${baseUrl}?page=order-success&order_id=${orderId}&amount=${finalPayableAmount}&method=${selectedPaymentMethod}`;
                }, 800);
            } else {
                // Failure (10% chance)
                spinner.classList.add('d-none');
                btn.disabled = false;
                document.getElementById('placeOrderBtnText').innerText = originalText;
                
                showError("Payment Failed: The bank server did not respond. Please try again or choose a different payment method.");
                // Note: Cart remains intact, user can retry.
            }
        }, 2500); // 2.5 seconds mock delay
    });

    // Run on load
    updateCheckoutUI();
});
