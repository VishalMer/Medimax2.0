/* --- Cart Discount & Coupon System --- */
document.addEventListener('DOMContentLoaded', function() {
    if (!window.mediMaxCartData) return;

    const data = window.mediMaxCartData;
    let appliedCoupon = sessionStorage.getItem('appliedCoupon') ? JSON.parse(sessionStorage.getItem('appliedCoupon')) : null;

    function getProductOriginalPrice(productId) {
        const prod = data.products.find(p => p.id == productId);
        if (prod && prod.original_price && prod.original_price > prod.price) {
            return parseFloat(prod.original_price);
        }
        return parseFloat(prod ? prod.price : 0);
    }

    function calculateSubtotal() {
        let originalSubtotal = 0;
        let actualSubtotal = 0;
        
        data.items.forEach(item => {
            const qty = parseInt(item.quantity, 10);
            const originalPrice = getProductOriginalPrice(item.id);
            const price = parseFloat(item.price);
            
            originalSubtotal += originalPrice * qty;
            actualSubtotal += price * qty;
        });

        return { originalSubtotal, actualSubtotal };
    }

    function calculateProductDiscount() {
        const { originalSubtotal, actualSubtotal } = calculateSubtotal();
        return originalSubtotal - actualSubtotal;
    }

    function calculateCouponDiscount(actualSubtotal) {
        if (!appliedCoupon) return 0;
        
        let discount = 0;
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

        if (applicableAmount <= 0) return 0;

        if (appliedCoupon.discountType === 'percentage') {
            discount = applicableAmount * (parseFloat(appliedCoupon.discountValue) / 100);
            if (appliedCoupon.maximumDiscount && discount > parseFloat(appliedCoupon.maximumDiscount)) {
                discount = parseFloat(appliedCoupon.maximumDiscount);
            }
        } else if (appliedCoupon.discountType === 'fixed') {
            discount = parseFloat(appliedCoupon.discountValue);
            if (discount > applicableAmount) {
                discount = applicableAmount;
            }
        }
        return discount;
    }

    function calculateDeliveryFee(subtotalAfterCoupons) {
        return subtotalAfterCoupons >= 999 ? 0 : 49;
    }

    function updateCartUI() {
        const { originalSubtotal, actualSubtotal } = calculateSubtotal();
        
        // Check if coupon is still valid after qty updates
        if (appliedCoupon) {
            const res = validateCoupon(appliedCoupon.code);
            if (!res.valid) {
                appliedCoupon = null;
                sessionStorage.removeItem('appliedCoupon');
                showCouponMsg(res.msg, 'danger');
            }
        }

        const productDiscount = calculateProductDiscount();
        const couponDiscount = calculateCouponDiscount(actualSubtotal);
        
        const subtotalAfterCoupons = actualSubtotal - couponDiscount;
        const deliveryFee = calculateDeliveryFee(subtotalAfterCoupons);
        const finalTotal = subtotalAfterCoupons + deliveryFee;
        const totalSavings = productDiscount + couponDiscount;

        document.getElementById('cartSubtotal').innerHTML = `&#8377;${originalSubtotal.toFixed(2)}`;
        
        const prodDiscRow = document.getElementById('productDiscountRow');
        if (productDiscount > 0) {
            prodDiscRow.classList.remove('d-none');
            document.getElementById('cartProductDiscount').innerHTML = `-&#8377;${productDiscount.toFixed(2)}`;
        } else {
            prodDiscRow.classList.add('d-none');
        }

        const coupDiscRow = document.getElementById('couponDiscountRow');
        if (couponDiscount > 0) {
            coupDiscRow.classList.remove('d-none');
            document.getElementById('cartCouponDiscount').innerHTML = `-&#8377;${couponDiscount.toFixed(2)}`;
            document.getElementById('appliedCouponCode').innerText = appliedCoupon.code;
        } else {
            coupDiscRow.classList.add('d-none');
        }

        document.getElementById('cartDelivery').innerHTML = deliveryFee === 0 ? '<span class="text-success fw-bold">FREE</span>' : `&#8377;${deliveryFee.toFixed(2)}`;
        document.getElementById('cartTotal').innerHTML = `&#8377;${finalTotal.toFixed(2)}`;

        const savingsSec = document.getElementById('savingsSection');
        if (totalSavings > 0) {
            savingsSec.classList.remove('d-none');
            document.getElementById('totalSavingsValue').innerHTML = `&#8377;${totalSavings.toFixed(2)}`;
        } else {
            savingsSec.classList.add('d-none');
        }

        const freeDeliveryThreshold = 999;
        const progressVal = Math.min((subtotalAfterCoupons / freeDeliveryThreshold) * 100, 100);
        document.getElementById('freeDeliveryBar').style.width = `${progressVal}%`;
        
        if (subtotalAfterCoupons >= freeDeliveryThreshold) {
            document.getElementById('freeDeliveryText').innerHTML = '🎉 You\'ve unlocked <span class="fw-bold text-success">FREE DELIVERY</span>!';
            document.getElementById('freeDeliveryBar').classList.add('bg-success');
            document.getElementById('freeDeliveryBar').classList.remove('bg-warning');
        } else {
            const needed = freeDeliveryThreshold - subtotalAfterCoupons;
            document.getElementById('freeDeliveryText').innerHTML = `Add &#8377;${needed.toFixed(2)} more to get <span class="fw-bold">FREE DELIVERY</span>`;
            document.getElementById('freeDeliveryBar').classList.add('bg-warning');
            document.getElementById('freeDeliveryBar').classList.remove('bg-success');
            document.getElementById('freeDeliveryBar').style.backgroundColor = 'var(--accent-orange, rgb(230, 120, 52))';
        }
        
        if (appliedCoupon) {
            document.getElementById('couponInput').value = appliedCoupon.code;
            document.getElementById('couponInput').disabled = true;
            document.getElementById('applyCouponBtn').disabled = true;
        } else {
            document.getElementById('couponInput').value = '';
            document.getElementById('couponInput').disabled = false;
            document.getElementById('applyCouponBtn').disabled = false;
        }
    }

    function validateCoupon(code) {
        const { actualSubtotal } = calculateSubtotal();
        code = code.trim().toUpperCase();
        
        const coupon = data.coupons.find(c => c.code === code && c.isActive);
        if (!coupon) {
            return { valid: false, msg: 'Coupon code is invalid.' };
        }
        
        const expiry = new Date(coupon.expiryDate);
        if (expiry < new Date()) {
            return { valid: false, msg: 'This coupon has expired.' };
        }
        
        if (actualSubtotal < parseFloat(coupon.minimumOrderValue)) {
            const diff = parseFloat(coupon.minimumOrderValue) - actualSubtotal;
            return { 
                valid: false, 
                msg: `This coupon requires a minimum order of &#8377;${coupon.minimumOrderValue}. You need &#8377;${diff.toFixed(2)} more.`
            };
        }
        
        if (coupon.applicableCategory && coupon.applicableCategory !== 'All') {
            let hasCategory = false;
            data.items.forEach(item => {
                const prod = data.products.find(p => p.id == item.id);
                if (prod && prod.category === coupon.applicableCategory) hasCategory = true;
            });
            if (!hasCategory) {
                return { valid: false, msg: `This coupon is only valid for ${coupon.applicableCategory} products.` };
            }
        }
        
        return { valid: true, msg: '✓ Coupon applied successfully.', coupon: coupon };
    }

    function applyCoupon() {
        const code = document.getElementById('couponInput').value;
        if (!code) return;
        
        if (appliedCoupon && appliedCoupon.code === code.toUpperCase()) {
            showCouponMsg('This coupon is already applied.', 'danger');
            return;
        }
        
        const res = validateCoupon(code);
        if (res.valid) {
            appliedCoupon = res.coupon;
            sessionStorage.setItem('appliedCoupon', JSON.stringify(appliedCoupon));
            showCouponMsg(res.msg, 'success');
            updateCartUI();
        } else {
            showCouponMsg(res.msg, 'danger');
        }
    }

    function removeCoupon(e) {
        if(e) e.preventDefault();
        appliedCoupon = null;
        sessionStorage.removeItem('appliedCoupon');
        showCouponMsg('Coupon removed.', 'secondary');
        updateCartUI();
    }

    function showCouponMsg(msg, type) {
        const msgDiv = document.getElementById('couponMessage');
        msgDiv.innerHTML = msg;
        msgDiv.className = `mt-2 text-${type}`;
        setTimeout(() => { if (msgDiv.innerHTML === msg) msgDiv.innerHTML = ''; }, 4000);
    }

    const applyBtn = document.getElementById('applyCouponBtn');
    if (applyBtn) applyBtn.addEventListener('click', applyCoupon);

    const removeBtn = document.getElementById('removeCouponBtn');
    if (removeBtn) removeBtn.addEventListener('click', removeCoupon);
    
    if (document.getElementById('cartSubtotal')) {
        updateCartUI();
    }
});
