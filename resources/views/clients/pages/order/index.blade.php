@extends('clients.layouts.master')



@section('title', 'Đơn hĂ ng cá»§a tĂ´i | ' . renderMeta($settings->site_name ?? ($settings->subname ?? 'NOBI FASHION')))



@section('head')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('clients/assets/css/order.css') }}">

    <meta name="robots" content="follow, noindex"/>

@endsection



@section('content')

    <div class="xanhworld_order_wrapper">

        <!-- Breadcrumb -->

        <section>

            <div class="xanhworld_order_breadcrumb">

                <a href="{{ route('client.home.index') }}">Trang chá»§</a>

                <span class="separator">>></span>

                <span class="breadcrumb-current">Đơn hĂ ng cá»§a tĂ´i</span>

            </div>

        </section>



        <section class="xanhworld_order_list">

            <div class="xanhworld_order_list_container">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                    <h1 class="xanhworld_order_list_title mb-0">Đơn hĂ ng cá»§a tĂ´i</h1>

                    <a href="{{ route('client.order.track') }}" class="xanhworld_order_item_btn xanhworld_order_item_btn_view" style="text-decoration:none;">

                        đŸ" Tra cứu vận đơn GHN

                    </a>

                </div>



                <!-- Filters -->

                <div class="xanhworld_order_filters">

                    <form method="GET" action="{{ route('client.order.index') }}" class="xanhworld_order_filter_form">

                        <div class="xanhworld_order_filter_group">

                            <label>Trạng thĂ¡i đơn hĂ ng:</label>

                            <select name="status" class="xanhworld_order_filter_select">

                                <option value="">Tất cả</option>

                                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Chờ xá»­ lĂ½</option>

                                <option value="processing" {{ $filters['status'] === 'processing' ? 'selected' : '' }}>Đang xá»­ lĂ½</option>

                                <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>HoĂ n thĂ nh</option>

                                <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>ÄĂ£ há»§y</option>

                            </select>

                        </div>

                        <div class="xanhworld_order_filter_group">

                            <label>Trạng thĂ¡i thanh toĂ¡n:</label>

                            <select name="payment_status" class="xanhworld_order_filter_select">

                                <option value="">Tất cả</option>

                                <option value="pending" {{ $filters['payment_status'] === 'pending' ? 'selected' : '' }}>Chờ thanh toĂ¡n</option>

                                <option value="paid" {{ $filters['payment_status'] === 'paid' ? 'selected' : '' }}>ÄĂ£ thanh toĂ¡n</option>

                                <option value="failed" {{ $filters['payment_status'] === 'failed' ? 'selected' : '' }}>Thất bại</option>

                            </select>

                        </div>

                        <button type="submit" class="xanhworld_order_filter_btn">Lọc</button>

                        <a href="{{ route('client.order.index') }}" class="xanhworld_order_filter_reset">XĂ³a bá»™ lọc</a>

                    </form>

                </div>



                <!-- Orders List -->

                @if($orders->count() > 0)

                    <div class="xanhworld_order_items">

                        @foreach($orders as $order)

                            <div class="xanhworld_order_item">

                                <div class="xanhworld_order_item_header">

                                    <div class="xanhworld_order_item_info">

                                        <h3 class="xanhworld_order_item_code">

                                            Đơn hĂ ng: <strong>{{ $order->code }}</strong>

                                        </h3>

                                        <div class="xanhworld_order_item_meta">

                                            <span class="xanhworld_order_item_date">

                                                đŸ"… {{ $order->created_at->format('d/m/Y H:i') }}

                                            </span>

                                            <span class="xanhworld_order_item_status status-{{ $order->status }}">

                                                @if($order->status === 'pending')

                                                    ⏳ Chờ xá»­ lĂ½

                                                @elseif($order->status === 'processing')

                                                    đŸ"" Đang xá»­ lĂ½

                                                @elseif($order->status === 'completed')

                                                    ✅ HoĂ n thĂ nh

                                                @else

                                                    ❌ ÄĂ£ há»§y

                                                @endif

                                            </span>

                                            <span class="xanhworld_order_item_payment payment-{{ $order->payment_status }}">

                                                @if($order->payment_status === 'pending')

                                                    đŸ'³ Chờ thanh toĂ¡n

                                                @elseif($order->payment_status === 'paid')

                                                    ✅ ÄĂ£ thanh toĂ¡n

                                                @else

                                                    ❌ Thanh toĂ¡n thất bại

                                                @endif

                                            </span>

                                        </div>

                                    </div>

                                    <div class="xanhworld_order_item_total">

                                        <strong>{{ number_format($order->final_price, 0, ',', '.') }} Ä'</strong>

                                    </div>

                                </div>



                                <div class="xanhworld_order_item_products">

                                    @foreach($order->items->take(3) as $item)

                                        <div class="xanhworld_order_item_product">

                                            @php

                                                $imageUrl = $item->variant?->primaryVariantImage

                                                    ? asset('clients/assets/img/clothes/' . $item->variant->primaryVariantImage->url)

                                                    : ($item->product->primaryImage

                                                        ? asset('clients/assets/img/clothes/' . $item->product->primaryImage->url)

                                                        : asset('clients/assets/img/clothes/no-image.webp'));

                                            @endphp

                                            <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="xanhworld_order_item_product_img">

                                            <div class="xanhworld_order_item_product_info">

                                                <div class="xanhworld_order_item_product_name">{{ $item->product->name }}</div>

                                                @if($item->variant)

                                                    @php

                                                        $attrs = is_string($item->variant->attributes) 

                                                            ? json_decode($item->variant->attributes, true) 

                                                            : $item->variant->attributes;

                                                    @endphp

                                                    @if($attrs && is_array($attrs))

                                                        <div class="xanhworld_order_item_product_attrs">

                                                            {{ collect($attrs)->map(fn($val, $key) => ucfirst($key) . ': ' . $val)->join(', ') }}

                                                        </div>

                                                    @endif

                                                @endif

                                                <div class="xanhworld_order_item_product_qty">

                                                    Số lượng: {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }} đ

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                    @if($order->items->count() > 3)

                                        <div class="xanhworld_order_item_product_more">

                                            + {{ $order->items->count() - 3 }} sản phẩm khĂ¡c

                                        </div>

                                    @endif

                                </div>



                                <div class="xanhworld_order_item_actions">

                                    <a href="{{ route('client.order.show', $order->id) }}" class="xanhworld_order_item_btn xanhworld_order_item_btn_view">

                                        đŸ'ï¸ Xem chi tiết

                                    </a>

                                    @if($order->shipping_partner === 'ghn' && $order->shipping_tracking_code)

                                        <a href="{{ route('client.order.track', ['tracking_code' => $order->shipping_tracking_code]) }}" class="xanhworld_order_item_btn xanhworld_order_item_btn_secondary">

                                            đŸ"¦ Tra cứu GHN

                                        </a>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>



                    <!-- Pagination -->

                    <div class="xanhworld_order_pagination">

                        {{ $orders->links() }}

                    </div>

                @else

                    <div class="xanhworld_order_empty">

                        <div class="xanhworld_order_empty_icon">đŸ"¦</div>

                        <h2>Chưa cĂ³ đơn hĂ ng nĂ o</h2>

                        <p>Bạn chưa cĂ³ đơn hĂ ng nĂ o. HĂ£y mua sắm ngay để cĂ³ đơn hĂ ng đầu tiĂªn!</p>

                        <a href="{{ route('client.shop.index') }}" class="xanhworld_order_empty_btn">đŸ›' Mua sắm ngay</a>

                    </div>

                @endif

            </div>

        </section>

    </div>



    @include('clients.templates.chat')

@endsection











