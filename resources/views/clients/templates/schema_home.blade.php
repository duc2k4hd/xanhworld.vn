@php
    $siteUrl = $settings->site_url ?? 'https://xanhworld.vn';
    $logoUrl = asset('clients/assets/img/business/'.($settings->site_logo ?? 'no-image.webp'));
    $bannerUrl = asset('clients/assets/img/banners/'.($settings->site_banner ?? 'no-image.webp'));

    $socialLinks = array_values(array_filter([
        optional($settings)->facebook_link,
        optional($settings)->instagram_link,
        optional($settings)->discord_link,
    ]));

    $featuredProducts = ($productsFeatured ?? collect())->take(10);
    $featuredItems = [];

    foreach ($featuredProducts as $index => $product) {
        $featuredItems[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'item' => [
                '@type' => 'Product',
                '@id' => $siteUrl.'/san-pham/'.$product->slug,
                'url' => $siteUrl.'/san-pham/'.$product->slug,
                'name' => $product->name,
                'image' => asset('clients/assets/img/clothes/'.($product->primaryImage->url ?? 'no-image.webp')),
                'sku' => $product->sku,
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'VND',
                    'price' => (float) $product->resolveCartPrice(),
                    'availability' => ($product->stock_quantity ?? 0) > 0
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                ],
                'aggregateRating' => ($product->approved_comments_count ?? 0) > 0 && ($product->approved_rating_avg ?? null)
                    ? [
                        '@type' => 'AggregateRating',
                        'ratingValue' => round((float) $product->approved_rating_avg, 1),
                        'reviewCount' => (int) $product->approved_comments_count,
                    ]
                    : null,
            ],
        ];
    }

    // Loại bỏ aggregateRating null nếu không có review
    $featuredItems = array_map(function ($item) {
        if (isset($item['item']['aggregateRating']) && $item['item']['aggregateRating'] === null) {
            unset($item['item']['aggregateRating']);
        }

        return $item;
    }, $featuredItems);
@endphp

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => $siteUrl.'#organization',
            'name' => $settings->site_name ?? 'Xanh World',
            'url' => $siteUrl,
            'logo' => $logoUrl,
            'email' => $settings->contact_email ?? 'xanhworldvietnam@gmail.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->contact_address ?? 'Xóm 3 - Xã Hà Đông - Thành Phố Hải Phòng',
                'addressLocality' => $settings->city ?? 'Hải Phòng',
                'addressRegion' => $settings->city ?? 'Hải Phòng',
                'postalCode' => $settings->postalCode ?? '180000',
                'addressCountry' => 'VN',
            ],
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'telephone' => $settings->contact_phone ?? '0827786198',
                'contactType' => 'customer service',
            ]],
            'sameAs' => $socialLinks,
        ],
        [
            '@type' => 'WebSite',
            '@id' => $siteUrl.'#website',
            'url' => $siteUrl,
            'name' => $settings->site_name ?? ($settings->subname ?? 'Xanh World - Thế giới cây xanh & phụ kiện'),
            'publisher' => ['@id' => $siteUrl.'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $siteUrl.'/shop?keyword={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => $siteUrl.'#localbusiness',
            'name' => $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
            ],
            'image' => $bannerUrl,
            'url' => $siteUrl,
            'telephone' => $settings->contact_phone ?? '0827786198',
            'priceRange' => '₫₫',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->contact_address ?? 'Xóm 3 - Xã Hà Đông - Thành Phố Hải Phòng',
                'addressLocality' => $settings->city ?? 'Hải Phòng',
                'addressRegion' => $settings->city ?? 'Hải Phòng',
                'postalCode' => $settings->postalCode ?? '180000',
                'addressCountry' => 'VN',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $settings->latitude ?? 20.86481,
                'longitude' => $settings->longitude ?? 106.68345,
            ],
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'opens' => '08:00',
                'closes' => '21:00',
            ]],
            'sameAs' => $socialLinks,
        ],
        [
            '@type' => 'WebPage',
            '@id' => $siteUrl. '#webpage',
            'url' => $siteUrl,
            'name' => $settings->site_name ?? 'Trang chủ '.$settings->subname ?? 'Xanh World - Thế giới cây xanh & phụ kiện',
            'description' => $settings->site_description ?? 'Xanh World - Thế giới cây xanh, chậu cảnh, phụ kiện trang trí, setup góc làm việc, ban công, sân vườn. Giao cây tận nơi, tư vấn miễn phí.',
            'inLanguage' => $settings->site_language ?? 'vi-VN',
            'isPartOf' => ['@id' => $siteUrl.'#website'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [[
                '@type' => 'ListItem',
                'position' => 1,
                'item' => [
                    '@id' => $siteUrl.'#home',
                    'name' => 'Trang chủ',
                    'image' => $bannerUrl,
                ],
            ]],
        ],
        // Danh sách sản phẩm nổi bật trên trang chủ
        [
            '@type' => 'ItemList',
            '@id' => $siteUrl.'#featured-products',
            'name' => 'Sản phẩm nổi bật',
            'itemListOrder' => 'http://schema.org/ItemListOrderAscending',
            'numberOfItems' => count($featuredItems),
            'itemListElement' => $featuredItems,
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>









