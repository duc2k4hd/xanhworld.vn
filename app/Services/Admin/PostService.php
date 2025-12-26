<?php

namespace App\Services\Admin;

use App\Models\Account;
use App\Models\Image;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    /**
     * Tạo bài viết mới
     */
    public function create(array $data, Account $user): Post
    {
        return DB::transaction(function () use ($data, $user) {
            // Tạo slug nếu chưa có
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            } else {
                $data['slug'] = $this->generateUniqueSlug($data['slug']);
            }

            // Nếu để trống canonical URL thì tự set theo slug
            if (empty($data['meta_canonical'])) {
                $data['meta_canonical'] = '/kinh-nghiem/'.$data['slug'];
            }

            // Set created_by và account_id
            $data['created_by'] = $user->id;
            if (empty($data['account_id'])) {
                $data['account_id'] = $user->id;
            }

            // Set published_at nếu status là published
            if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            // Lưu tag_ids và tag_names để sync sau
            $tagIds = Arr::get($data, 'tag_ids', []);
            $tagNames = Arr::get($data, 'tag_names');
            unset($data['tag_ids'], $data['tag_names']);

            // Lưu image_ids để sync sau
            $imageIds = Arr::get($data, 'image_ids', []);
            unset($data['image_ids']);

            $post = Post::create($data);

            // Sync images
            $this->syncImages($post, $imageIds);

            // Sync tags với bảng tags (sẽ tạo tags mới và cập nhật tag_ids)
            $this->syncTags($post, $tagIds, $tagNames);

            return $post->fresh();
        });
    }

    /**
     * Cập nhật bài viết
     */
    public function update(Post $post, array $data, Account $user): Post
    {
        return DB::transaction(function () use ($post, $data) {
            // Update slug nếu thay đổi title hoặc slug
            if (isset($data['title']) && $data['title'] !== $post->title) {
                if (empty($data['slug']) || $data['slug'] === $post->slug) {
                    $data['slug'] = $this->generateUniqueSlug($data['title'], $post->id);
                }
            } elseif (isset($data['slug']) && $data['slug'] !== $post->slug) {
                $data['slug'] = $this->generateUniqueSlug($data['slug'], $post->id);
            }

            // Nếu meta_canonical trống sau khi xử lý slug thì tự set lại
            if (empty($data['meta_canonical'])) {
                $data['meta_canonical'] = '/kinh-nghiem/'.($data['slug'] ?? $post->slug);
            }

            // Lưu tag_ids và tag_names để sync sau
            $tagIds = Arr::get($data, 'tag_ids', $post->tag_ids ?? []);
            $tagNames = Arr::get($data, 'tag_names');
            $shouldSyncTags = isset($data['tag_ids']) || isset($data['tag_names']);
            unset($data['tag_ids'], $data['tag_names']);

            // Lưu image_ids để sync sau
            $imageIds = Arr::get($data, 'image_ids', []);
            $shouldSyncImages = isset($data['image_ids']);
            unset($data['image_ids']);

            // Set published_at nếu status chuyển sang published
            if (($data['status'] ?? $post->status) === 'published' && empty($data['published_at']) && ! $post->published_at) {
                $data['published_at'] = now();
            }

            $post->update($data);

            // Sync images nếu có thay đổi
            if ($shouldSyncImages) {
                $this->syncImages($post, $imageIds);
            }

            // Sync tags nếu có thay đổi
            if ($shouldSyncTags) {
                $this->syncTags($post, $tagIds, $tagNames);
            }

            return $post->fresh();
        });
    }

    /**
     * Xóa bài viết (soft delete)
     */
    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    /**
     * Nhân bản bài viết
     */
    public function duplicate(Post $post, Account $user): Post
    {
        return DB::transaction(function () use ($post, $user) {
            $newPost = $post->replicate();
            $newPost->title = $post->title.' (Bản sao)';
            $newPost->slug = $this->generateUniqueSlug($post->slug);
            $newPost->status = 'draft';
            $newPost->published_at = null;
            $newPost->views = 0;
            $newPost->created_by = $user->id;
            $newPost->account_id = $user->id;
            $newPost->save();

            // Sync tags
            if ($post->tag_ids) {
                $this->syncTags($newPost, $post->tag_ids, null);
            }

            return $newPost;
        });
    }

    /**
     * Autosave revision
     */
    public function autosave(Post $post, array $data, Account $user): PostRevision
    {
        // Xóa autosave cũ của post này
        PostRevision::where('post_id', $post->id)
            ->where('is_autosave', true)
            ->delete();

        return PostRevision::create([
            'post_id' => $post->id,
            'edited_by' => $user->id,
            'title' => $data['title'] ?? $post->title,
            'content' => $data['content'] ?? $post->content,
            'excerpt' => $data['excerpt'] ?? $post->excerpt,
            'meta' => [
                'tag_ids' => $data['tag_ids'] ?? $post->tag_ids,
                'category_id' => $data['category_id'] ?? $post->category_id,
                'meta_title' => $data['meta_title'] ?? $post->meta_title,
                'meta_description' => $data['meta_description'] ?? $post->meta_description,
            ],
            'is_autosave' => true,
        ]);
    }

    /**
     * Khôi phục từ revision
     */
    public function restoreRevision(Post $post, PostRevision $revision, Account $user): Post
    {
        return DB::transaction(function () use ($post, $revision, $user) {
            $data = [
                'title' => $revision->title ?? $post->title,
                'content' => $revision->content ?? $post->content,
                'excerpt' => $revision->excerpt ?? $post->excerpt,
            ];

            if ($revision->meta) {
                $meta = $revision->meta;
                if (isset($meta['tag_ids'])) {
                    $data['tag_ids'] = $meta['tag_ids'];
                }
                if (isset($meta['category_id'])) {
                    $data['category_id'] = $meta['category_id'];
                }
                if (isset($meta['meta_title'])) {
                    $data['meta_title'] = $meta['meta_title'];
                }
                if (isset($meta['meta_description'])) {
                    $data['meta_description'] = $meta['meta_description'];
                }
            }

            return $this->update($post, $data, $user);
        });
    }

    /**
     * Tạo slug unique
     */
    protected function generateUniqueSlug(string $text, ?int $excludeId = null): string
    {
        $slug = Str::slug($text);
        $baseSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Sync tags với bảng tags
     */
    protected function syncTags(Post $post, array $tagIds, ?string $tagNames = null): void
    {
        // Xóa tất cả tags cũ của post này
        Tag::where('entity_type', Post::class)
            ->where('entity_id', $post->id)
            ->delete();

        // Xử lý tag names từ input (tags mới)
        $allTagNames = [];
        if (! empty($tagNames)) {
            $newTagNames = $this->parseTagNames($tagNames);
            $allTagNames = array_merge($allTagNames, $newTagNames);
        }

        // Nếu không có tagIds và không có tagNames, xóa hết tags
        if (empty($tagIds) && empty($allTagNames)) {
            $post->tag_ids = null;
            $post->saveQuietly();

            return;
        }

        // Lấy thông tin tags từ tagIds (có thể từ posts khác)
        $existingTags = [];
        if (! empty($tagIds)) {
            $existingTags = Tag::whereIn('id', $tagIds)
                ->where('entity_type', Post::class)
                ->select('id', 'name', 'slug', 'description', 'is_active')
                ->get()
                ->unique('name')
                ->keyBy('id');

            // Lấy thêm tag names từ existing tags
            foreach ($existingTags as $tag) {
                $allTagNames[] = $tag->name;
            }
        }

        // Loại bỏ duplicate và tạo tags
        $allTagNames = array_unique(array_map('trim', $allTagNames));
        $createdTagIds = [];

        foreach ($allTagNames as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            // Tìm tag template (có thể từ posts khác)
            $templateTag = Tag::where('entity_type', Post::class)
                ->where('name', $tagName)
                ->first();

            // Tạo tag mới với entity_type và entity_id cho post này
            $baseSlug = Str::slug($tagName);
            $uniqueSlug = $baseSlug.'-post-'.$post->id;

            // Đảm bảo slug unique
            $counter = 1;
            while (Tag::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $baseSlug.'-post-'.$post->id.'-'.$counter;
                $counter++;
            }

            $newTag = Tag::create([
                'name' => $tagName,
                'slug' => $uniqueSlug,
                'description' => $templateTag->description ?? null,
                'is_active' => $templateTag->is_active ?? true,
                'usage_count' => 0,
                'entity_id' => $post->id,
                'entity_type' => Post::class,
            ]);
            $createdTagIds[] = $newTag->id;
        }

        // Cập nhật tag_ids trong post
        $post->tag_ids = ! empty($createdTagIds) ? $createdTagIds : null;
        $post->saveQuietly();
    }

    /**
     * Parse tag names từ string (phân cách bằng dấu phẩy)
     */
    protected function parseTagNames(string $tagNames): array
    {
        return array_filter(
            array_map('trim', explode(',', $tagNames)),
            fn ($name) => ! empty($name)
        );
    }

    /**
     * Sync images với bảng images
     */
    protected function syncImages(Post $post, array $imageIds): void
    {
        $keepIds = [];

        foreach ($imageIds as $order => $imageData) {
            // Nếu là Image ID (số nguyên)
            if (is_numeric($imageData)) {
                $imageId = (int) $imageData;
                $image = Image::find($imageId);
                if ($image) {
                    $keepIds[] = $image->id;
                }

                continue;
            }

            // Nếu là tên file (string)
            if (is_string($imageData) && ! empty(trim($imageData))) {
                $filename = trim($imageData);

                // Loại bỏ path nếu có, chỉ lấy tên file
                $filename = basename($filename);

                // Tìm Image record có url = filename hoặc url chứa filename trong path posts
                $image = Image::where('url', $filename)
                    ->orWhere('url', 'like', '%/posts/'.$filename)
                    ->orWhere('url', 'like', 'posts/'.$filename)
                    ->first();

                if ($image) {
                    // Cập nhật url nếu cần để đảm bảo đúng format
                    if (! Str::contains($image->url, 'posts/')) {
                        $image->url = $filename;
                        $image->saveQuietly();
                    }
                    $keepIds[] = $image->id;
                } else {
                    // Tạo Image record mới với chỉ tên file (không có path)
                    $newImage = Image::create([
                        'url' => $filename,
                        'title' => null,
                        'alt' => null,
                        'is_primary' => $order === 0,
                        'order' => $order,
                    ]);
                    $keepIds[] = $newImage->id;
                }
            }
        }

        // Cập nhật image_ids trong post
        $post->image_ids = ! empty($keepIds) ? $keepIds : null;
        $post->saveQuietly();
    }
}
