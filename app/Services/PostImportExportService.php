<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\XLSX\Writer as XLSXWriter;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Common\Entity\Row;

class PostImportExportService
{
    /*
    |--------------------------------------------------------------------------
    | EXPORT TEMPLATE
    |--------------------------------------------------------------------------
    */

    public function exportTemplate(string $path): void
    {
        $writer = new XLSXWriter();
        $writer->openToFile($path);

        $headers = [
            'title','slug','status','category_slug','tags',
            'excerpt','content','image_paths','published_at',
            'created_by','meta_title','meta_description','meta_keywords',
        ];

        $writer->addRow(Row::fromValues($headers));

        $example = [
            'Ví dụ tiêu đề bài viết',
            'vi-du-tieu-de',
            'published',
            'tu-dong-hoa',
            'tag1,tag2',
            'Đoạn mô tả ngắn',
            'Nội dung bài viết đầy đủ...',
            'posts/image1.jpg,posts/image2.jpg',
            now()->toDateTimeString(),
            1,
            'Meta title ví dụ',
            'Meta description ví dụ',
            'keyword1,keyword2',
        ];

        $writer->addRow(Row::fromValues($example));
        $writer->close();
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT ALL POSTS
    |--------------------------------------------------------------------------
    */

    public function exportAll(string $path): void
    {
        $writer = new XLSXWriter();
        $writer->openToFile($path);

        $headers = [
            'id','title','slug','status','category_slug','tags',
            'excerpt','content','image_paths','published_at',
            'created_by','meta_title','meta_description','meta_keywords'
        ];

        $writer->addRow(Row::fromValues($headers));

        Post::orderBy('id')->chunk(500, function ($posts) use ($writer) {

            // 1️⃣ Gom tất cả image ids trong chunk
            $allImageIds = [];

            foreach ($posts as $post) {
                if (is_array($post->image_ids)) {
                    $allImageIds = array_merge($allImageIds, $post->image_ids);
                }
            }

            $allImageIds = array_unique($allImageIds);

            // 2️⃣ Load tất cả images 1 lần
            $imagesMap = \App\Models\Image::whereIn('id', $allImageIds)
                ->pluck('url', 'id')
                ->toArray();

            // 3️⃣ Build từng row
            foreach ($posts as $post) {

                $imageNames = [];

                if (is_array($post->image_ids)) {
                    foreach ($post->image_ids as $id) {
                        if (isset($imagesMap[$id])) {
                            $imageNames[] = $imagesMap[$id];
                        }
                    }
                }

                $row = [
                    $post->id,
                    $post->title,
                    $post->slug,
                    $post->status,
                    optional($post->category)->slug ?? '',

                    is_array($post->tag_ids)
                        ? implode(',', $this->resolveTagSlugs($post->tag_ids))
                        : '',

                    $post->excerpt ?? '',
                    $post->content ?? '',

                    implode(',', $imageNames),

                    optional($post->published_at)?->toDateTimeString() ?? '',
                    $post->created_by ?? '',
                    $post->meta_title ?? '',
                    $post->meta_description ?? '',
                    $post->meta_keywords ?? '',
                ];

                $writer->addRow(Row::fromValues($row));
            }
        });

        $writer->close();
    }

    protected function resolveTagSlugs(array $tagIds): array
    {
        return Tag::whereIn('id', $tagIds)->pluck('slug')->all();
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT FILE
    |--------------------------------------------------------------------------
    */

    public function importFromFile(string $path): array
    {
        $reader = new XLSXReader();
        $reader->open($path);

        $report = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $headers = null;
        $rowsBuffer = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {

                $cells = array_map(
                    fn($cell) => trim((string)$cell),
                    $row->toArray()
                );

                if (! $headers) {
                    $headers = array_map('strtolower', $cells);
                    continue;
                }

                $rowsBuffer[] = array_combine($headers, $cells);

                if (count($rowsBuffer) >= 200) {
                    $this->processRows($rowsBuffer, $report);
                    $rowsBuffer = [];
                }
            }
        }

        if (! empty($rowsBuffer)) {
            $this->processRows($rowsBuffer, $report);
        }

        $reader->close();

        return $report;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS ROWS
    |--------------------------------------------------------------------------
    */

    protected function processRows(array $rows, array &$report): void
    {
        foreach ($rows as $index => $data) {

            $report['processed']++;
            $rowIndex = $report['processed'];

            try {

                DB::transaction(function () use ($data, &$report, $rowIndex) {

                    /* ==============================
                    | 1. VALIDATE
                    ===============================*/

                    $title = trim($data['title'] ?? '');
                    $slug  = trim($data['slug'] ?? '');

                    if (!$title || !$slug) {
                        throw new \Exception("Title or slug missing");
                    }

                    /* ==============================
                    | 2. FIND POST
                    ===============================*/

                    $post = null;

                    if (!empty($data['id']) && is_numeric($data['id'])) {
                        $post = Post::find((int)$data['id']);
                    }

                    if (!$post) {
                        $post = Post::where('slug', $slug)->first();
                    }

                    $isNew = false;

                    if (!$post) {
                        $post = new Post();
                        $isNew = true;
                    }

                    /* ==============================
                    | 3. CATEGORY
                    ===============================*/

                    $categoryId = null;

                    if (!empty($data['category_slug'])) {

                        $category = Category::firstOrCreate(
                            ['slug' => $data['category_slug']],
                            [
                                'name' => ucfirst(
                                    str_replace('-', ' ', $data['category_slug'])
                                )
                            ]
                        );

                        $categoryId = $category->id;
                    }

                    /* ==============================
                    | 4. TAGS (MASTER TABLE)
                    ===============================*/

                    $tagIds = [];

                    if (!empty($data['tags'])) {

                        $tagSlugs = array_filter(
                            array_map('trim', explode(',', $data['tags']))
                        );

                        foreach ($tagSlugs as $tagSlug) {

                            $tag = Tag::firstOrCreate(
                                [
                                    'slug' => $slug,
                                    'entity_type' => Post::class,
                                    'entity_id' => 0 // hoặc truyền $post->id sau khi save
                                ],
                                [
                                    'name' => $slug,
                                    'is_active' => true
                                ]
                            );

                            $tagIds[] = $tag->id;
                        }
                    }

                    /* =============================
                    | IMAGE IDS (FROM FILENAME)
                    ==============================*/

                    $imageIds = [];

                    if (!empty($data['image_paths'])) {

                        $filenames = array_filter(
                            array_map('trim', explode(',', $data['image_paths']))
                        );

                        // Lấy tất cả images 1 lần (tối ưu)
                        $images = \App\Models\Image::whereIn('url', $filenames)
                            ->pluck('id', 'url')
                            ->toArray();

                        foreach ($filenames as $name) {

                            if (isset($images[$name])) {
                                $imageIds[] = $images[$name];
                            } else {
                                \Log::warning('Image not found in DB', [
                                    'row' => $rowIndex,
                                    'filename' => $name
                                ]);
                            }
                        }
                    }

                    $post->image_ids = $imageIds;

                    /* ==============================
                    | 6. MAP DATA (ONLY HEADER FIELDS)
                    ===============================*/

                    $post->title            = $title;
                    $post->slug             = $slug;
                    $post->status           = $data['status'] ?? 'draft';
                    $post->category_id      = $categoryId;
                    $post->tag_ids          = $tagIds;
                    $post->excerpt          = $data['excerpt'] ?? null;
                    $post->content          = $data['content'] ?? null;
                    $post->image_ids        = $imageIds;

                    $post->published_at = !empty($data['published_at'])
                        ? \Carbon\Carbon::parse($data['published_at'])
                        : null;

                    if ($isNew) {

                        if (empty($data['created_by'])) {
                            throw new \Exception("created_by is required");
                        }

                        $post->created_by = (int)$data['created_by'] ?? 1;
                        $post->account_id = (int)$data['created_by'] ?? 1;
                    }

                    $post->meta_title       = $data['meta_title'] ?? null;
                    $post->meta_description = $data['meta_description'] ?? null;
                    $post->meta_keywords    = $data['meta_keywords'] ?? null;
                    $post->meta_canonical   = ('/kinh-nghiem/'. $slug) ?? null;

                    $post->save();

                    $isNew
                        ? $report['created']++
                        : $report['updated']++;
                });

            } catch (\Throwable $e) {

                $report['errors'][] =
                    "Row {$rowIndex}: {$e->getMessage()}";
            }
        }
    }
}
