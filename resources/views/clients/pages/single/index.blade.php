@extends('clients.layouts.master')

@section('title', $product->meta_title .' | THẾ GIỚI CÂY XANH XWORLD' ?? ($product->name ? ($product->name. ' | THẾ GIỚI CÂY XANH XWORLD') : 'THẾ GIỚI CÂY XANH XWORLD - Chi tiết sản phẩm'))

@push('css_page')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/single.css') }}">
@endpush

@push('js_page')
    <script defer src="{{ asset('clients/assets/js/single.js') }}"></script>
@endpush

@section('head')
    @if ($product?->primaryImage?->url)
        <link rel="preload"
              as="image"
              href="/resize?url=clients/assets/img/clothes/{{ $product->primaryImage->url }}&width=400&height=400"
              fetchpriority="high">
    @else
        <link rel="preload" as="image" href="{{ asset('clients/assets/img/clothes/no-image.webp') }}"
            fetchpriority="high">
    @endif

    <meta name="robots" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>
    <meta name="keywords" content="{{ is_array($product->meta_keywords ?? null) ? implode(', ', $product->meta_keywords) : 'cây xanh, chậu cây, phụ kiện decor, cây phong thủy, cây văn phòng, THẾ GIỚI CÂY XANH XWORLD' }}">

    <meta name="description"
        content="{{ $product->meta_desc ?? ($product->meta_title ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD: Cây xanh, chậu cảnh, phụ kiện decor. Giao tận nơi, bảo hành cây khỏe, setup góc làm việc, ban công, sân vườn xanh mát.')) }}">

    <meta property="og:title"
        content="{{ $product->meta_title ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD - Cây xanh & phụ kiện decor') }}">
    <meta property="og:description"
        content="{{ $product->meta_desc ?? 'THẾ GIỚI CÂY XANH XWORLD: Cây xanh, chậu cảnh, phụ kiện trang trí. Hướng dẫn setup góc làm việc, ban công, sân vườn xanh mát, giao tận nơi.' }}">
    <meta property="og:url"
        content="{{ $product->canonical_url ?? ($settings->site_url ?? 'https://xanhworld.vn') . '/san-pham/' . ($product->slug ?? '') }}">
    <meta property="og:image"
        content="{{ asset('clients/assets/img/business/' . ($settings->site_banner ?? null ? $settings->site_banner : $settings->site_logo ?? 'logo-xworld.png')) }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"
        content="{{ $product->primaryImage->title ?? null ?: $product->name ?? ($settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD') }}">
    <meta property="og:image:type" content="image/webp">
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}">
    <meta property="og:locale" content="vi_VN">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}">
    <meta name="twitter:title"
        content="{{ $product->meta_title ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD - Cây xanh & phụ kiện decor') }}">
    <meta name="twitter:description"
        content="{{ $product->meta_desc ?? 'THẾ GIỚI CÂY XANH XWORLD: Giao cây tận nơi, tư vấn chăm sóc, setup góc làm việc / ban công xanh mát.' }}">
    <meta name="twitter:image"
        content="{{ asset('clients/assets/img/clothes/' . ($product?->primaryImage?->url ?? 'no-image.webp')) }}">
    <meta name="twitter:creator" content="{{ $settings->seo_author ?? 'THẾ GIỚI CÂY XANH XWORLD' }}">

    @php
        $productUrl = ($settings->site_url ?? 'https://xanhworld.vn').'/san-pham/'.($product->slug ?? '');
    @endphp
    <link rel="canonical" href="{{ $productUrl }}">
    <link rel="alternate" hreflang="vi" href="{{ $productUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $productUrl }}">
@endsection

@section('schema')
    @include('clients.templates.schema_product')
@endsection


@section('content')
    @php
        $includedSets = collect($includedProducts ?? []);
    @endphp
    <main class="xanhworld_single">
        <!-- Breadcrumb -->
        <section>
            @php
                // Lấy danh mục cuối cùng của sản phẩm
                $categoryBreadcrumb = $product?->primaryCategory?->first();

                // Truy ngược lên cha để tạo breadcrumb path
                $breadcrumbPath = collect();
                while ($categoryBreadcrumb) {
                    $breadcrumbPath->prepend($categoryBreadcrumb); // đưa vào đầu mảng
                    $categoryBreadcrumb = $categoryBreadcrumb->parent;
                }
            @endphp

            <div class="xanhworld_single_breadcrumb">
                <a href="{{ url('/') }}">Trang chủ</a>
                <span class="separator">>></span>

                @if ($breadcrumbPath->isNotEmpty())
                    @foreach ($breadcrumbPath as $breadcrumb)
                        <a href="{{ route('client.product.category.index', $breadcrumb->slug) }}">{{ $breadcrumb->name }}</a>
                        <span class="separator">>></span>
                    @endforeach
                @endif

                <span class="breadcrumb-current">{{ $product->name }}</span>
            </div>
        </section>

        @php
            $listImg = [];
        @endphp

        <!-- Thông tin sản phẩm -->
        <section>
            <div class="xanhworld_single_info">
                <div class="xanhworld_single_info_images">
                    <div class="xanhworld_single_info_images_main">
                        <img loading="eager" fetchpriority="high" width="400" height="400" decoding="async"
                            srcset="
                                /resize?url=clients/assets/img/clothes/{{ $product?->primaryImage?->url ?? 'no-image.webp' }}&width=400&height=400 400w
                            "
                            sizes="(max-width: 1050px) 400px, 400px"
                            src="{{ asset('clients/assets/img/clothes/' . ($product?->primaryImage?->url ?? 'no-image.webp')) }}"
                            alt="{{ $product->primaryImage->alt ?? null ?: ($product->name ?? 'NOBI FASHION') }}"
                            title="{{ $product->primaryImage->title ?? null ?: ($product->name ?? 'NOBI FASHION') }}"
                            class="xanhworld_single_info_images_main_image"
                            data-default-src="{{ asset('clients/assets/img/clothes/' . ($product?->primaryImage?->url ?? 'no-image.webp')) }}">
                    </div>
                    <div class="xanhworld_single_info_images_gallery">
                        @if ($product->images && $product->images->count() > 0)
                            @foreach ($product->images as $img)
                                <img data-src="{{ asset('clients/assets/img/clothes/' . ($img->url ?? 'no-image.webp')) }}"
                                     width="80" height="80"
                                     decoding="async"
                                     src="{{ asset('clients/assets/img/clothes/' . ($img->url ?? 'no-image.webp')) }}"
                                
                                     srcset="
                                        /resize?url=clients/assets/img/clothes/{{ $img->url ?? 'no-image.webp' }}&width=85&height=85 85w
                                     "
                                
                                     sizes="(max-width: 1050px) 85px, 85px"
                                
                                     alt="{{ $img->alt ?? ($product->name ?? 'NOBI FASHION') }}"
                                     title="{{ $img->title ?? ($product->name ?? 'NOBI FASHION') }}"
                                     class="xanhworld_single_info_images_gallery_image {{ $img->is_primary ? 'xanhworld_single_info_images_gallery_image_active' : '' }}">


                                @php
                                    $listImg[] = asset('clients/assets/img/clothes/' . ($img->url ?? 'no-image.webp'));
                                @endphp
                            @endforeach
                        @endif
                    </div>
                    @php
                        $overlayImages = ($product->images && $product->images->count() > 0)
                            ? $product->images
                            : collect([$product->primaryImage]);
                    @endphp
                    <div class="xanhworld_single_info_images_main_overlay">
                        <div class="xanhworld_single_info_images_main_overlay_images">
                            @forelse ($overlayImages as $img)
                                <div class="xanhworld_single_info_images_main_overlay_image">
                                    <img src="{{ asset('clients/assets/img/clothes/' . ($img->url ?? 'no-image.webp')) }}"
                                         alt="{{ $img->alt ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD') }}"
                                         loading="lazy">
                                </div>
                            @empty
                                <div class="xanhworld_single_info_images_main_overlay_image">
                                    <img src="{{ asset('clients/assets/img/clothes/no-image.webp') }}"
                                         alt="{{ $product->name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}">
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="xanhworld_single_info_images_support">
                        <form class="xanhworld_single_info_images_support_form" id="phone-request-form" method="POST">
                            @csrf
                            <div class="xanhworld_single_info_images_support_form_group">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="text" 
                                    placeholder="Nhập số điện thoại để được tư vấn (NOBI FASHION)."
                                    name="phone" 
                                    id="phone-input"
                                    class="xanhworld_single_info_images_support_form_group_input"
                                    required
                                    pattern="[0-9]{10,11}"
                                    maxlength="11">
                                <button type="submit" class="xanhworld_single_info_images_support_form_group_btn" id="phone-submit-btn">
                                    <span class="btn-text">Gửi yêu cầu</span>
                                    <span class="btn-loading" style="display: none;">Đang gửi...</span>
                                </button>
                            </div>
                            <div class="xanhworld_single_info_images_support_form_notice">
                                <p class="xanhworld_single_info_images_support_form_notice_text">Để lại số điện thoại,
                                    chúng tôi sẽ tư vấn cho bạn.</p>
                                <div id="phone-request-message" style="display: none; margin-top: 10px; padding: 8px; border-radius: 4px; font-size: 13px;"></div>
                            </div>
                        </form>
                    </div>
                </div>

                @php
                    $item = $product->isInFlashSale() ? $product->currentFlashSaleItem()->first() : $product;

                    $original = $item->original_price ?? ($item->price ?? 0);
                    $sale = $item->sale_price ?? 0;
                    // dd($product->currentFlashSale()->first())
                    $availableStock = max(0, (int) ($quantityProductDetail ?? 0));
                    $isOutOfStock = $availableStock <= 0;
                @endphp

                <div class="xanhworld_single_info_specifications">
                    @if ($product->isInFlashSale())
                        <script>
                            const endTime = new Date("{{ optional($product->currentFlashSale()->first())->end_time }}").getTime();
                        </script>
                        <div class="xanhworld_single_info_specifications_deal">
                            <div class="xanhworld_single_info_specifications_label">
                                ⚡ SĂN DEAL
                            </div>
                            @php
                                $stock = (int) ($item->stock ?? 0);
                                $sold = (int) ($item->sold ?? 0);
                                $percentage = $stock > 0 ? min(100, round(($sold / $stock) * 100)) : 0;
                            @endphp

                            <div class="xanhworld_single_info_specifications_progress">
                                <div class="xanhworld_single_info_specifications_progress_bar"
                                    style="width: {{ $percentage }}%;"></div>
                            </div>
                            <div class="xanhworld_single_info_specifications_time">
                                <span class="xanhworld_single_info_specifications_end_time">Kết thúc trong</span>
                                <div class="xanhworld_single_info_specifications_countdown">
                                    <div
                                        class="xanhworld_single_info_specifications_box xanhworld_single_info_specifications_box_days">
                                        00</div>
                                    <span>:</span>
                                    <div
                                        class="xanhworld_single_info_specifications_box xanhworld_single_info_specifications_box_house">
                                        00</div>
                                    <span>:</span>
                                    <div
                                        class="xanhworld_single_info_specifications_box xanhworld_single_info_specifications_box_minute">
                                        00</div>
                                    <span>:</span>
                                    <div
                                        class="xanhworld_single_info_specifications_box xanhworld_single_info_specifications_box_second">
                                        00</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <h1 class="xanhworld_single_info_specifications_title">
                        {{ $product->name ?? 'Sản phẩm thời trang chính hãng - NOBI FASHION' }}</h1>

                    <div class="xanhworld_single_info_specifications_brand">
                        <!-- Thương hiệu + Mã sản phẩm -->
                        <div class="xanhworld_single_info_specifications_brand_left">
                            <span>Mã tìm kiếm:
                                <strong
                                    class="xanhworld_single_info_specifications_brand_code">{{ $product->sku }}</strong>
                            </span>
                        </div>

                        <!-- Đánh giá -->
                        <div class="xanhworld_single_info_specifications_brand_right">
                            <span class="xanhworld_single_info_specifications_brand_stars">
                                @php
                                    $avg = $ratingStats['average_rating'] ?? 0;
                                    $hasReal = ($ratingStats['total_comments'] ?? 0) > 0 && $avg > 0;
                                    $star = $hasReal ? max(1, min(5, (int) round($avg))) : rand(4, 5);

                                    for ($i = 1; $i <= $star; $i++) {
                                        if ($star == 4) {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="10" width="10" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#FFD43B" d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"/></svg>';

                                            if ($i == 4) {
                                                echo '<svg xmlns="http://www.w3.org/2000/svg" height="10" width="10" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#FFD43B" d="M320.1 417.6C330.1 417.6 340 419.9 349.1 424.6L423.5 462.5L410.5 380C407.3 359.8 414 339.3 428.4 324.8L487.4 265.7L404.9 252.6C384.7 249.4 367.2 236.7 357.9 218.5L319.9 144.1L319.9 417.7zM489.4 553C482.1 558.3 472.4 559.1 464.4 555L320.1 481.6L175.8 555C167.8 559.1 158.1 558.3 150.8 553C143.5 547.7 139.8 538.8 141.2 529.8L166.4 369.9L52 255.4C45.6 249 43.4 239.6 46.2 231C49 222.4 56.3 216.1 65.3 214.7L225.2 189.3L298.8 45.1C302.9 37.1 311.2 32 320.2 32C329.2 32 337.5 37.1 341.6 45.1L415 189.3L574.9 214.7C583.8 216.1 591.2 222.4 594 231C596.8 239.6 594.5 249 588.2 255.4L473.7 369.9L499 529.8C500.4 538.7 496.7 547.7 489.4 553z"/></svg>';
                                                break;
                                            }
                                        }
                                        if ($star == 5) {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="10" width="10" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#FFD43B" d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"/></svg>';
                                        }
                                    }
                                @endphp
                            </span>
                            @php
                                $realCount = $ratingStats['total_comments'] ?? 0;
                                $displayCount = $realCount > 0 ? $realCount : rand(10, 1000);
                            @endphp
                            <span onclick="tabReview()" class="xanhworld_single_info_specifications_brand_reviews">
                                (<a href="#xanhworld_review">{{ $displayCount }} đánh giá</a>)
                            </span>
                        </div>
                    </div>

                    {{-- Giá sản phẩm --}}

                    <p class="xanhworld_single_info_specifications_price">
                        @if ($original > 0)
                            @if ($sale > 0 && $sale < $original)
                                {{-- Có giá khuyến mãi hợp lệ --}}
                                <meta content="VND">
                                <span class="xanhworld_single_info_specifications_new_price">
                                    {{ number_format($sale, 0, ',', '.') }}₫
                                </span>

                                <meta content="2025-12-31" />
                                <span class="xanhworld_single_info_specifications_old_price"
                                    style="text-decoration:line-through;">
                                    {{ number_format($original, 0, ',', '.') }}₫
                                </span>

                                {{-- Tính % giảm --}}
                                <span class="xanhworld_single_info_specifications_sale">
                                    -{{ round((($original - $sale) / $original) * 100) }}%
                                </span>
                            @else
                                {{-- Không có sale, chỉ hiển thị giá gốc --}}
                                <meta content="2025-12-31" />
                                <span class="xanhworld_single_info_specifications_new_price">
                                    {{ number_format($original, 0, ',', '.') }}₫
                                </span>
                                <span class="xanhworld_single_info_specifications_sale">
                                    <svg style="width: 35px; height: 35px;" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path fill="#fff"
                                            d="M434.8 54.1C446.7 62.7 451.1 78.3 445.7 91.9L367.3 288L512 288C525.5 288 537.5 296.4 542.1 309.1C546.7 321.8 542.8 336 532.5 344.6L244.5 584.6C233.2 594 217.1 594.5 205.2 585.9C193.3 577.3 188.9 561.7 194.3 548.1L272.7 352L128 352C114.5 352 102.5 343.6 97.9 330.9C93.3 318.2 97.2 304 107.5 295.4L395.5 55.4C406.8 46 422.9 45.5 434.8 54.1z" />
                                    </svg>
                                </span>
                            @endif
                        @endif
                        <a onclick="tabSizeGuide()" href="#xanhworld_main_tab_guide" class="xanhworld_main_size_guide">
                            Hướng dẫn
                        </a>
                    </p>

                    <!-- Product Actions Form -->
                    <form class="xanhworld_single_info_specifications_actions" action="{{ route('client.cart.store') }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <!-- Quantity Box -->
                        <div class="xanhworld_single_info_specifications_actions_qty"
                            data-max-stock="{{ max(1, $quantityProductDetail) }}">
                            <button type="button" class="xanhworld_single_info_specifications_actions_btn"
                                onclick="decreaseQty()">−</button>
                            <span class="xanhworld_single_info_specifications_actions_value">1</span>
                            <button type="button" class="xanhworld_single_info_specifications_actions_btn"
                                onclick="increaseQty()">+</button>
                        </div>
                        <input type="hidden" name="quantity" value="1">

                        <!-- Add to Cart -->
                        <button type="submit" name="action" value="add_to_cart"
                            class="xanhworld_single_info_specifications_actions_cart {{ $isOutOfStock ? 'disabled' : '' }}"
                            {{ $isOutOfStock ? 'disabled' : '' }}>
                            THÊM VÀO GIỎ
                        </button>

                        <!-- Buy Now (same behavior as Add to Cart) -->
                        <button type="submit" name="action" value="add_to_cart"
                            class="xanhworld_single_info_specifications_actions_buy {{ $isOutOfStock ? 'disabled' : '' }}"
                            {{ $isOutOfStock ? 'disabled' : '' }}>
                            MUA NGAY
                        </button>
                        
                        <!-- Favorite button -->
                        <button type="button" @if(in_array($product->id, $favoriteProductIds ?? [])) onclick="removeWishlist({{ $product->id }})" @else onclick="addWishlist({{ $product->id }})" @endif class="xanhworld_fav_btn {{ in_array($product->id, $favoriteProductIds ?? []) ? 'active' : '' }}" aria-label="Yêu thích" style="">
                            @if(in_array($product->id, $favoriteProductIds ?? []))
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ff0000" d="M305 151.1L320 171.8L335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1L576 231.7C576 343.9 436.1 474.2 363.1 529.9C350.7 539.3 335.5 544 320 544C304.5 544 289.2 539.4 276.9 529.9C203.9 474.2 64 343.9 64 231.7L64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1z"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ff0000" d="M442.9 144C415.6 144 389.9 157.1 373.9 179.2L339.5 226.8C335 233 327.8 236.7 320.1 236.7C312.4 236.7 305.2 233 300.7 226.8L266.3 179.2C250.3 157.1 224.6 144 197.3 144C150.3 144 112.2 182.1 112.2 229.1C112.2 279 144.2 327.5 180.3 371.4C221.4 421.4 271.7 465.4 306.2 491.7C309.4 494.1 314.1 495.9 320.2 495.9C326.3 495.9 331 494.1 334.2 491.7C368.7 465.4 419 421.3 460.1 371.4C496.3 327.5 528.2 279 528.2 229.1C528.2 182.1 490.1 144 443.1 144zM335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1C576 297.7 533.1 358 496.9 401.9C452.8 455.5 399.6 502 363.1 529.8C350.8 539.2 335.6 543.9 320 543.9C304.4 543.9 289.2 539.2 276.9 529.8C240.4 502 187.2 455.5 143.1 402C106.9 358.1 64 297.7 64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1L320 171.8L335 151.1z"/></svg>
                            @endif
                        </button>
                    </form>

                    <p class="xanhworld_single_info_specifications_stock">
                        @if ($isOutOfStock)
                            <span style="color: #d33;">Hết hàng</span>
                        @else
                            Còn lại <strong>{{ $quantityProductDetail }}</strong> sản phẩm
                        @endif
                    </p>

                    @if($includedSets->isNotEmpty())
                        <div class="xanhworld_single_accessories_strip">
                            <div class="xanhworld_single_accessories_strip_header">
                                <span>🎯 Gợi ý phụ kiện đi kèm</span>
                                <small>Kéo ngang để xem thêm</small>
                            </div>
                            @foreach ($includedSets as $set)
                                @php
                                    $category = $set['category'] ?? null;
                                    $accessories = collect($set['products'] ?? []);
                                @endphp
                                @if($accessories->isNotEmpty())
                                    <div class="xanhworld_single_accessories_group">
                                        <div class="xanhworld_single_accessories_group_title">
                                            {{ $category?->name ?? 'Danh mục khác' }}
                                        </div>
                                        <div class="xanhworld_single_accessories_scroller" data-accessory-scroll>
                                            @foreach ($accessories as $accessory)
                                                <div class="xanhworld_single_accessories_item">
                                                    <a href="{{ url('/san-pham/' . ($accessory->slug ?? '')) }}" class="xanhworld_single_accessories_item_thumb">
                                                        <img src="{{ asset('clients/assets/img/clothes/' . ($accessory->primaryImage->url ?? 'no-image.webp')) }}"
                                                            alt="{{ $accessory->name }}">
                                                    </a>
                                                    <div class="xanhworld_single_accessories_item_name">{{ $accessory->name }}</div>
                                                    <div class="xanhworld_single_accessories_item_price">
                                                        {{ number_format($accessory->sale_price ?? $accessory->price ?? 0, 0, ',', '.') }}đ
                                                    </div>
                                                    <button type="button"
                                                        class="xanhworld_single_accessories_item_btn"
                                                        data-accessory-add="{{ $accessory->id }}">
                                                        + Thêm nhanh
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="xanhworld_single_info_specifications_desc">
                            <h3 class="xanhworld_single_info_specifications_desc_title">
                                🎁 Ưu đãi khi mua cây tại {{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}
                            </h3>
                            <ul class="xanhworld_single_info_specifications_desc_list">
                                <li class="xanhworld_single_info_specifications_desc_item">
                                    <span class="xanhworld_single_info_specifications_desc_number">1</span>
                                    Tặng <strong>bảo hành chăm sóc 30 ngày</strong> cho mọi cây xanh.
                                </li>
                                <li class="xanhworld_single_info_specifications_desc_item">
                                    <span class="xanhworld_single_info_specifications_desc_number">2</span>
                                    <strong>Miễn phí tư vấn bố trí cây</strong> theo phong thủy và không gian sử dụng.
                                </li>
                                <li class="xanhworld_single_info_specifications_desc_item">
                                    <span class="xanhworld_single_info_specifications_desc_number">3</span>
                                    Giảm <strong>5–10%</strong> khi mua combo chậu + đất + phụ kiện đi kèm.
                                </li>
                                <li class="xanhworld_single_info_specifications_desc_item">
                                    <span class="xanhworld_single_info_specifications_desc_number">4</span>
                                    <strong>Miễn phí vận chuyển nội thành</strong> cho đơn hàng từ 700.000đ.
                                </li>
                            </ul>

                            @if ($product->isInFlashSale())
                                @php
                                    $currentFlashSale = $product->currentFlashSale()->first();
                                @endphp
                                @if ($currentFlashSale)
                                    <div class="xanhworld_single_info_specifications_desc_flashsale">
                                        <strong>⚡ Flash Sale: {{ $currentFlashSale->title }}</strong><br>
                                        Diễn ra từ
                                        <span class="time">
                                            {{ \Carbon\Carbon::parse($currentFlashSale->start_time)->format('H:i') }}
                                            –
                                            {{ \Carbon\Carbon::parse($currentFlashSale->end_time)->format('H:i') }}
                                        </span>
                                        ngày
                                        <span class="date">
                                            {{ \Carbon\Carbon::parse($currentFlashSale->start_time)->format('d/m') }}
                                        </span>.
                                        <br>
                                        🌱 Số lượng cây trong đợt Flash Sale có hạn, ưu tiên đơn thanh toán online.<br>
                                        ⚠️ Mỗi khách hàng chỉ mua tối đa 1 sản phẩm cùng loại trong chương trình.<br>
                                        🕒 Đơn hàng giữ trong 24h, không áp dụng kèm các khuyến mãi khác.
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                </div>

                <div class="xanhworld_single_info_policy">
                    <h3 class="xanhworld_single_info_policy_title">CHÍNH SÁCH BÁN HÀNG</h3>
                    <p class="xanhworld_single_info_policy_subtitle">Áp dụng cho từng ngành hàng</p>

                    <!-- MIỄN PHÍ VẬN CHUYỂN -->
                    <div class="xanhworld_single_info_policy_item">
                        <div class="xanhworld_single_info_policy_icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="#444"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20 8h-3V4H3v13h2a3 3 0 1 0 6 0h4a3 3 0 1 0 6 0h1v-5l-4-4zM5 15V6h10v9H5zm13 1a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-10 1a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm10-4V9.4l2.6 2.6H18z" />
                            </svg>
                        </div>
                        <div class="xanhworld_single_info_policy_content">
                            <strong>MIỄN PHÍ VẬN CHUYỂN</strong>
                        </div>
                    </div>

                    <!-- ĐỔI TRẢ MIỄN PHÍ -->
                    <div class="xanhworld_single_info_policy_item">
                        <div class="xanhworld_single_info_policy_icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="#444"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6a6 6 0 1 1-12 0H4a8 8 0 1 0 8-8z" />
                            </svg>
                        </div>
                        <div class="xanhworld_single_info_policy_content">
                            <strong>ĐỔI TRẢ MIỄN PHÍ</strong>
                        </div>
                    </div>

                    <!-- THANH TOÁN -->
                    <div class="xanhworld_single_info_policy_item">
                        <div class="xanhworld_single_info_policy_icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="#444"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v3h20V6c0-1.1-.9-2-2-2zm0 5H2v9c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9zm-6 6H6v-2h8v2z" />
                            </svg>
                        </div>
                        <div class="xanhworld_single_info_policy_content">
                            <strong>THANH TOÁN</strong>
                        </div>
                    </div>

                    <!-- HỖ TRỢ MUA NHANH -->
                    <div class="xanhworld_single_info_policy_item">
                        <div class="xanhworld_single_info_policy_icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="#444"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.62 10.79a15.055 15.055 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24 11.36 11.36 0 0 0 3.58.57 1 1 0 0 1 1 1v3.5a1 1 0 0 1-1 1C9.27 21 3 14.73 3 7.5a1 1 0 0 1 1-1H7.5a1 1 0 0 1 1 1c0 1.25.2 2.47.57 3.58a1 1 0 0 1-.24 1.01l-2.2 2.2z" />
                            </svg>
                        </div>
                        <div class="xanhworld_single_info_policy_content">
                            <strong>HỖ TRỢ MUA NHANH</strong>
                            <p><span class="xanhworld_single_info_policy_hotline">Call:
                                    {{ preg_replace('/(\d{4})(\d{3})(\d{3})/', '$1.$2.$3', $settings->contact_phone ?? '0382941465') }}
                                    - Zalo:
                                    {{ preg_replace('/(\d{4})(\d{3})(\d{3})/', '$1.$2.$3', $settings->contact_zalo ?? '0382941465') }}</span><br>từ
                                8:30 - 22:30 mỗi ngày.</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
                        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
                        <span style="padding: 0 12px; color: #f74a4a; font-weight: bold;">Khuyễn mãi & Ưu đãi</span>
                        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
                    </div>

                    <div class="xanhworld_single_info_voucher"
                        style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.8; width: fit-content; max-width: 100%; margin: auto; text-align: start;">
                        @foreach ($vouchers as $voucher)
                            @php
                                $type = $voucher->type ?? '';
                                $code = $voucher->code ?? '';
                                $value = $voucher->value ?? '';
                                $min = $voucher->min_order_amount ?? '';
                                $max = $voucher->max_discount_amount ?? '';
                            @endphp

                            @if ($type === 'free_ship')
                                <p style="margin:4px 0;font-size:14px;">
                                    🎫 Nhập mã <strong>{{ $code }}</strong> MIỄN PHÍ SHIP
                                    @if ($value)
                                        TỐI ĐA <span style="color:red">{{ number_format($value, 0, ',', '.') }}đ</span>
                                    @endif
                                    @if ($min)
                                        CHO ĐƠN TỪ <span style="color:red">{{ number_format($min, 0, ',', '.') }}đ</span>
                                    @endif
                                </p>
                            @elseif ($type === 'percentage')
                                <p style="margin:4px 0;font-size:14px;">
                                    🎫 Nhập mã <strong>{{ $code }}</strong> GIẢM <span
                                        style="color:red">{{ number_format($value, 0, ',', '.') }}%</span>
                                    @if ($max)
                                        TỐI ĐA <span style="color:red">{{ number_format($max, 0, ',', '.') }}đ</span>
                                    @endif
                                    @if ($min)
                                        CHO ĐƠN TỪ <span style="color:red">{{ number_format($min, 0, ',', '.') }}đ</span>
                                    @endif
                                </p>
                            @elseif ($type === 'fixed_amount')
                                <p style="margin:4px 0;font-size:14px;">
                                    🎫 Nhập mã <strong>{{ $code }}</strong> GIẢM <span
                                        style="color:red">{{ number_format($value, 0, ',', '.') }}</span>
                                    @if ($min)
                                        CHO ĐƠN TỪ <span style="color:red">{{ number_format($min, 0, ',', '.') }}đ</span>
                                    @endif
                                </p>
                            @endif
                        @endforeach

                        <p style="margin: 4px 0; font-size: 14px;"><span>🚚</span> <strong
                                style="font-size: 14px;">FREESHIP 100%</strong> đơn từ 1000K</p>

                        <div class="xanhworld_single_info_voucher_code" style="margin-top: 16px;">
                            <p style="margin-bottom: 8px;">Mã giảm giá bạn có thể sử dụng:</p>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
                                @foreach ($vouchers as $voucher)
                                    <div class="xanhworld_single_info_voucher_code_item"
                                        style="background: #000; color: #00ffff; padding: 6px 12px; border-radius: 8px; font-weight: bold; font-size: 13px; font-family: monospace; clip-path: polygon(10% 0%, 90% 0%, 90% 35%, 100% 50%, 90% 65%, 90% 100%, 10% 100%, 10% 65%, 0% 50%, 10% 35%); cursor: pointer;">
                                        {{ $voucher->code ?? 'NOBI2025' }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- Popup overlay -->
                    @if($vouchers->isNotEmpty())
                        <div id="voucherPopup" class="xanhworld_main_show_popup_voucher_overlay">
                            <div class="xanhworld_main_show_popup_voucher_box">
                                <button class="xanhworld_main_show_popup_voucher_close">&times;</button>
                                <h2>🎉 Chúc mừng bạn!</h2>
                                <img width="100" src="{{ asset('clients/assets/img/other/party.gif') }}"
                                    alt="Voucher NOBI FASHION">
                                <p>Bạn đã nhận được voucher đặc biệt từ shop:</p>
                                @foreach ($vouchers as $voucher)
                                    <div class="xanhworld_main_show_popup_voucher_code">{{ $voucher->code }}</div>
                                @endforeach
                                <p>Dùng ngay để được ưu đãi hấp dẫn 💖</p>
                            </div>
                        </div>
                    @else
                        <div id="voucherPopup" class="xanhworld_main_show_popup_voucher_overlay">
                            <div class="xanhworld_main_show_popup_voucher_box">
                                <button class="xanhworld_main_show_popup_voucher_close">&times;</button>
                                {{-- <h2>🎉 Chúc mừng bạn!</h2> --}}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            <div id="xanhworld_main_tab_guide" style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
                <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
                <span style="padding: 0 12px; color: #f74a4a; font-weight: bold;">Mô tả sản phẩm</span>
                <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
            </div>
        </section>

        <!-- Mô tả sản phẩm -->
        <section id="xanhworld_review">
            <div class="xanhworld_single_desc">
                <div class="xanhworld_single_desc_button">
                    <button class="xanhworld_single_desc_button_describe .xanhworld_single_desc_button_active">Mô
                        tả</button>
                    <button class="xanhworld_single_desc_button_add_info">Hướng dẫn</button>
                    <button class="xanhworld_single_desc_button_reviews">Đánh giá</button>
                </div>
                <div class="xanhworld_single_desc_tabs">
                    <div class="xanhworld_single_desc_tabs_describe .xanhworld_single_desc_tabs_active">
                        <div class="xanhworld_single_desc_tabs_describes">
                            <div class="xanhworld_single_desc_tabs_describe_specifications">

                                {!! $product->description ?? '<p>Chưa có mô tả cho sản phẩm này.</p>' !!}

                                <div class="xanhworld_single_info_images_tags">
                                    <h6 class="xanhworld_single_info_images_tags_title">Thẻ: </h6>
                                    @if ($product->tags?->isNotEmpty())
                                        @foreach ($product->tags as $tag)
                                            <a href="#"><span
                                                    class="xanhworld_single_info_images_tags_tag">#{{ $tag->name ?? 'thoi-trang' }}</span></a>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- FAQS --}}
                                @include('clients.templates.faqs')
                            </div>
                            <aside class="xanhworld_single_sidebar">
                                <div class="sticky-box">
                                    @include('clients.templates.product_new')
                                </div>
                            </aside>
                        </div>
                    </div>

                    <div class="xanhworld_single_desc_tabs_add_info">
                        @include('clients.templates.size')
                    </div>
                    <div class="xanhworld_single_desc_tabs_reviews">
                        @include('clients.partials.comments', [
                            'type' => 'product',
                            'objectId' => $product->id,
                            'comments' => $comments ?? null,
                            'ratingStats' => $ratingStats ?? null,
                            'totalComments' => $totalComments ?? 0
                        ])
                    </div>
                </div>
            </div>
        </section>

        {{-- Sản phẩm đi kèm (section danh mục) --}}
        @if($includedSets->isNotEmpty())
            <section class="xanhworld_single_product_included">
                <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
                    <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
                    <span style="padding: 0 12px; color: #f74a4a; font-weight: bold;">Sản phẩm đi kèm đề xuất</span>
                    <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
                </div>
                @foreach($includedSets as $set)
                    @php
                        $category = $set['category'] ?? null;
                        $includedList = collect($set['products'] ?? []);
                    @endphp
                    @if($includedList->isNotEmpty())
                        <div class="xanhworld_single_product_related">
                            <h3 class="xanhworld_single_product_related_title">
                                🔗 Sản phẩm đi kèm
                                @if($category)
                                    @if(!empty($category->slug))
                                        <a href="{{ route('client.product.category.index', $category->slug) }}" style="color:#e6525e;">
                                            {{ $category->name }}
                                        </a>
                                    @else
                                        {{ $category->name }}
                                    @endif
                                @endif
                            </h3>
                            <div class="xanhworld_single_product_related_grid">
                                @foreach ($includedList as $included)
                                    <div class="xanhworld_single_product_related_item">
                                        <a href="/san-pham/{{ $included->slug ?? 'san-pham-di-kem' }}" class="xanhworld_single_product_related_img">
                                            <img src="{{ asset('clients/assets/img/clothes/'. ($included->primaryImage->url ?? 'no-image.webp')) }}" alt="{{ $included->name }}">
                                            @if($included->is_featured)
                                                <span class="xanhworld_single_product_related_badge">Hot</span>
                                            @elseif(optional($included->created_at)->diffInDays(now()) <= 30)
                                                <span class="xanhworld_single_product_related_badge">New</span>
                                            @endif
                                        </a>
                                        <div class="xanhworld_single_product_related_info">
                                            <a href="/san-pham/{{ $included->slug ?? 'san-pham-di-kem' }}" class="xanhworld_single_product_related_name">{{ $included->name }}</a>
                                            <p class="xanhworld_single_product_related_price">{{ number_format($included?->sale_price ?? $included?->price ?? 0, 0, ',', '.') }}đ</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </section>
        @endif

        {{-- Sản phẩm liên quan --}}
        @include('clients.templates.product_related')

        <section>
            <div class="xanhworld_chat">
                <!-- Nút cuộn lên đầu trang -->
                <div class="xanhworld_back_to_top">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path
                            d="M270.7 9.7C268.2 3.8 262.4 0 256 0s-12.2 3.8-14.7 9.7L197.2 112.6c-3.4 8-5.2 16.5-5.2 25.2l0 77-144 84L48 280c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 56 0 32 0 24c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8 144 0 0 32.7L133.5 468c-3.5 3-5.5 7.4-5.5 12l0 16c0 8.8 7.2 16 16 16l96 0 0-64c0-8.8 7.2-16 16-16s16 7.2 16 16l0 64 96 0c8.8 0 16-7.2 16-16l0-16c0-4.6-2-9-5.5-12L320 416.7l0-32.7 144 0 0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-24 0-32 0-56c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 18.8-144-84 0-77c0-8.7-1.8-17.2-5.2-25.2L270.7 9.7z" />
                    </svg>
                </div>

                <!-- Zalo -->
                <a href="https://zalo.me/{{ $settings->contact_zalo ?? '0382941465' }}" target="_blank"
                    class="xanhworld_chat_zalo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path
                            d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" />
                    </svg>
                </a>

                <!-- Gọi điện -->
                <a href="tel:{{ $settings->contact_phone ?? '0382941465' }}" class="xanhworld_chat_phone">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path
                            d="M256.6 8C116.5 8 8 110.3 8 248.6c0 72.3 29.7 134.8 78.1 177.9 8.4 7.5 6.6 11.9 8.1 58.2A19.9 19.9 0 0 0 122 502.3c52.9-23.3 53.6-25.1 62.6-22.7C337.9 521.8 504 423.7 504 248.6 504 110.3 396.6 8 256.6 8zm149.2 185.1l-73 115.6a37.4 37.4 0 0 1 -53.9 9.9l-58.1-43.5a15 15 0 0 0 -18 0l-78.4 59.4c-10.5 7.9-24.2-4.6-17.1-15.7l73-115.6a37.4 37.4 0 0 1 53.9-9.9l58.1 43.5a15 15 0 0 0 18 0l78.4-59.4c10.4-8 24.1 4.5 17.1 15.6z" />
                    </svg>
                </a>

                <!-- Facebook -->
                <a href="{{ $settings->facebook_link ?? 'https://www.facebook.com/xanhworld.vn' }}" target="_blank"
                    class="xanhworld_chat_facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path
                            d="M320 0c17.7 0 32 14.3 32 32l0 64 120 0c39.8 0 72 32.2 72 72l0 272c0 39.8-32.2 72-72 72l-304 0c-39.8 0-72-32.2-72-72l0-272c0-39.8 32.2-72 72-72l120 0 0-64c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l32 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-32 0zM264 256a40 40 0 1 0 -80 0 40 40 0 1 0 80 0zm152 40a40 40 0 1 0 0-80 40 40 0 1 0 0 80zM48 224l16 0 0 192-16 0c-26.5 0-48-21.5-48-48l0-96c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-16 0 0-192 16 0z" />
                    </svg>
                </a>
            </div>
        </section>
    </main>

    <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
        <span style="padding: 0 12px; color: #f74a4a; font-weight: bold; text-align: center;">Đăng ký Email nhận thông báo từ {{ $settings->subname ?? '' }}</span>
        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
    </div>

    @include('clients.templates.call')

    <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
        <span style="padding: 0 12px; color: #f74a4a; font-weight: bold; text-align: center;">Đăng ký Email nhận thông báo từ {{ $settings->subname ?? '' }}</span>
        <hr style="flex: 1; height: 2px; background-color: #e6525e; border: none; margin: 0;">
    </div>

    @include('clients.pages.single.images')
@endsection
