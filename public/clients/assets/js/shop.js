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
    if (typeof showCustomToast === 'function') {
        showCustomToast(`Chọn giá từ ${min.toLocaleString('vi-VN')} đến ${max.toLocaleString('vi-VN')}`);
    }
    
    // Đợi 1 chút để user thấy thông báo
    if (typeof sleep === 'function') {
        await sleep(1000);
    } else {
        await new Promise(resolve => setTimeout(resolve, 1000));
    }

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
