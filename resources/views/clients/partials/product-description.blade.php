@php
    $description = $product->description;
    $isStructured = is_array($description) && isset($description['sections']);
@endphp

@if($isStructured)
    <div class="product-description-structured">
        @foreach($description['sections'] as $index => $section)
            @php
                $hasMedia = !empty($section['media']['url']);
                $mediaType = $section['media']['type'] ?? 'image';
                $mediaUrl = $section['media']['url'] ?? '';
                $isEven = $loop->iteration % 2 == 0;
                // If even: Image-Content. If odd: Content-Image
                // But for "zigzag", usually we want row 1: Content - Image, row 2: Image - Content.
                // Or user said: "ví dụ có 5 key thì hàng 1 sẽ là content bên trái ảnh bên phải, tiếp hàng 2 sẽ là ảnh bên trái và content bên phải"
                // Row 1 (odd): Content - Image
                // Row 2 (even): Image - Content
                $rowClass = $isEven ? 'image-left' : 'content-left';
            @endphp

            <div class="description-section {{ $rowClass }} {{ !$hasMedia ? 'no-media' : '' }}">
                <div class="description-content">
                    @if(!empty($section['title']))
                        <h3 class="section-title text-uppercase">{{ $section['title'] }}</h3>
                    @endif
                    <div class="section-body">
                        {!! $section['content'] ?? '' !!}
                    </div>
                </div>

                @if($hasMedia)
                    <div class="description-media">
                        @if($mediaType == 'image')
                            <img src="{{ asset('clients/assets/img/clothes/'.$mediaUrl) }}" 
                                 alt="{{ $section['title'] ?? $product->name }}"
                                 loading="lazy">
                        @elseif($mediaType == 'video')
                            <video controls loading="lazy">
                                <source src="{{ asset('clients/assets/img/clothes/'.$mediaUrl) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    {{-- Legacy Content --}}
    <div class="product-description">
        {!! $description ?? '<p>Chưa có mô tả cho sản phẩm này.</p>' !!}
        
        <div class="xanhworld_single_info_images_tags">
            <h4 class="xanhworld_single_info_images_tags_title">Thẻ: </h4>
            @if ($product->tags?->isNotEmpty())
                @foreach ($product->tags as $tag)
                    <a href="{{ route('client.shop.index', ['tags' => $tag->slug]) }}" title="Xem tất cả sản phẩm có thẻ {{ $tag->name }}">
                        <span class="xanhworld_single_info_images_tags_tag">#{{ $tag->name ?? 'thoi-trang' }}</span>
                    </a>
                @endforeach
            @endif
        </div>

        {{-- FAQS --}}
        @include('clients.templates.faqs')
    </div>
@endif
