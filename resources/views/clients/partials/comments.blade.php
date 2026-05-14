@once
    @push('css_page')
        <link rel="stylesheet" href="{{ asset('clients/assets/css/comments.css?v='. time()) }}">
    @endpush

    @push('js_page')
        <script defer src="{{ asset('clients/assets/js/comments.js?v='. time()) }}"></script>
    @endpush
@endonce

{{-- Comments Section - Simple & Clean --}}
<div class="comments-section">
    <h3>Bình luận</h3>

    {{-- Rating Summary --}}
    @if(isset($ratingStats) && $ratingStats['total_comments'] > 0)
        <div class="rating-summary">
            <div class="rating-avg">
                <span class="rating-number">{{ number_format($ratingStats['average_rating'], 1) }}</span>
                <div class="rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($ratingStats['average_rating']))
                            ⭐
                        @else
                            ☆
                        @endif
                    @endfor
                </div>
                <span class="rating-count">({{ $ratingStats['total_comments'] }} đánh giá)</span>
            </div>
            <div class="rating-breakdown">
                @for($i = 5; $i >= 1; $i--)
                    @php
                        $count = $ratingStats['star_' . $i . '_count'] ?? 0;
                        $percentage = $ratingStats['total_comments'] > 0 
                            ? round(($count / $ratingStats['total_comments']) * 100) 
                            : 0;
                    @endphp
                    <div class="rating-row">
                        <span>{{ $i }}⭐</span>
                        <div class="rating-bar">
                            <div class="rating-bar-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span>{{ $count }}</span>
                    </div>
                @endfor
            </div>
        </div>
    @endif

    {{-- Comment Form --}}
    <div class="comment-form">
        <h4>Viết bình luận</h4>
        <form id="commentForm" data-store-url="{{ route('comments.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="object_id" value="{{ $objectId }}">

            <div class="form-group">
                <label>Đánh giá <span>*</span></label>
                <div class="rating-input" id="ratingContainer">
                    @for($i = 1; $i <= 5; $i++)
                        <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" required>
                        <label for="rating{{ $i }}" class="star" data-rating="{{ $i }}">☆</label>
                    @endfor
                    <span class="rating-text" id="ratingText">Chọn số sao</span>
                </div>
                <div class="rating-error" id="ratingError" style="display: none; color: #d32f2f; font-size: 12px; margin-top: 5px;"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tên <span>*</span></label>
                    <input type="text" name="name" value="{{ auth('web')->user()?->name ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Email <span>*</span></label>
                    <input type="email" name="email" value="{{ auth('web')->user()?->email ?? '' }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Nội dung <span>*</span></label>
                <textarea name="content" id="commentContent" rows="4" required placeholder="Nhập bình luận của bạn..." maxlength="200"></textarea>
                <div class="char-counter" style="text-align: right; margin-top: 5px; font-size: 12px; color: #666;">
                    <span id="charCount">0</span>/200 ký tự
                </div>
            </div>

            <button type="submit">Gửi bình luận</button>
        </form>
    </div>

    {{-- Comments List --}}
    <div class="comments-list" id="commentsList">
        @forelse($comments ?? [] as $comment)
            @include('clients.partials.comment-item', ['comment' => $comment])
        @empty
            <div class="comment-empty">Chưa có bình luận nào.</div>
        @endforelse
    </div>

    {{-- Load More Button --}}
    @if(isset($totalComments) && $totalComments > count($comments ?? []))
        <div class="comments-load-more" id="commentsLoadMore">
            <button type="button" class="load-more-btn" id="loadMoreBtn" data-load-more-url="{{ route('comments.load-more') }}" data-type="{{ $type }}" data-object-id="{{ $objectId }}" data-offset="{{ count($comments ?? []) }}">
                <span class="btn-text">Xem thêm bình luận ({{ $totalComments - count($comments ?? []) }} còn lại)</span>
                <span class="btn-loading" style="display: none;">Đang tải...</span>
            </button>
        </div>
    @endif
</div>
