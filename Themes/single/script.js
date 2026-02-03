// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== THUMBNAIL GALLERY SCROLL FUNCTIONALITY =====
    const thumbnailGallery = document.querySelector('.thumbnail-gallery');
    const scrollUpBtn = document.querySelector('.scroll-up');
    const scrollDownBtn = document.querySelector('.scroll-down');
    
    // Scroll amount per click (height of one thumbnail + gap)
    const scrollAmount = 88; // 80px thumbnail + 8px gap
    
    // Update scroll buttons visibility
    function updateScrollButtons() {
        if (thumbnailGallery) {
            const atTop = thumbnailGallery.scrollTop === 0;
            const atBottom = Math.ceil(thumbnailGallery.scrollTop + thumbnailGallery.clientHeight) >= thumbnailGallery.scrollHeight;
            
            if (scrollUpBtn) scrollUpBtn.disabled = atTop;
            if (scrollDownBtn) scrollDownBtn.disabled = atBottom;
        }
    }
    
    // Scroll up button
    if (scrollUpBtn) {
        scrollUpBtn.addEventListener('click', function() {
            thumbnailGallery.scrollBy({
                top: -scrollAmount,
                behavior: 'smooth'
            });
        });
    }
    
    // Scroll down button
    if (scrollDownBtn) {
        scrollDownBtn.addEventListener('click', function() {
            thumbnailGallery.scrollBy({
                top: scrollAmount,
                behavior: 'smooth'
            });
        });
    }
    
    // Update buttons on scroll
    if (thumbnailGallery) {
        thumbnailGallery.addEventListener('scroll', updateScrollButtons);
        // Initial check
        updateScrollButtons();
    }
    
    // Update on window resize
    window.addEventListener('resize', updateScrollButtons);
    
    // ===== IMAGE GALLERY FUNCTIONALITY =====
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            const newImageSrc = this.getAttribute('data-image');
            mainImage.src = newImageSrc;
        });
    });
    
    // ===== TABS FUNCTIONALITY =====
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // ===== COLOR SELECTION =====
    const colorButtons = document.querySelectorAll('.color-btn');
    
    colorButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all color buttons
            colorButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
        });
    });
    
    // ===== QUANTITY CONTROLS =====
    const qtyInput = document.querySelector('.qty-input');
    const minusBtn = document.querySelector('.qty-btn.minus');
    const plusBtn = document.querySelector('.qty-btn.plus');
    
    minusBtn.addEventListener('click', function() {
        let currentValue = parseInt(qtyInput.value);
        if (currentValue > 1) {
            qtyInput.value = currentValue - 1;
        }
    });
    
    plusBtn.addEventListener('click', function() {
        let currentValue = parseInt(qtyInput.value);
        const maxStock = 999; // Maximum stock
        if (currentValue < maxStock) {
            qtyInput.value = currentValue + 1;
        }
    });
    
    // Prevent invalid input
    qtyInput.addEventListener('input', function() {
        let value = parseInt(this.value);
        
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 999) {
            this.value = 999;
        }
    });
    
    // ===== ADD TO CART FUNCTIONALITY =====
    const addToCartBtn = document.querySelector('.btn-cart');
    const buyNowBtn = document.querySelector('.btn-buy');
    
    addToCartBtn.addEventListener('click', function() {
        const quantity = qtyInput.value;
        const selectedColor = document.querySelector('.color-btn.active');
        
        // Show confirmation message
        alert(`Đã thêm ${quantity} sản phẩm vào giỏ hàng!`);
        
        // Here you would typically send data to backend
        console.log('Added to cart:', {
            quantity: quantity,
            color: selectedColor ? selectedColor.style.background : 'default'
        });
    });
    
    buyNowBtn.addEventListener('click', function() {
        const quantity = qtyInput.value;
        const selectedColor = document.querySelector('.color-btn.active');
        
        // Show confirmation message
        alert(`Đang chuyển đến trang thanh toán...`);
        
        // Here you would typically redirect to checkout
        console.log('Buy now:', {
            quantity: quantity,
            color: selectedColor ? selectedColor.style.background : 'default'
        });
    });
    
    // ===== PRODUCT CARD INTERACTIONS =====
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            console.log('Product card clicked');
            // Here you would typically navigate to product detail page
        });
    });
    
    // ===== SMOOTH SCROLL FOR TABS =====
    const tabsSection = document.querySelector('.product-tabs');
    if (tabsSection) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Smooth scroll to tabs section on mobile
                if (window.innerWidth < 768) {
                    tabsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }
    
    // ===== REVIEW STARS ANIMATION =====
    const reviewStars = document.querySelectorAll('.review-stars');
    
    // Add hover effect for star ratings (if you want interactive rating)
    reviewStars.forEach(stars => {
        stars.style.cursor = 'pointer';
    });
    
    // ===== RATING BARS ANIMATION =====
    const ratingBars = document.querySelectorAll('.bar-fill');
    
    // Animate bars on page load
    const animateBars = function() {
        ratingBars.forEach(bar => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 100);
        });
    };
    
    // Create Intersection Observer for animation when scrolling into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateBars();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    const ratingSummary = document.querySelector('.rating-summary');
    if (ratingSummary) {
        observer.observe(ratingSummary);
    }
    
    // ===== STICKY PRODUCT INFO ON SCROLL (Desktop) =====
    const rightColumn = document.querySelector('.right-column');
    const productInfo = document.querySelector('.product-info');
    
    if (window.innerWidth > 992) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const containerTop = document.querySelector('.container').offsetTop;
            
            if (scrollTop > containerTop) {
                productInfo.style.top = '20px';
            }
        });
    }
    
    // ===== FORM VALIDATION =====
    qtyInput.addEventListener('blur', function() {
        if (this.value === '' || parseInt(this.value) < 1) {
            this.value = 1;
        }
    });
    
    // ===== PREVENT DEFAULT FORM SUBMISSION =====
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    });
    
    // ===== LAZY LOADING FOR IMAGES (Optional Enhancement) =====
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // ===== RESPONSIVE MENU TOGGLE (if needed for mobile) =====
    const handleResize = function() {
        if (window.innerWidth < 992) {
            // Mobile adjustments
            productInfo.style.position = 'static';
        } else {
            // Desktop adjustments
            productInfo.style.position = 'sticky';
        }
    };
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Call on load
    
    // ===== CONSOLE LOG FOR DEBUGGING =====
    console.log('Product Detail Page Initialized');
    console.log('Total thumbnails:', thumbnails.length);
    console.log('Total tabs:', tabButtons.length);
    console.log('Total color options:', colorButtons.length);
});

// ===== UTILITY FUNCTIONS =====

// Format price with Vietnamese currency
function formatPrice(price) {
    return '₫' + price.toLocaleString('vi-VN');
}

// Calculate discount percentage
function calculateDiscount(originalPrice, currentPrice) {
    return Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
}

// Update stock display
function updateStockDisplay(stock) {
    const stockInfo = document.querySelector('.stock-info');
    if (stockInfo) {
        if (stock > 0) {
            stockInfo.textContent = `Còn ${stock} sản phẩm`;
            stockInfo.style.color = '#00b14f';
        } else {
            stockInfo.textContent = 'Hết hàng';
            stockInfo.style.color = '#ee4d2d';
        }
    }
}

// Show notification toast
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style toast
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '15px 25px',
        background: type === 'success' ? '#00b14f' : '#ee4d2d',
        color: 'white',
        borderRadius: '6px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        animation: 'slideIn 0.3s ease-out'
    });
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);