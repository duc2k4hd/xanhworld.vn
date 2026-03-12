console.log('single.js: Initializing...');
// Bổ sung các biến global cần thiết
window.currentVariantId = null;
window.lightboxImages = [];
window.currentLightboxIndex = 0;
// Trạng thái Lightbox Pro
window.lbState = {
  scale: 1,
  translateX: 0,
  translateY: 0,
  isDragging: false,
  startX: 0,
  startY: 0
};

/**
 * Thay đổi ảnh chính khi click vào thumbnail
 */
window.xanhworld_changeImg = function(thumb, src) {
  const mainImg = document.getElementById('xanhworld_main_img_src');
  if (mainImg) {
    mainImg.src = src;
    // Thêm hiệu ứng fade nhẹ
    mainImg.style.opacity = '0.5';
    setTimeout(() => {
      mainImg.style.opacity = '1';
    }, 100);
  }
  
  // Cập nhật trạng thái active của thumbnail
  document.querySelectorAll('.xanhworld_single_gallery_thumbs img').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

/**
 * Khởi tạo danh sách ảnh cho Lightbox
 */
window.xanhworld_initLightbox = function() {
  const thumbEls = document.querySelectorAll('.xanhworld_single_gallery_thumbs img');
  window.lightboxImages = Array.from(thumbEls).map(img => img.src.replace('_thumb', '')); // Giả sử nếu có thumb thì xóa hậu tố
  
  const container = document.getElementById('xanhworld_lightbox_thumbs');
  if (container) {
    container.innerHTML = '';
    window.lightboxImages.forEach((src, index) => {
      const img = document.createElement('img');
      img.src = src;
      img.alt = `Ảnh ${index + 1}`;
      img.dataset.index = index;
      img.onclick = () => window.xanhworld_setLightboxImg(index);
      container.appendChild(img);
    });
  }
}

/**
 * Mở Lightbox
 */
window.xanhworld_openLightbox = function(index = null) {
  const lightbox = document.getElementById('xanhworld_lightbox');
  const mainImg = document.getElementById('xanhworld_main_img_src');
  if (!lightbox || !mainImg) return;

  let targetIndex = index;
  if (targetIndex === null) {
    // Tìm index của ảnh hiện tại nếu không truyền vào
    targetIndex = window.lightboxImages.indexOf(mainImg.src);
    if (targetIndex < 0) targetIndex = 0;
  }

  window.xanhworld_setLightboxImg(targetIndex);

  lightbox.classList.add('active');
  document.body.classList.add('no-scroll');
}

/**
 * Đóng Lightbox
 */
window.xanhworld_closeLightbox = function() {
  const lightbox = document.getElementById('xanhworld_lightbox');
  if (lightbox) {
    lightbox.classList.remove('active');
    document.body.classList.remove('no-scroll');
  }
}

/**
 * Đặt ảnh cho Lightbox theo index
 */
window.xanhworld_setLightboxImg = function(index) {
  if (index < 0 || index >= window.lightboxImages.length) return;
  
  window.currentLightboxIndex = index;
  const lbImg = document.getElementById('xanhworld_lightbox_img');
  if (lbImg) {
    lbImg.src = window.lightboxImages[index];
    // Hiệu ứng zoom nhẹ khi đổi ảnh
    lbImg.style.transform = 'scale(0.9)';
    setTimeout(() => lbImg.style.transform = 'scale(1)', 50);
  }

  // Cập nhật thumb active trong lightbox
  document.querySelectorAll('#xanhworld_lightbox_thumbs img').forEach((img, i) => {
    img.classList.toggle('active', i === index);
  });

  // Reset zoom khi đổi ảnh
  window.xanhworld_resetLightboxZoom();
}

/**
 * Reset Zoom & Pan
 */
window.xanhworld_resetLightboxZoom = function() {
  window.lbState.scale = 1;
  window.lbState.translateX = 0;
  window.lbState.translateY = 0;
  window.xanhworld_applyLightboxTransform();
}

/**
 * Áp dụng Transform cho ảnh
 */
window.xanhworld_applyLightboxTransform = function() {
  const lbImg = document.getElementById('xanhworld_lightbox_img');
  if (lbImg) {
    lbImg.style.transform = `translate(${window.lbState.translateX}px, ${window.lbState.translateY}px) scale(${window.lbState.scale})`;
    lbImg.classList.toggle('zoomable', window.lbState.scale > 1);
  }
}

/**
 * Phóng to / Thu nhỏ
 */
window.xanhworld_zoomLightbox = function(delta) {
  const newScale = window.lbState.scale + delta;
  if (newScale >= 1 && newScale <= 5) {
    window.lbState.scale = newScale;
    if (newScale === 1) {
      window.lbState.translateX = 0;
      window.lbState.translateY = 0;
    }
    window.xanhworld_applyLightboxTransform();
  }
}

/**
 * Tải ảnh xuống
 */
window.xanhworld_downloadLightboxImg = function() {
  const src = window.lightboxImages[window.currentLightboxIndex];
  if (!src) return;

  const fileName = `xanhworld-product-${Date.now()}.jpg`;
  
  // Hiển thị thông báo đang tải
  if (typeof showCustomToast === 'function') {
    showCustomToast('Đang chuẩn bị tải ảnh...', 'info');
  }

  fetch(src)
    .then(resp => resp.blob())
    .then(blob => {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      if (typeof showCustomToast === 'function') {
        showCustomToast('Tải ảnh thành công!', 'success');
      }
    })
    .catch(() => {
      // Fallback nếu fetch bị chặn cors
      window.open(src, '_blank');
    });
}

/**
 * Toàn màn hình
 */
window.xanhworld_toggleFullscreen = function() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(err => {
      console.error(`Error attempting to enable full-screen mode: ${err.message}`);
    });
  } else {
    document.exitFullscreen();
  }
}

/**
 * Chuyển ảnh (Next/Prev)
 */
window.xanhworld_navLightbox = function(step) {
  let newIndex = window.currentLightboxIndex + step;
  if (newIndex < 0) newIndex = window.lightboxImages.length - 1;
  if (newIndex >= window.lightboxImages.length) newIndex = 0;
  window.xanhworld_setLightboxImg(newIndex);
}

/**
 * Điều chỉnh số lượng
 */
window.xanhworld_changeQty = function(delta) {
  const input = document.getElementById('xanhworld_qty');
  if (!input) return;
  
  let currentVal = parseInt(input.value);
  if (isNaN(currentVal)) currentVal = 1;
  
  let val = currentVal + delta;
  const min = parseInt(input.getAttribute('min')) || 1;
  const max = parseInt(input.getAttribute('max')) || 999;
  
  if (val < min) val = min;
  if (val > max) {
    val = max;
    if (typeof showCustomToast === 'function') {
      showCustomToast(`Rất tiếc, chúng tôi chỉ còn ${max} sản phẩm này.`, 'info');
    }
  }
  
  input.value = val;
  
  // Cập nhật input ẩn trong form nếu có
  const formQty = document.getElementById('form_quantity_input');
  if (formQty) formQty.value = val;
}

/**
 * Chọn biến thể sản phẩm
 */
window.xanhworld_selectVariant = function(btn, data) {
  // data: {id, price, sale_price, stock, sku, name}
  
  // Cập nhật UI nút
  document.querySelectorAll('.xanhworld_single_size_btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  
  // Cập nhật giá hiển thị
  const priceDisplay = document.getElementById('xanhworld_price_display');
  const oldPriceDisplay = document.getElementById('xanhworld_old_price_display');
  
  if (priceDisplay) {
    let priceText = new Intl.NumberFormat('vi-VN').format(data.sale_price || data.price) + '₫';
    priceDisplay.textContent = priceText;
    
    if (oldPriceDisplay) {
      if (data.sale_price && data.sale_price < data.price) {
        oldPriceDisplay.textContent = new Intl.NumberFormat('vi-VN').format(data.price) + '₫';
        oldPriceDisplay.style.display = 'block';
      } else {
        oldPriceDisplay.style.display = 'none';
      }
    }
  }
  
  // Cập nhật SKU
  const skuDisplay = document.getElementById('xanhworld_sku_display');
  if (skuDisplay && data.sku) {
    skuDisplay.textContent = data.sku;
  }
  
  // Cập nhật Stock info
  const stockInfo = document.getElementById('xanhworld_stock_info');
  if (stockInfo) {
    const icon = '<span class="xanhworld_single_meta_icon">📦</span>';
    const label = '<span class="xanhworld_single_meta_label">Tình trạng:</span>';
    
    if (data.stock !== null && data.stock <= 0) {
      stockInfo.innerHTML = `${icon} ${label} <span class="xanhworld_single_stock_badge out_of_stock">Hết hàng</span>`;
      const addBtn = document.querySelector('.xanhworld_single_add_btn');
      if (addBtn) {
        addBtn.disabled = true;
        addBtn.style.opacity = '0.5';
      }
    } else {
      let stockBadge = '<span class="xanhworld_single_stock_badge in_stock">Còn hàng</span>';
      let skuTag = data.sku ? `<span class="xanhworld_single_sku_tag">SKU: <span id="xanhworld_sku_display">${data.sku}</span></span>` : '';
      
      stockInfo.innerHTML = `${icon} ${label} ${stockBadge} ${skuTag}`;
      
      const addBtn = document.querySelector('.xanhworld_single_add_btn');
      if (addBtn) {
        addBtn.disabled = false;
        addBtn.style.opacity = '1';
      }
      
      // Cập nhật giới hạn cho input quantity
      const qtyInput = document.getElementById('xanhworld_qty');
      if (qtyInput && data.stock !== null) {
        qtyInput.setAttribute('max', data.stock);
        if (parseInt(qtyInput.value) > data.stock) {
          qtyInput.value = data.stock;
        }
      }
    }
  }
  
  // Cập nhật input ẩn cho form submit
  currentVariantId = data.id;
  
  const formVariantInput = document.getElementById('form_variant_id');
  if (formVariantInput) formVariantInput.value = data.id;
}

/**
 * Toggle Accordion
 */
window.xanhworld_toggleAcc = function(id) {
  const item = document.getElementById(id);
  if (!item) return;
  
  const body = item.querySelector('.xanhworld_single_accordion_body');
  const isOpen = item.classList.contains('open');
  
  if (isOpen) {
    item.classList.remove('open');
    body.style.maxHeight = '0';
  } else {
    item.classList.add('open');
    body.style.maxHeight = body.scrollHeight + 'px';
  }
}

/**
 * Xử lý yêu thích
 */
/**
 * Xử lý yêu thích dùng AJAX
 */
window.xanhworld_toggleWishlist = function(productId) {
  const wishBtn = document.getElementById('xanhworld_wish_btn');
  const saveBtn = document.getElementById('xanhworld_save_btn');
  
  // Xác định trạng thái hiện tại (dựa trên wishBtn làm chuẩn)
  const isWished = wishBtn ? wishBtn.classList.contains('xanhworld_single_wished') : false;
  
  const url = '/san-pham/yeu-thich';
  const method = isWished ? 'DELETE' : 'POST';
  
  const formData = new FormData();
  formData.append('product_id', productId);
  if (isWished) formData.append('_method', 'DELETE');

  // Thêm trạng thái loading nhẹ
  if (wishBtn) wishBtn.style.opacity = '0.5';
  if (saveBtn) saveBtn.style.opacity = '0.5';

  fetch(url, {
    method: 'POST', // Laravel sử dụng POST + _method cho DELETE
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const active = data.action === 'added';
      
      // Đồng bộ cả 2 nút
      [wishBtn, saveBtn].forEach(btn => {
        if (!btn) return;
        btn.classList.toggle('xanhworld_single_wished', active);
      });

      // Cập nhật icon/text cho wishBtn (trên ảnh)
      if (wishBtn) wishBtn.innerHTML = active ? '♥' : '♡';

      // Cập nhật text cho saveBtn (dưới form)
      if (saveBtn) {
        saveBtn.innerHTML = active ? '♥ Đã lưu vào yêu thích' : '♡ Lưu để xem sau';
      }

      if (typeof showCustomToast === 'function') {
        showCustomToast(data.message, 'success');
      }
    } else {
      if (typeof showCustomToast === 'function') {
        showCustomToast(data.message || 'Có lỗi xảy ra', 'error');
      }
    }
  })
  .catch(err => {
    console.error('Wishlist error:', err);
    if (typeof showCustomToast === 'function') {
      showCustomToast('Không thể kết nối máy chủ', 'error');
    }
  })
  .finally(() => {
    if (wishBtn) wishBtn.style.opacity = '1';
    if (saveBtn) saveBtn.style.opacity = '1';
  });
}

/**
 * Khởi tạo khi trang load xong
 */
document.addEventListener('DOMContentLoaded', function() {
  // Nạp danh sách ảnh Lightroom
  xanhworld_initLightbox();

  // Mặc định chọn variant đầu tiên
  const firstVariantBtn = document.querySelector('.xanhworld_single_size_btn');
  if (firstVariantBtn) {
    console.log('single.js: Selecting first variant');
    // Trực tiếp gọi hàm thay vì click() để chắc chắn hơn
    try {
      const data = JSON.parse(firstVariantBtn.getAttribute('data-variant'));
      xanhworld_selectVariant(firstVariantBtn, data);
    } catch(e) {
      console.error('Error selecting first variant:', e);
    }
  }

  // Mặc định mở accordion đầu tiên
  const firstAcc = document.querySelector('.xanhworld_single_accordion_item');
  if (firstAcc) {
    xanhworld_toggleAcc(firstAcc.id);
  }
  
  // Lắng nghe click toàn cục để xử lý (Event Delegation)
  document.addEventListener('click', function(e) {
    const qtyBtn = e.target.closest('[data-qty]');
    if (qtyBtn) {
      console.log('single.js: Qty button clicked', qtyBtn.getAttribute('data-qty'));
      const isPlus = qtyBtn.getAttribute('data-qty') === 'plus';
      xanhworld_changeQty(isPlus ? 1 : -1);
    }

    // Mở Lightbox khi nhấn vào ảnh chính
    const mainImgContainer = e.target.closest('.xanhworld_single_gallery_main');
    if (mainImgContainer && !e.target.closest('.xanhworld_single_gallery_wishlist')) {
      xanhworld_openLightbox();
    }

    // Nút đóng Lightbox
    if (e.target.closest('.xanhworld_lightbox_close') || e.target.closest('.xanhworld_lightbox_overlay')) {
      xanhworld_closeLightbox();
    }

    // Điều hướng Lightbox
    const navBtn = e.target.closest('.xanhworld_lightbox_nav');
    if (navBtn) {
      const isNext = navBtn.classList.contains('next');
      xanhworld_navLightbox(isNext ? 1 : -1);
    }

    // Thanh công cụ Lightbox
    const toolBtn = e.target.closest('.xanhworld_lightbox_tool_btn');
    if (toolBtn) {
      const tool = toolBtn.getAttribute('data-tool');
      switch (tool) {
        case 'zoom-in': xanhworld_zoomLightbox(0.5); break;
        case 'zoom-out': xanhworld_zoomLightbox(-0.5); break;
        case 'reset': xanhworld_resetLightboxZoom(); break;
        case 'download': xanhworld_downloadLightboxImg(); break;
        case 'fullscreen': xanhworld_toggleFullscreen(); break;
      }
    }

    const variantBtn = e.target.closest('.xanhworld_single_size_btn');
    if (variantBtn && variantBtn.hasAttribute('data-variant')) {
      try {
        const data = JSON.parse(variantBtn.getAttribute('data-variant'));
        xanhworld_selectVariant(variantBtn, data);
      } catch(err) {
        console.error('Variant data error:', err);
      }
    }

    const wishBtn = e.target.closest('[data-wishlist-id]');
    if (wishBtn) {
      const productId = wishBtn.getAttribute('data-wishlist-id');
      xanhworld_toggleWishlist(productId);
    }

    const thumb = e.target.closest('.xanhworld_single_gallery_thumbs img');
    if (thumb) {
      const src = thumb.getAttribute('data-thumb-src');
      xanhworld_changeImg(thumb, src);
      
      // Mở Lightbox ngay tại ảnh vừa click
      const thumbs = Array.from(document.querySelectorAll('.xanhworld_single_gallery_thumbs img'));
      const index = thumbs.indexOf(thumb);
      xanhworld_openLightbox(index >= 0 ? index : 0);
    }
  });

  // Hỗ trợ phím Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      xanhworld_closeLightbox();
    }
    if (document.getElementById('xanhworld_lightbox').classList.contains('active')) {
      if (e.key === 'ArrowRight') xanhworld_navLightbox(1);
      if (e.key === 'ArrowLeft') xanhworld_navLightbox(-1);
      if (e.key === '+') xanhworld_zoomLightbox(0.5);
      if (e.key === '-') xanhworld_zoomLightbox(-0.5);
      if (e.key.toLowerCase() === 'd') xanhworld_downloadLightboxImg();
      if (e.key.toLowerCase() === 'f') xanhworld_toggleFullscreen();
      if (e.key.toLowerCase() === 'r') xanhworld_resetLightboxZoom();
    }
  });

  // Hỗ trợ Pan (kéo ảnh)
  const lbImg = document.getElementById('xanhworld_lightbox_img');
  if (lbImg) {
    lbImg.addEventListener('mousedown', e => {
      if (window.lbState.scale > 1) {
        window.lbState.isDragging = true;
        window.lbState.startX = e.clientX - window.lbState.translateX;
        window.lbState.startY = e.clientY - window.lbState.translateY;
        lbImg.classList.add('grabbing');
      }
    });

    window.addEventListener('mousemove', e => {
      if (window.lbState.isDragging) {
        window.lbState.translateX = e.clientX - window.lbState.startX;
        window.lbState.translateY = e.clientY - window.lbState.startY;
        window.xanhworld_applyLightboxTransform();
      }
    });

    window.addEventListener('mouseup', () => {
      window.lbState.isDragging = false;
      if (lbImg) lbImg.classList.remove('grabbing');
    });

    // Hỗ trợ Wheel Zoom
    lbImg.addEventListener('wheel', e => {
      e.preventDefault();
      const delta = e.deltaY > 0 ? -0.2 : 0.2;
      window.xanhworld_zoomLightbox(delta);
    }, { passive: false });
  }
  // Lắng nghe thay đổi input số lượng
  const qtyInput = document.getElementById('xanhworld_qty');
  if (qtyInput) {
    qtyInput.addEventListener('change', function() {
      xanhworld_changeQty(0);
    });
    // Chặn nhập ký tự không phải số (ngoại trừ các phím điều hướng)
    qtyInput.addEventListener('keypress', function(e) {
      if (e.which < 48 || e.which > 57) {
        if (e.which !== 8 && e.which !== 0) { // Backspace and other special keys
            e.preventDefault();
        }
      }
    });
  }

  // Xử lý AJAX thêm vào giỏ hàng
  const cartForm = document.querySelector('.action-buttons-form');
  if (cartForm) {
    console.log('single.js: Cart form found, adding listener');
    cartForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const submitBtn = cartForm.querySelector('.xanhworld_single_add_btn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner">Đang xử lý...</span>';
      }

      const formData = new FormData(cartForm);
      
      fetch(cartForm.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      })
      .then(response => {
        if (!response.ok) {
           return response.json().then(err => { throw err; });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          if (typeof showCustomToast === 'function') {
            showCustomToast(data.message || 'Đã thêm vào giỏ hàng!', 'success');
          } else {
            alert(data.message || 'Đã thêm vào giỏ hàng!');
          }

          // Cập nhật số lượng trên header
          console.log('single.js: Updating cart count to', data.cart_total_items);
          const cartCountEls = document.querySelectorAll('.xanhworld_header_main_icon_cart_count');
          cartCountEls.forEach(el => {
            if (el) {
              el.textContent = data.cart_total_items;
              // Thêm hiệu ứng nháy nhẹ để báo hiệu sự thay đổi
              el.style.animation = 'none';
              setTimeout(() => el.style.animation = 'pulse 0.5s', 10);
            }
          });
        } else {
          throw data;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        const errorMsg = error.message || (error.errors ? Object.values(error.errors)[0][0] : 'Có lỗi xảy ra khi thêm vào giỏ hàng.');
        if (typeof showCustomToast === 'function') {
          showCustomToast(errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = submitBtn.originalText;
        }
      });
    });
  }
});