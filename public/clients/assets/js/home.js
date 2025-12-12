(async () => {
    // === SLIDER CHÍNH ===
const sliderTrack =
    document.querySelector(".xanhworld_main_slider_main_slider_track") ||
    document.querySelector(".xanhworld_main_slider_track");

    const slides = document.querySelectorAll(
        ".xanhworld_main_slider_main_slide, .xanhworld_main_slider_item"
    );

    // === Tạo dots tự động ===
    const dotsContainer = document.querySelector(".xanhworld_main_slider_main_dots");

    if (dotsContainer) {
        dotsContainer.innerHTML = "";
        slides.forEach((_, idx) => {
            const dot = document.createElement("button");
            dot.className = "xanhworld_main_slider_dot";
            if (idx === 0) dot.classList.add("xanhworld_main_slider_dot_active");
            dotsContainer.appendChild(dot);
        });
    }

    const dots = document.querySelectorAll(
        ".xanhworld_main_slider_main_dots .xanhworld_main_slider_dot"
    );

    let currentSlide = 0;
    let autoSlide;
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let animationID;

    // ---- Update Slider ----
    const updateSlider = () => {
        if (!sliderTrack || slides.length === 0) return;

        dots.forEach(dot => dot.classList.remove("xanhworld_main_slider_dot_active"));
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add("xanhworld_main_slider_dot_active");
        }

        sliderTrack.style.transition = "transform .35s ease";
        sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;

        prevTranslate = -currentSlide * sliderTrack.offsetWidth;
    };

    // ---- Auto Slide ----
    const startAuto = () => {
        stopAuto();
        autoSlide = setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            updateSlider();
        }, 5000);
    };

    const stopAuto = () => clearInterval(autoSlide);

    // ---- BUTTONS ----
    document.querySelector(".xanhworld_main_slider_prev")?.addEventListener("click", () => {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlider();
        startAuto();
    });

    document.querySelector(".xanhworld_main_slider_next")?.addEventListener("click", () => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
        startAuto();
    });

    // ---- DOTS ----
    dots.forEach((dot, idx) => {
        dot.addEventListener("click", () => {
            currentSlide = idx;
            updateSlider();
            startAuto();
        });
    });

    // ---- DRAG & TOUCH ----
    const touchStart = (x) => {
        isDragging = true;
        startPos = x - prevTranslate;
        sliderTrack.style.transition = "none";
        stopAuto();
    };

    const touchMove = (x) => {
        if (!isDragging) return;
        currentTranslate = x - startPos;
        sliderTrack.style.transform = `translateX(${currentTranslate}px)`;
    };

    const touchEnd = () => {
        if (!isDragging) return;
        isDragging = false;

        const moved = currentTranslate - prevTranslate;
        const threshold = sliderTrack.offsetWidth * 0.2; // 20% width

        if (moved < -threshold) currentSlide = Math.min(currentSlide + 1, slides.length - 1);
        if (moved > threshold) currentSlide = Math.max(currentSlide - 1, 0);

        updateSlider();
        startAuto();
    };

    // ---- MOUSE EVENTS ----
    sliderTrack.addEventListener("mousedown", e => touchStart(e.clientX));
    sliderTrack.addEventListener("mousemove", e => touchMove(e.clientX));
    sliderTrack.addEventListener("mouseup", touchEnd);
    sliderTrack.addEventListener("mouseleave", touchEnd);

    // ---- TOUCH EVENTS ----
    sliderTrack.addEventListener("touchstart", e => touchStart(e.touches[0].clientX));
    sliderTrack.addEventListener("touchmove", e => touchMove(e.touches[0].clientX));
    sliderTrack.addEventListener("touchend", touchEnd);

    // ---- START ----
    if (sliderTrack && slides.length > 1) startAuto();


    // Set thời gian kết thúc Flash Sale (ví dụ lấy từ DB)
    const endTime = typeof timeFlashSale !== "undefined"
        ? Number(timeFlashSale)
        : null;

    // Lấy các phần tử hiển thị
    const daysEl = document.querySelector(".xanhworld_flash_sale_timer_days");
    const hoursEl = document.querySelector(
        ".xanhworld_flash_sale_timer_hours"
    );
    const minutesEl = document.querySelector(
        ".xanhworld_flash_sale_timer_minutes"
    );
    const secondsEl = document.querySelector(
        ".xanhworld_flash_sale_timer_seconds"
    );

    // Lưu giá trị trước đó để so sánh
    let prevDays, prevHours, prevMinutes, prevSeconds;

    function updateTimer() {
        const now = new Date().getTime();
        let distance = endTime - now;

        if (distance <= 0) {
            daysEl.textContent = "00";
            hoursEl.textContent = "00";
            minutesEl.textContent = "00";
            secondsEl.textContent = "00";
            clearInterval(interval);
            return;
        }

        let days = Math.floor(distance / (1000 * 60 * 60 * 24));
        let hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Format 2 chữ số
        days = String(days).padStart(2, "0");
        hours = String(hours).padStart(2, "0");
        minutes = String(minutes).padStart(2, "0");
        seconds = String(seconds).padStart(2, "0");

        // Chỉ animate khi giá trị thay đổi
        if (seconds !== prevSeconds) animateFlip(secondsEl, seconds);
        if (minutes !== prevMinutes) animateFlip(minutesEl, minutes);
        if (hours !== prevHours) animateFlip(hoursEl, hours);
        if (days !== prevDays) animateFlip(daysEl, days);

        // Cập nhật giá trị trước đó
        prevDays = days;
        prevHours = hours;
        prevMinutes = minutes;
        prevSeconds = seconds;
    }

    // Hàm gán text + trigger animation
    function animateFlip(el, newValue) {
        el.textContent = newValue;
        el.classList.remove("flip-animate");
        void el.offsetWidth; // reset
        el.classList.add("flip-animate");
    }

    if (endTime && !Number.isNaN(endTime)) {
        const interval = setInterval(updateTimer, 1000);
        updateTimer();
    }

    handleFlashSale();

})();


[
    '.xanhworld_header_main_search_select',
].forEach(selector => {

    document.querySelectorAll(selector)?.forEach(el => {
        if (typeof SlimSelect === "function") {
            new SlimSelect({ select: el });
        } else {
            console.warn("SlimSelect is not available; skipping select enhancement.");
        }
    });

});

const emblaHomeSlider = document.querySelector(".xanhworld_main_slider_main_slider_track");
EmblaCarousel(emblaHomeSlider, { loop: false, dragFree: false })

const emblaCategoriesList = document.querySelector(".xanhworld_main_categories_viewport");
EmblaCarousel(emblaCategoriesList, { 
    dragFree: true,
    align: 'start',
    containScroll: 'trimSnaps',
    loop: false
 })