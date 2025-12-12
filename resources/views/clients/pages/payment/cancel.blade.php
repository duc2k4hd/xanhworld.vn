@extends('clients.layouts.master')

@section('title', 'Thanh toán không thành công')

@section('head')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/main.css') }}">
@endsection

@section('content')
<div class="container" style="text-align: center; padding: 50px 0;">
    <h2>Thanh toán không thành công!</h2>
    <p>Đã có lỗi xảy ra hoặc bạn đã hủy thanh toán cho đơn hàng có mã: <strong>{{ $orderCode ?? 'N/A' }}</strong></p>
    <p>{{ $message ?? 'Vui lòng thử lại hoặc liên hệ với chúng tôi để được hỗ trợ.' }}</p>
    <a href="{{ route('client.checkout.index') }}" class="btn btn-secondary">Thử lại thanh toán</a>
    <a href="{{ route('client.home.index') }}" class="btn btn-primary">Quay về trang chủ</a>
</div>
@endsection
