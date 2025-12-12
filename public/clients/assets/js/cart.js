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

    rows.forEach((row) => {
        const input = row.querySelector('.xanhworld_cart_item_quantity_input');
        const increaseBtn = row.querySelector('.xanhworld_cart_item_quantity_increase');
        const decreaseBtn = row.querySelector('.xanhworld_cart_item_quantity_decrease');

        increaseBtn?.addEventListener('click', () => {
            if (! input) {
                return;
            }
            input.stepUp();
            updateRow(row);
        });

        decreaseBtn?.addEventListener('click', () => {
            if (! input) {
                return;
            }
            input.stepDown();
            if (parseInt(input.value, 10) < 0) {
                input.value = 0;
            }
            updateRow(row);
        });

        input?.addEventListener('change', () => updateRow(row));

        updateRow(row);
    });
});
