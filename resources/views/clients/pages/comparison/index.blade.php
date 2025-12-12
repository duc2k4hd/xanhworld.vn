@extends('clients.layouts.master')

@section('title', 'So sánh sản phẩm')

@section('head')
    <link rel="stylesheet" href="{{ asset('clients/assets/css/main.css') }}">
@endsection

@section('content')
<div class="container py-5">
    <h2 class="mb-4">So sánh sản phẩm</h2>

    @if($products->isEmpty())
        <div class="alert alert-info">
            <p>Bạn chưa có sản phẩm nào để so sánh.</p>
            <a href="{{ route('client.shop.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tiêu chí</th>
                        @foreach($products as $product)
                            <th>
                                <div class="text-center">
                                    <img src="{{ $product->primaryImage->url ?? asset('clients/assets/img/no-image.jpg') }}" 
                                         alt="{{ $product->name }}" 
                                         style="max-width: 100px; height: auto;" 
                                         class="mb-2">
                                    <h6>{{ $product->name }}</h6>
                                    <p class="text-muted mb-2">{{ $product->sku }}</p>
                                    <a href="{{ route('client.product.detail', $product->slug) }}" class="btn btn-sm btn-primary mb-2">Xem chi tiết</a>
                                    <button class="btn btn-sm btn-danger remove-comparison" data-product-id="{{ $product->id }}">
                                        <i class="fas fa-times"></i> Xóa
                                    </button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Giá</strong></td>
                        @foreach($products as $product)
                            <td>
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-danger fw-bold">{{ number_format($product->sale_price, 0, ',', '.') }} đ</span>
                                    <br><small class="text-muted text-decoration-line-through">{{ number_format($product->price, 0, ',', '.') }} đ</small>
                                @else
                                    <span class="fw-bold">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Đánh giá</strong></td>
                        @foreach($products as $product)
                            <td>
                                <div class="d-flex align-items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $product->display_rating_star ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-2">({{ $product->display_review_count }} đánh giá)</span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Tồn kho</strong></td>
                        @foreach($products as $product)
                            <td>
                                @if($product->stock_quantity === null)
                                    <span class="badge bg-success">Còn hàng</span>
                                @elseif($product->stock_quantity > 0)
                                    <span class="badge bg-success">Còn {{ $product->stock_quantity }} sản phẩm</span>
                                @else
                                    <span class="badge bg-danger">Hết hàng</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Danh mục</strong></td>
                        @foreach($products as $product)
                            <td>{{ $product->primaryCategory->name ?? 'N/A' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Mô tả ngắn</strong></td>
                        @foreach($products as $product)
                            <td>{{ Str::limit(strip_tags($product->short_description ?? $product->description ?? ''), 100) }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <button class="btn btn-danger" id="clear-comparison">
                <i class="fas fa-trash"></i> Xóa tất cả
            </button>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove product from comparison
    document.querySelectorAll('.remove-comparison').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            fetch(`/so-sanh/${productId}/remove`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        });
    });

    // Clear all
    document.getElementById('clear-comparison')?.addEventListener('click', function() {
        if(confirm('Xóa tất cả sản phẩm khỏi danh sách so sánh?')) {
            fetch('/so-sanh/clear', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush
@endsection

