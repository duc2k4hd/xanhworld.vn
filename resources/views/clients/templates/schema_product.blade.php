<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}#organization",
      "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD - Thế giới cây xanh & phụ kiện' }}",
      "url": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}",
      "logo": "{{ asset('clients/assets/img/business/' . ($settings->site_logo ?? 'no-image.webp')) }}",
      "email": "{{ ($settings->contact_email ?? 'support@nobifashion.vn') }}",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{ ($settings->contact_address ?? 'Số 123 Đường Thời Trang, Quận Trung Tâm') }}",
        "addressRegion": "{{ ($settings->city ?? 'Hải Phòng') }}",
        "postalCode": "{{ ($settings->postalCode ?? '180000') }}",
        "addressCountry": "{{ ($settings->site_language ?? 'vi') }}",
        "addressLocality": "{{ ($settings->addressLocality ?? 'Hải Phòng') }}"
      },
      "contactPoint": [
        {
          "@type": "ContactPoint",
          "telephone": "{{ ($settings->contact_phone ?? '0827 786 198') }}",
          "contactType": "customer service"
        }
      ],
      "sameAs": [
        "{{ ($settings->facebook_link ?? 'https://www.facebook.com/nobifashion.vn') }}",
        "{{ ($settings->instagram_link ?? 'https://www.instagram.com/nobifashion.vn') }}",
        "{{ ($settings->discord_link ?? 'https://discord.gg/nobifashion') }}"
      ]
    },
    {
      "@type": "WebPage",
      "@id": "{{ ($product->canonical_url ?? ($settings->site_url ?? 'https://nobifashion.vn')) }}#webpage",
      "url": "{{ ($product->canonical_url ?? ($settings->site_url ?? 'https://nobifashion.vn')) }}",
      "name": "{{ $product->meta_title . ' | ' . ($settings->site_name ?? $settings->subname) ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD - Cây xanh & phụ kiện decor') }}",
      "description": "{{ $product->meta_desc ?? 'THẾ GIỚI CÂY XANH XWORLD: Cây xanh, chậu cảnh, phụ kiện decor. Setup góc làm việc, ban công, sân vườn xanh mát. Giao cây tận nơi, bảo hành cây khỏe.' }}",
      "inLanguage": "{{ ($settings->site_language ?? 'vi') }}",
      "isPartOf": {
        "@id": "{{ ($product->canonical_url ?? ($settings->site_url ?? 'https://nobifashion.vn')) }}#website"
      }
    },
    {
      "@type": "LocalBusiness",
      "@id": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}#localbusiness",
      "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}",
      "logo": {
        "@type": "ImageObject",
        "url": "{{ asset('clients/assets/img/business/' . ($settings->site_logo ?? 'no-image.webp')) }}"
      },
      "image": "{{ asset('clients/assets/img/banners/' . ($settings->site_banner ?? 'no-image.webp')) }}",
      "url": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}",
      "telephone": "{{ ($settings->contact_phone ?? '0827 786 198') }}",
      "email": "{{ ($settings->contact_email ?? 'xanhworldvietnam@gmail.com') }}",
      "priceRange": "₫₫",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{ ($settings->contact_address ?? 'Xóm 3 - Xã Hà Đông - Thành Phố Hải Phòng') }}",
        "addressLocality": "{{ ($settings->city ?? 'Hải Phòng') }}",
        "addressRegion": "{{ ($settings->city ?? 'Hải Phòng') }}",
        "postalCode": "{{ ($settings->postalCode ?? '180000') }}",
        "addressCountry": "VN"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": {{ ($settings->latitude ?? 20.86481) }},
        "longitude": {{ ($settings->longitude ?? 106.68345) }}
      },
      "openingHoursSpecification": [{
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
        "opens": "08:00",
        "closes": "17:30"
      }],
      "sameAs": [
        "{{ ($settings->facebook_link ?? 'https://www.facebook.com/nobifashionvietnam') }}",
        "{{ ($settings->instagram_link ?? 'https://www.instagram.com/nobifashionvietnam') }}",
        "{{ ($settings->discord_link ?? 'https://discord.gg/nobifashion') }}"
      ]
    },
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@id": "{{ ($settings->site_url ?? 'https://xanhworld.vn') }}",
            "name": "Trang chủ THẾ GIỚI CÂY XANH XWORLD"
          }
        }
        @php
          $position = 2;
          $categoryBreadcrumb = $product?->primaryCategory?->first() ?? null;
          $breadcrumbPath = collect();
          while ($categoryBreadcrumb) {
            $breadcrumbPath->prepend($categoryBreadcrumb);
            $categoryBreadcrumb = $categoryBreadcrumb->parent ?? null;
          }
        @endphp
        @foreach ($breadcrumbPath as $breadcrumb)
          ,{
            "@type": "ListItem",
            "position": {{ $position }},
            "item": {
              "@id": "{{ route('client.product.category.index', $breadcrumb->slug) }}",
              "name": "{{ $breadcrumb->name }}"
            }
          }
          @php $position++; @endphp
        @endforeach
        @if ($product->primaryCategory)
          @php $lastCategory = $product?->extraCategories()?->last(); @endphp
          @if ($lastCategory && !$breadcrumbPath->contains('id', $lastCategory->id))
            ,{
              "@type": "ListItem",
              "position": {{ $position }},
              "item": {
                "@id": "{{ route('client.product.category.index', $lastCategory->slug) }}",
                "name": "{{ $lastCategory->name }}"
              }
            }
            @php $position++; @endphp
          @endif
        @endif
        ,{
          "@type": "ListItem",
          "position": {{ $position }},
          "item": {
            "@id": "{{ ($settings->site_url ?? 'https://xanhworld.vn') . ($product->meta_canonical ?? ($settings->site_url ?? 'https://xanhworld.vn')) }}",
            "name": "{{ $product->meta_title . ' | ' . ($settings->site_name ?? $settings->subname) ?? ($product->name ?? 'THẾ GIỚI CÂY XANH XWORLD - Cây xanh & phụ kiện decor') }}"
          }
        }
      ]
    },
  {
    "@type": "Product",
    "@id": "{{ ($product->canonical_url ?? ($settings->site_url ?? 'https://xanhworld.vn')) }}/#product",
    "name": "{{ $product->meta_title . ' | ' . ($settings->site_name ?? $settings->subname) ?? ($product->name ?? 'Cây xanh & phụ kiện - THẾ GIỚI CÂY XANH XWORLD') }}",
    "image": {
      "@type": "ImageObject",
      "url": "{{ asset('clients/assets/img/clothes/' . ($product->primaryImage->url ?? 'no-image.jpg')) }}",
      "width": 600,
      "height": 600
    },
    "description": "{{ $product->meta_desc ?? 'THẾ GIỚI CÂY XANH XWORLD: Cây xanh khỏe mạnh, chậu trang trí, phụ kiện setup góc sống xanh. Giao cây tận nơi, hướng dẫn chăm sóc & bảo hành cây.' }}",
    "sku": "{{ ($product->sku ?? 'SKU-DEFAULT') }}",
    "mpn": "{{ ($product->sku ?? 'SKU-DEFAULT') }}",
    "productID": "sku:{{ ($product->sku ?? 'SKU-DEFAULT') }}",
    "brand": {
      "@type": "Brand",
      "@id": "{{ ($settings->site_url ?? 'https://xanhworld.vn') }}#brand-xworld",
      "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}"
    },
    "manufacturer": {
      "@type": "Organization",
      "@id": "{{ ($settings->site_url ?? 'https://xanhworld.vn') }}#manufacturer-xworld",
      "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}"
    },
      "countryOfOrigin": "{{ ($product->countryOfOrigin ?? 'VN') }}",
      @php
        $schemaRatingTotal = $ratingStats['total_comments'] ?? ($product->approved_comments_count ?? 0);
        $schemaRatingAvg = $ratingStats['average_rating'] ?? ($product->approved_rating_avg ?? null);
      @endphp
      @if($schemaRatingTotal > 0 && $schemaRatingAvg)
        "aggregateRating": {
          "@type": "AggregateRating",
          "ratingValue": "{{ round($schemaRatingAvg, 1) }}",
          "reviewCount": "{{ (int) $schemaRatingTotal }}"
        },
      @endif
      "isFamilyFriendly": true,
      "keywords": {!! json_encode($product->meta_keywords ?? [
  'quần áo',
  'phụ kiện',
  'thời trang nam',
  'thời trang nữ',
  'áo phông',
  'sơ mi',
  'quần jean',
  'váy',
  'túi xách',
  'mũ nón',
  'thắt lưng'
]) !!},
      "releaseDate": "{{ (($product->created_at ?? null) ? $product->created_at->format('Y-m-d') : now()->format('Y-m-d')) }}",
      "audience": {
        "@type": "PeopleAudience",
        "@id": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}#audience-{{ ($product->brand->slug ?? 'nobi-fashion') }}",
        "audienceType": "Người tiêu dùng yêu thời trang"
      },
      "offers": {
        "@type": "Offer",
        "url": "{{ ($product->canonical_url ?? ($settings->site_url ?? 'https://nobifashion.vn')) }}",
        "priceCurrency": "VND",
        "price": "{{ ($product->price ?? 199000) }}",
        "priceValidUntil": "{{ (\Carbon\Carbon::now()->addMonths(6)->format('Y-m-d')) }}",
        "availability": "{{ ($product->in_stock ?? true) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "itemCondition": "https://schema.org/NewCondition",
        "seller": {
          "@type": "Organization",
          "@id": "{{ ($settings->site_url ?? 'https://nobifashion.vn') }}#organization",
          "name": "{{ $settings->site_name ?? 'NOBI FASHION' }}"
        },
        "shippingDetails": {
          "@type": "OfferShippingDetails",
          "shippingDestination": { "@type": "DefinedRegion", "addressCountry": "VN" },
          "shippingRate": { "@type": "MonetaryAmount", "value": "{{ ($product->shipping_fee ?? 30000) }}", "currency": "VND" },
          "deliveryTime": {
            "@type": "ShippingDeliveryTime",
            "handlingTime": { "@type": "QuantitativeValue", "minValue": 1, "maxValue": 2, "unitCode": "DAY" },
            "transitTime": { "@type": "QuantitativeValue", "minValue": 1, "maxValue": 3, "unitCode": "DAY" }
          }
        },
        "hasMerchantReturnPolicy": {
          "@type": "MerchantReturnPolicy",
          "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
          "merchantReturnDays": {{ ($settings->return_days ?? 7) }},
          "returnMethod": "https://schema.org/ReturnByMail",
          "returnFees": "https://schema.org/FreeReturn",
          "refundType": "https://schema.org/FullRefund",
          "applicableCountry": "VN",
          "merchantReturnLink": "{{ ($settings->return_link ?? 'https://nobifashion.vn/chinh-sach-doi-tra') }}"
        }
      }@if(isset($latestReviews) && $latestReviews->count() > 0),
        "review": [
          @foreach($latestReviews as $review)
            {
              "@type": "Review",
              "author": {
                "@type": "Person",
                "name": "{{ $review->account->name ?? $review->name ?? 'Khách hàng' }}"
              },
              "reviewRating": {
                "@type": "Rating",
                "ratingValue": "{{ (int) ($review->rating ?? 5) }}",
                "bestRating": "5",
                "worstRating": "1"
              },
              "datePublished": "{{ optional($review->created_at)->format('Y-m-d') ?? now()->format('Y-m-d') }}",
              "reviewBody": {!! json_encode(Str::limit($review->content ?? '', 200), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            }{{ !$loop->last ? ',' : '' }}
          @endforeach
      ]@endif
    },
    {
      "@type": "FAQPage",
      "name": "Câu hỏi thường gặp về {{ $product->name ?? 'cây xanh và chậu cảnh tại THẾ GIỚI CÂY XANH XWORLD' }}",
      "mainEntity": [
        @if ($product->faqs && $product->faqs->count())
          @foreach ($product->faqs as $faq)
            {
              "@type": "Question",
              "name": "{{ $faq->question ?? 'Cây có khỏe, đúng giống và đúng kích thước như mô tả không?' }}",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ $faq->answer ?? 'XWORLD cam kết cây đúng giống, đúng kích thước, được chăm kỹ trước khi giao và hướng dẫn chăm sóc chi tiết.' }}"
              }
            }{{ !$loop->last ? ',' : '' }}
          @endforeach
        @else
          {
            "@type": "Question",
            "name": "Cây tại XWORLD có đảm bảo khỏe mạnh khi giao không?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Tất cả cây đều được tuyển chọn, chăm sóc và kiểm tra trước khi đóng gói. XWORLD cam kết cây khỏe, không sâu bệnh và đúng mô tả."
            }
          },
          {
            "@type": "Question",
            "name": "Có hướng dẫn chăm sóc cây sau khi mua không?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "XWORLD cung cấp hướng dẫn chăm sóc chi tiết cho từng loại cây: tưới nước, ánh sáng, thay chậu, đất trồng và cách phục hồi cây yếu."
            }
          },
          {
            "@type": "Question",
            "name": "Cây có được đổi trả nếu bị hư hại khi vận chuyển không?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Có. Nếu cây bị gãy, dập hoặc hư hại do vận chuyển, XWORLD hỗ trợ đổi cây mới hoặc hoàn tiền theo chính sách bảo vệ khách hàng."
            }
          },
          {
            "@type": "Question",
            "name": "Thời gian giao cây là bao lâu?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Nội thành giao nhanh trong ngày. Ngoại tỉnh từ 1–3 ngày làm việc. Cây được đóng gói an toàn theo tiêu chuẩn XWORLD."
            }
          },
          {
            "@type": "Question",
            "name": "Có được xem cây trước khi thanh toán không?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "XWORLD hỗ trợ đồng kiểm khi nhận hàng. Bạn có thể kiểm tra tình trạng cây trước khi thanh toán để đảm bảo sự hài lòng."
            }
          }
        @endif
      ]
    }
    
    @if (optional($product->howtos->first())->steps)
      ,{
        "@type": "HowTo",
        "name": "{{ ($product->howtos->first()->title ?? 'Hướng dẫn chăm sóc cây đúng cách') }}",
        "description": "{{ ($product->howtos->first()->description ?? 'Các bước chăm sóc, tưới nước và bố trí ánh sáng để cây phát triển khỏe mạnh tại nhà.') }}",
        "image": "{{ asset('clients/assets/img/clothers/' . ($product->primary_image->url ?? 'no-image.jpg')) }}",
        "totalTime": "PT15M",
        "estimatedCost": { "@type": "MonetaryAmount", "currency": "VND", "value": "10000" },

        @php
          $howto = data_get($product, 'howtos.0');
          $supplies = collect(data_get($howto, 'supplies', []))->filter()->values();
          $steps = collect(data_get($howto, 'steps', []))->filter()->values();
        @endphp

        @if($supplies->isNotEmpty())
          "supply": [
            @foreach($supplies as $supply)
                  {!! json_encode([
                '@type' => 'HowToSupply',
                'name' => is_array($supply)
                  ? ($supply['name'] ?? 'Dụng cụ chăm cây cơ bản')
                  : ($supply ?? 'Dụng cụ chăm cây cơ bản')
              ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}{{ !$loop->last ? ',' : '' }}
            @endforeach
          ]@if($steps->isNotEmpty()),@endif
        @endif

        @if($steps->isNotEmpty())
          "step": [
            @foreach($steps as $step)
                  {!! json_encode([
                '@type' => 'HowToStep',
                'name' => is_array($step)
                  ? ($step['name'] ?? 'Bước chăm sóc cây')
                  : ($step ?? 'Bước chăm sóc cây'),
                'text' => is_array($step)
                  ? ($step['text'] ?? 'Làm theo hướng dẫn để cây luôn khỏe và phát triển tốt.')
                  : 'Làm theo hướng dẫn để cây luôn khỏe và phát triển tốt.'
              ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}{{ !$loop->last ? ',' : '' }}
            @endforeach
          ]
        @endif
      }
    @endif

  ]
}
</script>