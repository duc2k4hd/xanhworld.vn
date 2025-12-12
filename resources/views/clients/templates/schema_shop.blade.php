<!-- 🌐 SCHEMA TRANG CỬA HÀNG / DANH MỤC - THẾ GIỚI CÂY XANH XWORLD -->
<script type="application/ld+json">
    {
"@context": "https://schema.org",
"@graph": [
  {
    "@type": "Organization",
    "@id": "{{ ($settings->site_url ?? url('/')) }}#organization",
    "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}",
    "image": "{{ asset('clients/assets/img/banners/' . ($settings->site_banner ?? 'banner.jpg')) }}",
    "url": "{{ $settings->site_url ?? url('/') }}",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('clients/assets/img/business/' . ($settings->site_logo ?? 'logo.png')) }}",
      "width": 600,
      "height": 200
    },
    "email": "{{ $settings->contact_email ?? '' }}",
    "telephone": "{{ $settings->contact_phone ?? '' }}",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "{{ $settings->contact_address ?? '' }}",
      "addressLocality": "{{ $settings->city ?? '' }}",
      "addressRegion": "{{ $settings->city ?? '' }}",
      "postalCode": "{{ $settings->postalCode ?? '' }}",
      "addressCountry": "VN"
    },
    "contactPoint": [{
      "@type": "ContactPoint",
      "telephone": "{{ $settings->contact_phone ?? '' }}",
      "contactType": "customer service",
      "availableLanguage": ["Vietnamese"],
      "areaServed": "VN"
    }],
    "sameAs": [
      "{{ $settings->facebook_link ?? 'https://www.facebook.com' }}",
      "{{ $settings->instagram_link ?? 'https://www.instagram.com' }}",
      "{{ $settings->discord_link ?? 'https://discord.com' }}"
    ]
  },
  {
    "@type": "WebSite",
    "@id": "{{ ($settings->site_url ?? url('/')) }}#website",
    "url": "{{ $settings->site_url ?? url('/') }}",
    "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}",
    "publisher": { "@id": "{{ ($settings->site_url ?? url('/')) }}#organization" },
    "potentialAction": {
      "@type": "SearchAction",
      "target": "{{ ($settings->site_url ?? url('/')) }}/tim-kiem/{search_term_string}",
      "query-input": "required name=search_term_string"
    }
  },
  {
    "@type": "WebPage",
    "@id": "{{ url()->current() }}#webpage",
    "url": "{{ url()->current() }}",
    "name": "{{ $category->name ?? ($settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD') }}",
    "inLanguage": "vi",
    "isPartOf": { "@id": "{{ ($settings->site_url ?? url('/')) }}#website" },
    "about": { "@id": "{{ ($settings->site_url ?? url('/')) }}#organization" },
    "breadcrumb": { "@id": "{{ ($settings->site_url ?? url('/')) }}#breadcrumb" },
    "primaryImageOfPage": {
      "@type": "ImageObject",
      "url": "{{ asset('clients/assets/img/business/' . ($settings->site_logo ?? 'logo.png')) }}"
    },
    "datePublished": "{{ now()->toDateString() }}",
    "dateModified": "{{ now()->toDateString() }}"
  },
  {
    "@type": "LocalBusiness",
    "@id": "{{ ($settings->site_url ?? url('/')) }}#localbusiness",
    "name": "{{ $settings->site_name ?? 'THẾ GIỚI CÂY XANH XWORLD' }}",
    "image": "{{ asset('clients/assets/img/banners/' . ($settings->site_banner ?? 'banner.jpg')) }}",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('clients/assets/img/business/' . ($settings->site_logo ?? 'logo.png')) }}"
    },
    "url": "{{ $settings->site_url ?? url('/') }}",
    "telephone": "{{ $settings->contact_phone ?? '' }}",
    "email": "{{ $settings->contact_email ?? '' }}",
    "priceRange": "₫₫",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "{{ $settings->contact_address ?? '' }}",
      "addressLocality": "{{ $settings->city ?? '' }}",
      "addressRegion": "{{ $settings->city ?? '' }}",
      "postalCode": "{{ $settings->postalCode ?? '' }}",
      "addressCountry": "VN"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": "{{ $settings->latitude ?? 20.86481 }}",
      "longitude": "{{ $settings->longitude ?? 106.68345 }}"
    },
    "openingHoursSpecification": [{
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens": "08:00",
      "closes": "17:30"
    }],
    "sameAs": [
      "{{ $settings->facebook_link ?? 'https://www.facebook.com' }}",
      "{{ $settings->instagram_link ?? 'https://www.instagram.com' }}",
      "{{ $settings->discord_link ?? 'https://discord.com' }}"
    ]
  },
  {
    "@type": "BreadcrumbList",
    "@id": "{{ ($settings->site_url ?? url('/')) }}#breadcrumb",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "item": {
          "@id": "{{ $settings->site_url ?? url('/') }}",
          "name": "Trang chủ"
        }
      },
      {
        "@type": "ListItem",
        "position": 2,
        "item": {
          "@id": "{{ route('client.shop.index') }}",
          "name": "Cửa hàng cây xanh"
        }
      }
      @if(!empty($category))
      ,{
        "@type": "ListItem",
        "position": 3,
        "item": {
          "@id": "{{ url()->current() }}",
          "name": "{{ $category->name }}"
        }
      }
      @endif
    ]
  },
  {
    "@type": "ItemList",
    "@id": "{{ url()->current() }}#itemlist",
    "url": "{{ url()->current() }}",
    "name": "Danh sách sản phẩm {{ $category->name ?? 'cây xanh, chậu cảnh và phụ kiện' }}",
    "itemListOrder": "https://schema.org/ItemListOrderDescending",
    "numberOfItems": {{ method_exists($products, 'total') ? $products->total() : $products->count() }},
    "itemListElement":[
      @foreach($products as $index => $product)
      {
        "@type": "ListItem",
        "position": {{ $loop->iteration }},
        "url": "{{ $product->canonical_url }}",
        "name": "{{ $product->name }}"
      }{{ !$loop->last ? ',' : '' }}
      @endforeach
    ]
  }
]
}
</script>
<!-- 🌐 END SCHEMA CHUẨN TRANG DANH MỤC -->
