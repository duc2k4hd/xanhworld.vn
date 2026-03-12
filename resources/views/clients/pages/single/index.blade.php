@extends('clients.layouts.master')

@section('title', $product->meta_title . ' – Thế giới cây xanh Xworld' ?? ($product->name ? ($product->name . ' – Thế giới cây xanh Xworld') : 'Thế giới cây xanh Xworld - Chi tiết sản phẩm'))

@section('schema')
    @include('clients.templates.schema_product')
@endsection

@section('head')
    @php
        $siteUrl = rtrim($settings->site_url ?? 'https://xanhworld.vn', '/');
        $productUrl = $product->canonical_url ?? ($siteUrl.'/san-pham/'.($product->slug ?? 'san-pham'));
        $productUrl = rtrim($productUrl, '/');
        $pageTitle = $product->meta_title ?? ($product->name . ' – Thế giới cây xanh Xworld');
        $pageDescription = $product->meta_description ?? 'Thế giới cây xanh Xworld: Cây xanh, chậu cảnh, phụ kiện trang trí. Hướng dẫn setup góc làm việc, ban công, sân vườn xanh mát, giao tận nơi.';
        $keywords = $product->meta_keywords ?? [];
        $pageKeywords = is_array($keywords) ? implode(', ', $keywords) : $keywords;
        $primaryImg = optional($product->primaryImage)->url 
            ? asset('clients/assets/img/clothes/' . $product->primaryImage->url) 
            : asset('clients/assets/img/posts/no-image.webp');
    @endphp

    <meta name="robots" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>
    @if(!empty($pageKeywords))
        <meta name="keywords" content="{{ $pageKeywords }}">
    @endif
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="author" content="{{ $settings->seo_author ?? 'Nguyễn Minh Đức (Đức Nobi)' }}">

    {{-- LCP Image Preload --}}
    <link rel="preload" as="image" href="{{ $primaryImg }}" fetchpriority="high">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $productUrl }}">
    <meta property="og:image" content="{{ $primaryImg }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    <meta property="og:image:type" content="image/webp">
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="{{ $settings->site_name ?? 'Thế giới cây xanh Xworld' }}">
    <meta property="og:locale" content="vi_VN">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ $settings->site_name ?? 'Thế giới cây xanh Xworld' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $primaryImg }}">
    <meta name="twitter:creator" content="{{ $settings->seo_author ?? 'Nguyễn Minh Đức (Đức Nobi)' }}">

    {{-- Canonical & Alternate --}}
    <link rel="canonical" href="{{ $productUrl }}">
    <link rel="alternate" hreflang="vi" href="{{ $productUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $productUrl }}">

    {{-- DNS Prefetch & Preconnect for performance --}}
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/single.css?v=' . time()) }}">
@endpush

@push('js_page')
    <script src="{{ asset('clients/assets/js/single.js?v=' . time()) }}"></script>
@endpush

@section('content')
    <!-- TOP BANNER (Thông báo khuyến mãi chung) -->
    <div class="xanhworld_single_topbanner">
        <p>Miễn phí vận chuyển cho đơn hàng từ <span>4.999K</span></p>
        <p>Tặng kèm chậu và đá khi mua cây giá trị từ <span>4999K</span></p>
        <p>Bảo hành 1 đổi 1 trong vòng 15 ngày</p>
    </div>

    <!-- BREADCRUMB -->
    <div class="xanhworld_single_breadcrumb">
        <a href="{{ route('client.home.index') }}">Trang chủ</a>
        <span>›</span>
        @if($product->primaryCategory)
            <a href="{{ route('client.product.category.index', $product->primaryCategory->slug) }}">{{ $product->primaryCategory->name }}</a>
            <span>›</span>
        @endif
        <span>{{ $product->name }}</span>
    </div>

    <!-- MAIN PRODUCT -->
    <main class="xanhworld_single_main">
        <div class="xanhworld_single_product_top">

            <!-- LEFT: GALLERY -->
            <div class="xanhworld_single_gallery">
                <div class="xanhworld_single_gallery_main" id="xanhworld_main_img">
                    @php 
                        $mainImgPath = $product->primaryImage ? 'clients/assets/img/clothes/' . $product->primaryImage->url : 'clients/assets/img/other/no-image.webp';
                    @endphp
                    <img src="{{ asset($mainImgPath) }}" 
                         alt="{{ $product->name }}" id="xanhworld_main_img_src">
                    
                    @if($product->sale_price && $product->sale_price < $product->price)
                        <div class="xanhworld_single_gallery_badge">GIẢM GIÁ</div>
                    @elseif($product->is_featured)
                        <div class="xanhworld_single_gallery_badge">NỔI BẬT</div>
                    @endif

                    <div class="xanhworld_single_gallery_wishlist {{ in_array($product->id, $favoriteProductIds ?? []) ? 'xanhworld_single_wished' : '' }}" 
                         id="xanhworld_wish_btn" 
                         title="Thêm vào yêu thích" 
                         data-wishlist-id="{{ $product->id }}">
                         {{ in_array($product->id, $favoriteProductIds ?? []) ? '♥' : '♡' }}
                    </div>
                </div>
                
                @if($product->images->isNotEmpty())
                    <div class="xanhworld_single_gallery_thumbs">
                        @php $primaryUrl = $product->primaryImage->url ?? 'no-image.webp'; @endphp
                        <img src="{{ asset('clients/assets/img/clothes/' . $primaryUrl) }}" 
                             alt="{{ $product->name }}" 
                             class="active" 
                             onerror="this.src='{{ asset('clients/assets/img/other/no-image.webp') }}'"
                             data-thumb-src="{{ asset('clients/assets/img/clothes/' . $primaryUrl) }}">
                        @foreach($product->images as $img)
                            @if($product->primaryImage && $img->url === $product->primaryImage->url) @continue @endif
                            @php $thumbPath = 'clients/assets/img/clothes/' . $img->url; @endphp
                            <img src="{{ asset($thumbPath) }}" 
                                 alt="{{ $product->name }}" 
                                 onerror="this.src='{{ asset('clients/assets/img/other/no-image.webp') }}'"
                                 data-thumb-src="{{ asset($thumbPath) }}">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- RIGHT: PRODUCT INFO -->
            <div class="xanhworld_single_product_info">
                <div class="xanhworld_single_brand">
                    @if($product->primaryCategory)
                        <a href="{{ route('client.product.category.index', $product->primaryCategory->slug) }}">{{ $product->primaryCategory->name }}</a>
                    @else
                        <span>XWORLD</span>
                    @endif
                </div>
                <h1 class="xanhworld_single_title">{{ $product->name }}</h1>

                <div class="xanhworld_single_badges">
                    <div class="xanhworld_single_badge_item">
                        <span class="xanhworld_single_check">✔</span> Cây khỏe mạnh, được tuyển chọn kỹ lưỡng
                    </div>
                    <div class="xanhworld_single_badge_item">
                        <span class="xanhworld_single_check">✔</span> Tặng kèm hướng dẫn chăm sóc chuyên sâu
                    </div>
                    <div class="xanhworld_single_badge_item">
                        <span class="xanhworld_single_check">✔</span> Bảo hành 1 đổi 1 trong vòng 30 ngày
                    </div>
                </div>

                <div class="xanhworld_single_price_block">
                    <span class="xanhworld_single_price" id="xanhworld_price_display">
                        {{ number_format($product->resolveCartPrice(), 0, ',', '.') }}₫
                    </span>
                    @if($product->sale_price && $product->sale_price < $product->price)
                        <span class="xanhworld_single_price_old" id="xanhworld_old_price_display">
                            {{ number_format($product->price, 0, ',', '.') }}₫
                        </span>
                    @else
                        <span class="xanhworld_single_price_old" id="xanhworld_old_price_display" style="display: none;"></span>
                    @endif
                </div>

                @if($vouchers && $vouchers->count() > 0)
                    <div class="xanhworld_single_promo_box">
                        💰 Nhập mã <strong>{{ $vouchers->first()->code }}</strong> để giảm ngay 
                        <strong>{{ $vouchers->first()->discount_type == 'percentage' ? $vouchers->first()->discount_value . '%' : number_format($vouchers->first()->discount_value, 0, ',', '.') . '₫' }}</strong> 
                        cho đơn hàng từ {{ number_format($vouchers->first()->min_order_value, 0, ',', '.') }}₫.
                    </div>
                @endif

                

                <!-- BIẾN THỂ -->
                @if($product->variants->count() > 0)
                    <div>
                        <div class="xanhworld_single_size_label">Chọn kích thước / chậu</div>
                        <div class="xanhworld_single_size_options">
                            @foreach($product->variants as $variant)
                                <button class="xanhworld_single_size_btn" 
                                        type="button"
                                        data-variant='{{ json_encode(["id" => $variant->id, "price" => $variant->price, "sale_price" => $variant->sale_price ?? null, "stock" => $variant->stock_quantity ?? null, "sku" => $variant->sku]) }}'>
                                    {{ $variant->name }}
                                    @if($variant->sale_price)
                                        <small>{{ number_format($variant->sale_price, 0, ',', '.') }}₫</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- SỐ LƯỢNG & ĐẶT HÀNG -->
                <form action="{{ route('client.cart.store') }}" method="POST" class="action-buttons-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="product_variant_id" id="form_variant_id" value="{{ $product->variants->first()->id ?? '' }}">
                    <input type="hidden" name="quantity" id="form_quantity_input" value="1">
                    
                    <div class="xanhworld_single_qty_row">
                        <div class="xanhworld_single_qty_control">
                            <button type="button" data-qty="minus">−</button>
                            <input type="number" id="xanhworld_qty" value="1" min="1" max="{{ $product->stock_quantity ?? 999 }}">
                            <button type="button" data-qty="plus">+</button>
                        </div>
                        <button type="submit" class="xanhworld_single_add_btn" {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                            🛒 Thêm vào giỏ hàng
                        </button>
                    </div>
                </form>

                <button class="xanhworld_single_save_btn {{ in_array($product->id, $favoriteProductIds ?? []) ? 'xanhworld_single_wished' : '' }}" 
                        id="xanhworld_save_btn"
                        data-wishlist-id="{{ $product->id }}">
                    {{ in_array($product->id, $favoriteProductIds ?? []) ? '♥ Đã lưu vào yêu thích' : '♡ Lưu để xem sau' }}
                </button>

                <div style="font-size:16px;color:#2d6a4f;font-weight:600; margin-top: 10px;">
                    ❓ Cần tư vấn phong thủy? 
                    <a href="javascript:void(0)" onclick="document.getElementById('phone-request-modal').style.display='flex'" style="color:#d62828; text-decoration: underline;">
                        Yêu cầu gọi lại ngay
                    </a>
                </div>

                <div class="xanhworld_single_meta">
                    <div class="xanhworld_single_meta_item">
                        <span class="xanhworld_single_meta_icon">🛡️</span> Bảo hành cây sống khỏe 30 ngày
                    </div>
                    <div class="xanhworld_single_meta_item">
                        <span class="xanhworld_single_meta_icon">🚚</span> Miễn phí giao hàng cho đơn từ 4999k
                    </div>
                    <div class="xanhworld_single_meta_item" id="xanhworld_stock_info">
                        <span class="xanhworld_single_meta_icon">📦</span> 
                        <span class="xanhworld_single_meta_label">Tình trạng:</span>
                        @if($product->stock_quantity > 0)
                            <span class="xanhworld_single_stock_badge in_stock">Còn hàng</span>
                            <span class="xanhworld_single_sku_tag">SKU: <span id="xanhworld_sku_display">{{ $product->sku }}</span></span>
                        @else
                            <span class="xanhworld_single_stock_badge out_of_stock">Hết hàng</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM MUA KÈM GỢI Ý (Cross-sell) -->
        @if($includedProducts && $includedProducts->isNotEmpty())
            <section class="xanhworld_single_section" style="background: #f0f7f4; padding: 15px; border-radius: 12px; margin-top: 30px;">
                @foreach($includedProducts as $set)
                    <h2 class="xanhworld_single_section_title" style="margin-bottom: 20px;">{{ $set['category']->name }}</h2>
                    <div class="xanhworld_single_products_grid">
                        @foreach($set['products'] as $p)
                            <div class="xanhworld_single_product_card" style="background: #fff;">
                                @php 
                                    $incImgPath = $p->primaryImage ? 'clients/assets/img/clothes/' . $p->primaryImage->url : 'clients/assets/img/other/no-image.webp';
                                @endphp
                                <a href="{{ route('client.product.detail', $p->slug) }}" class="xanhworld_single_product_card_img">
                                    <img onerror="this.src='{{ asset('clients/assets/img/other/no-image.webp') }}'" src="{{ asset($incImgPath) }}" alt="{{ $p->name }}">
                                    @if($p->sale_price && $p->sale_price < $p->price)
                                        <span class="xanhworld_single_product_card_badge">GIẢM GIÁ</span>
                                    @endif
                                </a>
                                <div class="xanhworld_single_product_card_body">
                                    <a href="{{ route('client.product.detail', $p->slug) }}" class="xanhworld_single_product_card_name">
                                        {{ $p->name }}
                                    </a>
                                    <div class="xanhworld_single_product_card_price">
                                        {{ number_format($p->resolveCartPrice(), 0, ',', '.') }}₫
                                        @if($p->sale_price && $p->sale_price < $p->price)
                                            <span class="xanhworld_single_product_card_oldprice">{{ number_format($p->price, 0, ',', '.') }}₫</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('client.cart.store') }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="w-100" style="background:#2d6a4f; color:#fff; border:none; padding:8px; border-radius:4px; font-size:13px; cursor:pointer;">
                                            + Thêm vào giỏ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </section>
        @endif

    <!-- DESCRIPTION + SPECS -->
    <div class="xanhworld_single_desc_section">
        <div>
            <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;" bis_skin_checked="1">
                <hr style="flex: 1; height: 2px; background-color: #48a73b; border: none; margin: 0;">
                <span style="padding: 0 12px; color: #48a73b; font-weight: bold; text-align: center;"><p class="xanhworld_single_desc_title">Mô tả chi tiết sản phẩm</p></span>
                <hr style="flex: 1; height: 2px; background-color: #48a73b; border: none; margin: 0;">
            </div>
            
            <div class="xanhworld_single_desc_text">
                {!! $product->descriptionToHtml() !!}
            </div>

            <div class="xanhworld_single_accordions">
                {{-- Debug: instruction length: {{ isset($product->description['instruction']) ? strlen($product->description['instruction']) : 'not set' }} --}}
                @if(isset($product->description['instruction']) && !empty(trim($product->description['instruction'])))
                <div class="xanhworld_single_accordion_item" id="xanhworld_acc1">
                    <div class="xanhworld_single_accordion_header" onclick="xanhworld_toggleAcc('xanhworld_acc1')">
                        <span>Hướng dẫn trồng & Chăm sóc</span>
                        <span class="xanhworld_single_accordion_arrow">⌄</span>
                    </div>
                    <div class="xanhworld_single_accordion_body">
                        <div class="xanhworld_single_accordion_body_inner">
                            {!! $product->description['instruction'] !!}
                        </div>
                    </div>
                </div>
                @endif
                <div class="xanhworld_single_accordion_item" id="xanhworld_acc2">
                    <div class="xanhworld_single_accordion_header" onclick="xanhworld_toggleAcc('xanhworld_acc2')">
                        <span>Chính sách vận chuyển & Bảo hành</span>
                        <span class="xanhworld_single_accordion_arrow">⌄</span>
                    </div>
                    <div class="xanhworld_single_accordion_body">
                        <div class="xanhworld_single_accordion_body_inner">
                            Xworld giao hàng hỏa tốc trong 2h tại nội thành và 1-3 ngày toàn quốc. 
                            Mọi đơn hàng đều được đóng gói chuyên dụng chống sốc. 
                            Nếu cây bị hư hỏng do vận chuyển, chúng tôi cam kết đổi cây mới ngay lập tức.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPECS SIDEBAR (Thông số kỹ thuật cây cảnh) -->
        <div class="xanhworld_single_specs_card">
            @php
                $highlights = $product->description['highlights'] ?? [];
                if (empty($highlights)) {
                    $highlights = [
                        ['icon' => '🚚', 'text' => 'Giao hàng nhanh'],
                        ['icon' => '🛡️', 'text' => 'Bảo hành đổi trả']
                    ];
                }
            @endphp
            <div class="xanhworld_single_specs_header">
                @foreach($highlights as $hl)
                    <div class="xanhworld_single_specs_badge">
                        {!! $hl['icon'] ?? '' !!}<br>{{ $hl['text'] ?? '' }}
                    </div>
                @endforeach
            </div>
            <div class="xanhworld_single_specs_table">
                @php
                    $specs = $product->description['specifications'] ?? [];
                    $general = $product->description['general'] ?? [];
                    $defaultSpecs = [
                        'height' => 'Chiều cao trưởng thành',
                        'foliage' => 'Độ phủ tán',
                        'light' => 'Ánh sáng',
                        'water' => 'Nhu cầu nước',
                        'fengshui' => 'Ý nghĩa phong thủy',
                        'position' => 'Vị trí đặt',
                    ];
                @endphp

                @foreach($defaultSpecs as $key => $label)
                    @if(!empty($specs[$key]))
                    <div class="xanhworld_single_specs_row">
                        <span class="xanhworld_single_specs_key">{{ $label }}</span>
                        <span class="xanhworld_single_specs_val">{{ $specs[$key] }}</span>
                    </div>
                    @endif
                @endforeach

                @if(!empty($specs['scientific_name']))
                <div class="xanhworld_single_specs_row">
                    <span class="xanhworld_single_specs_key">Tên khoa học</span>
                    <span class="xanhworld_single_specs_val">{{ $specs['scientific_name'] }}</span>
                </div>
                @elseif(!empty($product->meta_keywords))
                <div class="xanhworld_single_specs_row">
                    <span class="xanhworld_single_specs_key">Từ khóa</span>
                    <span class="xanhworld_single_specs_val">{{ is_array($product->meta_keywords) ? implode(', ', $product->meta_keywords) : $product->meta_keywords }}</span>
                </div>
                @endif

                {{-- Custom General Specs --}}
                @foreach($general as $key => $item)
                    @if(!empty($item['value']))
                    <div class="xanhworld_single_specs_row">
                        <span class="xanhworld_single_specs_key">{{ $item['name'] ?? $key }}</span>
                        <span class="xanhworld_single_specs_val">{{ $item['value'] }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
            <div class="xanhworld_single_specs_map">
                 <p style="font-size: 13px; color: #1e4d34; font-weight: 600;">Phù hợp mọi không gian xanh</p>
            </div>
        </div>

        <!-- CUSTOMER REVIEWS (Tích hợp module comment) -->
        <section class="xanhworld_single_reviews_section" id="comment-form-section">
            <div style="display: flex; align-items: center; justify-content: center; margin: 1rem 0;">
                <hr style="flex: 1; height: 2px; background-color: #48a73b; border: none; margin: 0;">
                <span style="padding: 0 12px; color: #48a73b; font-weight: bold; text-align: center;"><p class="xanhworld_single_desc_title">Đánh giá từ khách hàng</p></span>
                <hr style="flex: 1; height: 2px; background-color: #48a73b; border: none; margin: 0;">
            </div>

            @include('clients.partials.comments', ['type' => 'product', 'objectId' => $product->id])
        </section>
    </div>

    <!-- SẢN PHẨM LIÊN QUAN -->
    @if($productRelated && $productRelated->count() > 0)
        <section class="xanhworld_single_section">
            <h2 class="xanhworld_single_section_title">Có thể bạn quan tâm</h2>
            <div class="xanhworld_single_products_grid">
                @foreach($productRelated as $p)
                    <div class="xanhworld_single_product_card">
                        @php 
                            $relatedImgPath = $p->primaryImage ? 'clients/assets/img/clothes/' . $p->primaryImage->url : 'clients/assets/img/other/no-image.webp';
                        @endphp
                        <a href="{{ route('client.product.detail', $p->slug) }}" class="xanhworld_single_product_card_img">
                            <img onerror="this.src='{{ asset('clients/assets/img/other/no-image.webp') }}'" src="{{ asset($relatedImgPath) }}" alt="{{ $p->name }}">
                            @if($p->sale_price && $p->sale_price < $p->price)
                                <span class="xanhworld_single_product_card_badge">GIẢM GIÁ</span>
                            @endif
                        </a>
                        <div class="xanhworld_single_product_card_body">
                            <a href="{{ route('client.product.detail', $p->slug) }}" class="xanhworld_single_product_card_name">
                                {{ $p->name }}
                            </a>
                            <div class="xanhworld_single_product_card_stars">★★★★★</div>
                            <div class="xanhworld_single_product_card_price">
                                {{ number_format($p->resolveCartPrice(), 0, ',', '.') }}₫
                                @if($p->sale_price && $p->sale_price < $p->price)
                                    <span class="xanhworld_single_product_card_oldprice">{{ number_format($p->price, 0, ',', '.') }}₫</span>
                                @endif
                            </div>
                            <div class="xanhworld_single_product_card_shipping">Miễn phí giao hàng từ 4999k</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- TRUST STATS -->
    <section class="xanhworld_single_trust_section">
        <div class="xanhworld_single_trust_inner">
            <div class="xanhworld_single_trust_stat">
                <div class="xanhworld_single_trust_score_stars">★★★★★</div>
                <div class="xanhworld_single_trust_number">Hơn 10.000+ khách hàng tin tưởng mỗi tháng</div>
                <div class="xanhworld_single_trust_sub">Cam kết chất lượng cây xanh hàng đầu Việt Nam</div>
            </div>
            <div class="xanhworld_single_trust_chat">
                <div class="xanhworld_single_trust_chat_tag">CHUYÊN GIA CÂY CẢNH</div>
                <h3>Bạn có câu hỏi?</h3>
                <p>Chat ngay với chuyên gia của Xworld để được tư vấn chọn cây phù hợp với tuổi và mệnh của bạn.</p>
                <button class="xanhworld_single_trust_chat_btn" onclick="javascript:void(0)">Chat ngay bây giờ →</button>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="xanhworld_single_features_section">
        <h2 class="xanhworld_single_features_title">Trải nghiệm mua sắm tuyệt vời tại Xworld</h2>
        <div class="xanhworld_single_features_grid">
            <div class="xanhworld_single_feature_card">
                <div class="xanhworld_single_feature_icon">🚚</div>
                <div class="xanhworld_single_feature_name">Giao hàng thần tốc</div>
                <div class="xanhworld_single_feature_desc">Nhận cây trong 2h tại nội thành HN & HCM</div>
            </div>
            <div class="xanhworld_single_feature_card">
                <div class="xanhworld_single_feature_icon">🌳</div>
                <div class="xanhworld_single_feature_name">Lựa chọn đa dạng</div>
                <div class="xanhworld_single_feature_desc">Hàng ngàn mẫu cây & chậu thiết kế độc đáo</div>
            </div>
            <div class="xanhworld_single_feature_card">
                <div class="xanhworld_single_feature_icon">⭐</div>
                <div class="xanhworld_single_feature_name">Chất lượng bền vững</div>
                <div class="xanhworld_single_feature_desc">Cây được dưỡng khỏe mạnh trước khi giao</div>
            </div>
            <div class="xanhworld_single_feature_card">
                <div class="xanhworld_single_feature_icon">💬</div>
                <div class="xanhworld_single_feature_name">Hỗ trợ trọn đời</div>
                <div class="xanhworld_single_feature_desc">Đồng hành cùng bạn trong suốt quá trình chăm sóc</div>
            </div>
        </div>
    </section>

    <!-- STATS BANNER -->
    <div class="xanhworld_single_stats_section">
        <div class="xanhworld_single_stats_number">1.500.000+</div>
        <div class="xanhworld_single_stats_sub">Cây xanh đã được trao đi kiến tạo không gian sống</div>
    </div>
    </main>

    <!-- PHONE REQUEST MODAL -->
    <div id="phone-request-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center; padding: 20px;">
        <div style="background:#fff; width:100%; max-width:400px; border-radius:12px; padding:30px; position:relative; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
            <span onclick="this.parentElement.parentElement.style.display='none'" style="position:absolute; top:15px; right:20px; font-size:24px; cursor:pointer; color:#999;">&times;</span>
            <h3 style="font-size:20px; font-weight:800; color:#2d6a4f; margin-bottom:15px; text-align:center;">Tư vấn chuyên sâu</h3>
            <p style="font-size:14px; color:#666; margin-bottom:20px; text-align:center;">Để lại số điện thoại, chuyên gia Xworld sẽ gọi lại hỗ trợ bạn chọn cây hợp mệnh trong 5 phút!</p>
            <form action="{{ route('client.product.phone-request') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="text" name="phone" placeholder="Số điện thoại của bạn..." required 
                       style="width:100%; padding:15px; border:2px solid #eee; border-radius:8px; margin-bottom:15px; font-size:16px; outline:none; transition:border-color 0.2s;">
                <button type="submit" style="width:100%; padding:15px; background:#2d6a4f; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:700; cursor:pointer;">GỬI YÊU CẦU</button>
            </form>
        </div>
    </div>


<!-- LIGHTBOX CAO CẤP -->
<div id="xanhworld_lightbox" class="xanhworld_lightbox">
    <div class="xanhworld_lightbox_overlay"></div>
    <div class="xanhworld_lightbox_content">
        <div class="xanhworld_lightbox_toolbar">
            <button class="xanhworld_lightbox_tool_btn" data-tool="zoom-in" title="Phóng to">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            </button>
            <button class="xanhworld_lightbox_tool_btn" data-tool="zoom-out" title="Thu nhỏ">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            </button>
            <button class="xanhworld_lightbox_tool_btn" data-tool="reset" title="Đặt lại">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
            </button>
            <div class="xanhworld_lightbox_tool_divider"></div>
            <button class="xanhworld_lightbox_tool_btn" data-tool="download" title="Tải ảnh xuống">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </button>
            <button class="xanhworld_lightbox_tool_btn" data-tool="fullscreen" title="Toàn màn hình">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 1 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
            </button>
        </div>
        <button class="xanhworld_lightbox_close" aria-label="Đóng">×</button>
        
        <div class="xanhworld_lightbox_main_wrapper">
            <button class="xanhworld_lightbox_nav prev" aria-label="Ảnh trước">‹</button>
            <div class="xanhworld_lightbox_image_container">
                <img id="xanhworld_lightbox_img" src="" alt="Sản phẩm">
            </div>
            <button class="xanhworld_lightbox_nav next" aria-label="Ảnh sau">›</button>
        </div>

        <div class="xanhworld_lightbox_thumbs_wrapper">
            <div class="xanhworld_lightbox_thumbs" id="xanhworld_lightbox_thumbs">
                <!-- Sẽ được điền bằng JS -->
            </div>
        </div>
    </div>
</div>
@endsection
