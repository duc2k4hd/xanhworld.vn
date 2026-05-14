(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('commentForm');
        if (!form) {
            return;
        }

        const storeUrl = form.dataset.storeUrl;
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        const stars = document.querySelectorAll('.rating-input .star');
        const ratingText = document.getElementById('ratingText');
        const ratingError = document.getElementById('ratingError');
        const submitBtn = form.querySelector('button[type="submit"]');
        const contentTextarea = document.getElementById('commentContent');
        const charCount = document.getElementById('charCount');
        let selectedRating = 0;

        if (!submitBtn || !storeUrl) {
            return;
        }

        submitBtn.disabled = true;

        if (contentTextarea && charCount) {
            const updateCharCount = function () {
                const length = contentTextarea.value.length;
                charCount.textContent = length;

                if (length > 200) {
                    charCount.style.color = '#d32f2f';
                } else if (length > 180) {
                    charCount.style.color = '#ff9800';
                } else {
                    charCount.style.color = '#666';
                }
            };

            contentTextarea.addEventListener('input', updateCharCount);
            updateCharCount();
        }

        stars.forEach(function (star, index) {
            const rating = index + 1;

            star.addEventListener('click', function () {
                selectedRating = rating;

                stars.forEach(function (item, itemIndex) {
                    if (itemIndex < rating) {
                        item.classList.add('filled');
                    } else {
                        item.classList.remove('filled');
                    }
                });

                const ratingInput = document.getElementById('rating' + rating);
                if (ratingInput) {
                    ratingInput.checked = true;
                }

                if (ratingText) {
                    ratingText.textContent = rating + ' sao';
                }

                if (ratingError) {
                    ratingError.style.display = 'none';
                }

                submitBtn.disabled = false;
            });

            star.addEventListener('mouseenter', function () {
                stars.forEach(function (item, itemIndex) {
                    if (itemIndex < rating) {
                        item.style.opacity = '0.7';
                    }
                });
            });

            star.addEventListener('mouseleave', function () {
                stars.forEach(function (item) {
                    item.style.opacity = '1';
                });
            });
        });

        ratingInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                selectedRating = parseInt(this.value, 10) || 0;
            });
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!selectedRating) {
                if (ratingError) {
                    ratingError.textContent = 'Vui lòng chọn đánh giá';
                    ratingError.style.display = 'block';
                }

                return;
            }

            const formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang gửi...';

            try {
                const response = await fetch(storeUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    showCustomToast('Bình luận của bạn đã được gửi và đang chờ duyệt.');
                    form.reset();
                    selectedRating = 0;

                    stars.forEach(function (star) {
                        star.classList.remove('filled');
                    });

                    if (ratingText) {
                        ratingText.textContent = 'Chọn số sao';
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Gửi bình luận';

                    if (charCount) {
                        charCount.textContent = '0';
                        charCount.style.color = '#666';
                    }

                    if (typeof window.reloadComments === 'function') {
                        window.reloadComments();
                    } else {
                        location.reload();
                    }

                    return;
                }

                if (data.errors && data.errors.rating && ratingError) {
                    ratingError.textContent = data.errors.rating[0];
                    ratingError.style.display = 'block';
                } else {
                    showCustomToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                }
            } catch (error) {
                console.error('Error:', error);
                showCustomToast('Có lỗi xảy ra. Vui lòng thử lại.');
            } finally {
                if (!form.matches(':invalid') && selectedRating) {
                    submitBtn.disabled = false;
                }

                submitBtn.textContent = 'Gửi bình luận';
            }
        });

        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (!loadMoreBtn) {
            return;
        }

        loadMoreBtn.addEventListener('click', async function () {
            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            const commentsList = document.getElementById('commentsList');
            const loadMoreUrl = btn.dataset.loadMoreUrl;
            const type = btn.dataset.type;
            const objectId = btn.dataset.objectId;
            const offset = parseInt(btn.dataset.offset || '0', 10);

            if (!commentsList || !loadMoreUrl) {
                return;
            }

            btn.disabled = true;
            if (btnText) {
                btnText.style.display = 'none';
            }
            if (btnLoading) {
                btnLoading.style.display = 'inline';
            }

            try {
                const query = new URLSearchParams({
                    type: type,
                    object_id: objectId,
                    offset: String(offset),
                });

                const response = await fetch(loadMoreUrl + '?' + query.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach(function (comment) {
                        commentsList.insertAdjacentHTML('beforeend', renderComment(comment));
                    });

                    if (data.nextOffset !== null) {
                        btn.dataset.offset = data.nextOffset;

                        if (btnText) {
                            btnText.textContent = 'Xem thêm bình luận (' + (data.total - data.nextOffset) + ' còn lại)';
                            btnText.style.display = 'inline';
                        }

                        if (btnLoading) {
                            btnLoading.style.display = 'none';
                        }

                        btn.disabled = false;
                    } else {
                        const loadMoreWrap = document.getElementById('commentsLoadMore');
                        if (loadMoreWrap) {
                            loadMoreWrap.remove();
                        }
                    }

                    return;
                }

                const loadMoreWrap = document.getElementById('commentsLoadMore');
                if (loadMoreWrap) {
                    loadMoreWrap.remove();
                }
            } catch (error) {
                console.error('Error loading more comments:', error);
                showCustomToast('Có lỗi xảy ra khi tải thêm bình luận. Vui lòng thử lại.');
                btn.disabled = false;
                if (btnText) {
                    btnText.style.display = 'inline';
                }
                if (btnLoading) {
                    btnLoading.style.display = 'none';
                }
            }
        });

        function renderComment(comment) {
            const userName = comment.account && comment.account.name ? comment.account.name : (comment.name || 'Người dùng');
            const userInitial = userName.charAt(0).toUpperCase();
            const rating = comment.rating || 0;
            const ratingStars = '⭐'.repeat(rating);
            const date = new Date(comment.created_at).toLocaleDateString('vi-VN');

            let adminReplyHtml = '';
            if (comment.admin_reply) {
                const adminName = comment.admin_reply.account && comment.admin_reply.account.name ? comment.admin_reply.account.name : 'Admin';
                const adminInitial = adminName.charAt(0).toUpperCase();
                const adminDate = new Date(comment.admin_reply.created_at).toLocaleDateString('vi-VN');

                adminReplyHtml = `
                    <div class="comment-reply">
                        <div class="comment-reply-avatar">
                            ${adminInitial}
                            <span class="admin-badge">QTV</span>
                        </div>
                        <div class="comment-reply-info">
                            <div class="comment-reply-header">
                                <strong class="comment-reply-name">Quản Trị Viên</strong>
                                <span class="comment-reply-date">${adminDate}</span>
                            </div>
                            <div class="comment-reply-content">${comment.admin_reply.content || ''}</div>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="comment-item" data-comment-id="${comment.id}">
                    <div class="comment-user">
                        <div class="comment-avatar">${userInitial}</div>
                        <div class="comment-info">
                            <div class="comment-header">
                                <strong class="comment-name">${userName}</strong>
                                ${rating > 0 ? `<span class="comment-rating">${ratingStars}</span>` : ''}
                            </div>
                            <div class="comment-meta">
                                <span class="comment-date">${date}</span>
                            </div>
                            <div class="comment-content">${comment.content || ''}</div>
                        </div>
                    </div>
                    ${adminReplyHtml}
                </div>
            `;
        }
    });
})();
