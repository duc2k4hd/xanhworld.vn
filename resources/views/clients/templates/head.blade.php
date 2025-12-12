<meta name="author" content="{{ $settings->seo_author ?? 'THẾ GIỚI CÂY XANH XWORLD' }}">

<link rel="apple-touch-icon" sizes="180x180"
    href="{{ asset('/clients/assets/img/business/' . ($settings->site_favicon ?? 'favicon.png')) }}">
<link rel="icon" type="image/png" sizes="32x32"
    href="{{ asset('/clients/assets/img/business/' . ($settings->site_favicon ?? 'favicon.png')) }}">
<link rel="icon" type="image/png" sizes="16x16"
    href="{{ asset('/clients/assets/img/business/' . ($settings->site_favicon ?? 'favicon.png')) }}">
<link rel="mask-icon"
    href="{{ asset('clients/assets/img/business/' . ($settings->site_favicon ?? 'favicon.png')) }}"
    color="#5bbad5">
<link rel="icon"
    href="{{ asset('clients/assets/img/business/' . ($settings->site_favicon ?? 'favicon.png')) }}"
    type="image/x-icon">
<meta name="theme-color" content="#ffffff">

<meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">
<meta http-equiv="X-Content-Type-Options" content="nosniff">
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
{{-- {!! $settings->site_pinterest ?? '' !!} --}}
{{-- {!! $settings->google_tag_header ?? '' !!} --}}