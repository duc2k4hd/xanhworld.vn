<!-- Call to action -->

<section class="xanhworld_main_newsletter_banner_section">
    <div class="xanhworld_main_newsletter_banner">
        <div class="xanhworld_main_newsletter_banner_content">
            <h2 class="xanhworld_main_newsletter_banner_title">
                Đăng ký nhận bản tin
            </h2>
            <p class="xanhworld_main_newsletter_banner_desc">
                Nhận thông tin mới nhất về sản phẩm, xu hướng CÂY CẢNH, CÂY PHONG THỦY và ưu đãi độc quyền từ {{ $setting->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}.
            </p>
            <form action="{{ route('client.newsletter.subscription') }}" method="POST" class="xanhworld_main_newsletter_banner_form">
                @csrf
                <input value="{{ old('xanhworld_main_newsletter_email') }}" type="email" name="xanhworld_main_newsletter_email" class="xanhworld_main_newsletter_banner_input" placeholder="Nhập email của bạn..." required>
                <small>@error('xanhworld_main_newsletter_email') {{ $message }} @enderror</small>
                <button type="submit" class="xanhworld_main_newsletter_banner_btn">
                    Đăng ký
                </button>
            </form>
        </div>
        <div class="xanhworld_main_newsletter_banner_img">
            <img
                loading="lazy"
                src="{{ asset('clients/assets/img/banners/dang-ky-nhan-ban-tin-THE-GIOI-CAY-XANH-XWORLD.jpg') }}"
                alt="Nhận thông tin mới nhất từ {{ $setting->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}"
            >
        </div>
    </div>
</section>

