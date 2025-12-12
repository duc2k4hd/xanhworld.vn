// Show lọc danh mục sản phẩm
const xanhworldFilterWrapper = document.querySelector('.xanhworld_shop_products_filter');
const xanhworldFilterHeading = document.querySelector('.xanhworld_shop_products_filter_categories_title');

if (xanhworldFilterWrapper && xanhworldFilterHeading) {
    xanhworldFilterHeading.addEventListener('click', function() {
        xanhworldFilterWrapper.classList.toggle('xanhworld_shop_products_filter_height_full');
    });
}

// Form lọc giá
async function setPrice(min, max) {
    showCustomToast(`Chọn giá từ ${min} đến ${max}`);
    await sleep(1000);
    const minInput = document.getElementById('minPriceRange');
    const maxInput = document.getElementById('maxPriceRange');
    const form = document.getElementById('xanhworld_shop_products_filter_price_content_form');

    if (!minInput || !maxInput || !form) {
        return;
    }

    minInput.value = min;
    maxInput.value = max;
    form.submit();
}

