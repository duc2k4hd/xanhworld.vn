<link rel="stylesheet" href="{{ asset('clients/assets/css/main.css') }}" />

<link rel="preload"
      href="{{ asset('clients/assets/css/main.css') }}"
      as="style"
      onload="this.rel='stylesheet'">

@stack('css_page')

<!-- ========================= -->
<!-- 🔥 NON-CRITICAL CSS -->
<!-- ========================= -->
<link rel="stylesheet" href="{{ asset('clients/assets/css/vendor/slim_select.css') }}" media="print" onload="this.media='all'">
