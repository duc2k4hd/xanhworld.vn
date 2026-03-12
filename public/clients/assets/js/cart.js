document.addEventListener('DOMContentLoaded', () => {
    const formatCurrency = (value) =>
        new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            maximumFractionDigits: 0,
        }).format(value);

    const rows = document.querySelectorAll('.xanhworld_cart_item');
    const subtotalEl = document.querySelector('.xanhworld_cart_summary_row_subtotal');
    const totalEl = document.querySelector('.xanhworld_cart_summary_amount');

    const recalcSummary = () => {
        let sum = 0;
        rows.forEach((row) => {
            const input = row.querySelector('.xanhworld_cart_item_quantity_input');
            if (! input) {
                return;
            }
            const qty = Math.max(parseInt(input.value, 10) || 0, 0);
            const price = parseFloat(row.dataset.unitPrice || '0');
            sum += price * qty;
        });

        if (subtotalEl) {
            subtotalEl.textContent = formatCurrency(sum);
    }
        if (totalEl) {
            totalEl.textContent = formatCurrency(sum);
            totalEl.setAttribute('data-amount', `${sum}`);
        }
    };

    const clampValue = (input) => {
        let value = parseInt(input.value, 10);
        if (isNaN(value) || value < 0) {
            value = 0;
        }
        const maxAttr = input.dataset.maxQuantity;
        const max = maxAttr === '' ? null : parseInt(maxAttr, 10);
        if (max !== null && ! Number.isNaN(max) && value > max) {
            value = max;
            }

        input.value = value;
        return value;
    };

    const updateRow = (row) => {
        const input = row.querySelector('.xanhworld_cart_item_quantity_input');
        const totalCell = row.querySelector('.xanhworld_cart_item_total');
        const stockBadge = row.querySelector('.xanhworld_cart_item_stock_notice');

        if (! input) {
            return;
        }

        const value = clampValue(input);
        const unitPrice = parseFloat(row.dataset.unitPrice || '0');

        if (totalCell) {
            totalCell.textContent = formatCurrency(unitPrice * value);
        }

        if (stockBadge) {
            const maxAttr = input.dataset.maxQuantity;
            const max = maxAttr === '' ? null : parseInt(maxAttr, 10);
            if (max !== null && ! Number.isNaN(max)) {
                const remaining = Math.max(max - value, 0);
                stockBadge.textContent = remaining;
            }
        }

        recalcSummary();
    };

    const updateForm = document.getElementById('cart-update-form');
    let updateTimeout = null;

    if (!updateForm) {
        console.warn('[Cart] Update form not found!');
    }

    const autoUpdateCart = () => {
        // Clear previous timeout
        if (updateTimeout) {
            clearTimeout(updateTimeout);
        }

        // Auto submit after 1.5 seconds of no changes
        updateTimeout = setTimeout(() => {
            if (updateForm) {
                // Ensure all inputs are in the form or collected
                rows.forEach((row) => {
                    const input = row.querySelector('.xanhworld_cart_item_quantity_input');
                    if (input && !updateForm.contains(input)) {
                        // Input is outside form, need to add it or ensure it's tracked
                        const existingHidden = updateForm.querySelector(`input[name="${input.name}"][type="hidden"]`);
                        if (existingHidden) {
                            existingHidden.value = input.value;
                        } else {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = input.name;
                            hiddenInput.value = input.value;
                            updateForm.appendChild(hiddenInput);
                        }
                    }
                });
                
                // Show loading indicator
                const updateBtn = document.querySelector('.xanhworld_cart_update');
                if (updateBtn) {
                    updateBtn.disabled = true;
                    updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang cập nhật...';
                }
                updateForm.submit();
            }
        }, 1500);
    };

    rows.forEach((row) => {
        const input = row.querySelector('.xanhworld_cart_item_quantity_input');
        const increaseBtn = row.querySelector('.xanhworld_cart_item_quantity_increase');
        const decreaseBtn = row.querySelector('.xanhworld_cart_item_quantity_decrease');

        if (!input) {
            return;
        }

        increaseBtn?.addEventListener('click', () => {
            input.stepUp();
            updateRow(row);
            autoUpdateCart();
        });

        decreaseBtn?.addEventListener('click', () => {
            input.stepDown();
            if (parseInt(input.value, 10) < 0) {
                input.value = 0;
            }
            updateRow(row);
            autoUpdateCart();
        });

        input.addEventListener('change', () => {
            updateRow(row);
            autoUpdateCart();
        });

        input.addEventListener('input', () => {
            updateRow(row);
            autoUpdateCart();
        });

        updateRow(row);
    });

    // Manual update button
    const manualUpdateBtn = document.querySelector('.xanhworld_cart_update');
    if (manualUpdateBtn && updateForm) {
        manualUpdateBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (updateTimeout) {
                clearTimeout(updateTimeout);
            }
            
            rows.forEach((row) => {
                const input = row.querySelector('.xanhworld_cart_item_quantity_input');
                if (input && !updateForm.contains(input)) {
                    const existingHidden = updateForm.querySelector(`input[name="${input.name}"][type="hidden"]`);
                    if (existingHidden) {
                        existingHidden.value = input.value;
                    } else {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.value = input.value;
                        updateForm.appendChild(hiddenInput);
                    }
                }
            });
            
            updateForm.submit();
        });
    }
});
