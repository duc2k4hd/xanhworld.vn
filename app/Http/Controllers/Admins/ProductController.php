<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Image;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tag;
use App\Services\Admin\ProductService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected ActivityLogService $activityLogService
    ) {}

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('primaryCategory')
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('sku', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        return view('admins.products.index', compact('products'));
    }

    public function create(): View
    {
        // Chỉ lấy tags của products (entity_type = Product::class)
        $productTags = Tag::where('entity_type', Product::class)
            ->select('id', 'name')
            ->distinct('name')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        return view('admins.products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
            'tags' => $productTags,
            'mediaImages' => $this->getMediaImages(100, 0)['data'],
            'siteUrl' => $this->getSiteUrl(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            // Log activity
            $this->activityLogService->logCreate($product, 'Tạo sản phẩm mới: '.$product->name);

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Tạo sản phẩm thành công');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Không thể tạo sản phẩm: '.$e->getMessage());
        }
    }

    public function show(Product $product): View
    {
        $product->load(['primaryCategory', 'faqs', 'howTos']);

        return view('admins.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        if ($response = $this->handleEditingLock($product, true)) {
            return $response;
        }

        // Load images từ image_ids
        $product->load(['primaryCategory', 'faqs', 'howTos']);

        // Chỉ lấy tags của products (entity_type = Product::class)
        $productTags = Tag::where('entity_type', Product::class)
            ->select('id', 'name')
            ->distinct('name')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        return view('admins.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'tags' => $productTags,
            'mediaImages' => $this->getMediaImages(100, 0)['data'],
            'siteUrl' => $this->getSiteUrl(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        if ($response = $this->handleEditingLock($product, false)) {
            return $response;
        }

        try {
            $oldData = $product->toArray();
            $this->productService->update($product, $request->validated());
            $this->releaseEditingLock($product);

            // Log activity
            $this->activityLogService->logUpdate($product->fresh(), $oldData, 'Cập nhật sản phẩm: '.$product->name);

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Cập nhật sản phẩm thành công');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật: '.$e->getMessage());
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($response = $this->handleEditingLock($product, false)) {
            return $response;
        }

        // Log activity before delete
        $this->activityLogService->logDelete($product, 'Xóa sản phẩm: '.$product->name);

        $this->releaseEditingLock($product);
        $this->productService->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã chuyển sản phẩm sang trạng thái tạm ẩn');
    }

    public function restore(Request $request, Product $product): RedirectResponse
    {
        try {
            // Khôi phục sản phẩm bằng cách set is_active = true
            $product->update(['is_active' => true]);

            return redirect()
                ->route('admin.products.index', ['status' => 'inactive'])
                ->with('success', 'Đã khôi phục sản phẩm (đang ở trạng thái tạm ẩn, cần bật Đang bán nếu muốn hiển thị).');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with('error', 'Không thể khôi phục: '.$e->getMessage());
        }
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:products,id'],
            'bulk_action' => ['required', 'in:hide,delete'],
        ]);

        $productIds = $request->input('selected', []);
        $action = $request->input('bulk_action');

        if ($action === 'hide') {
            Product::whereIn('id', $productIds)->update(['is_active' => false]);

            return back()->with('success', 'Đã chuyển '.count($productIds).' sản phẩm sang trạng thái tạm ẩn.');
        }

        if ($action === 'delete') {
            foreach (Product::whereIn('id', $productIds)->get() as $product) {
                $this->productService->delete($product);
            }

            return back()->with('success', 'Đã xóa mềm '.count($productIds).' sản phẩm.');
        }

        return back()->with('error', 'Hành động không hợp lệ.');
    }

    public function inventory(Product $product): View
    {
        $movements = InventoryMovement::query()
            ->with('account')
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admins.products.inventory', compact('product', 'movements'));
    }

    public function inventoryAdjust(Product $product, \Illuminate\Http\Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:increase,decrease,set'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $current = (int) ($product->stock_quantity ?? 0);
        $qty = (int) $data['quantity'];

        if ($data['action'] === 'set') {
            $change = $qty - $current;
            $type = 'adjust';
        } elseif ($data['action'] === 'increase') {
            $change = $qty;
            $type = 'import';
        } else {
            $change = -$qty;
            $type = 'export';
        }

        try {
            /** @var \App\Models\Account|null $actor */
            $actor = auth('admin')->user() ?? auth('web')->user();

            app(\App\Services\InventoryService::class)->adjustStock(
                $product,
                $change,
                $type,
                $actor,
                null,
                null,
                $data['note'] ?? null
            );

            return redirect()
                ->route('admin.products.inventory', $product)
                ->with('success', 'Đã cập nhật tồn kho sản phẩm.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Không thể cập nhật tồn kho: '.$e->getMessage());
        }
    }

    /**
     * Release lock via API (khi đóng trang hoặc navigate away)
     */
    public function releaseLock(Product $product): JsonResponse
    {
        $this->releaseEditingLock($product);

        return response()->json([
            'success' => true,
            'message' => 'Lock đã được release thành công',
        ]);
    }

    /**
     * API endpoint để load thêm ảnh (pagination)
     */
    public function getMediaImagesApi(): JsonResponse
    {
        $limit = (int) request('limit', 100);
        $offset = (int) request('offset', 0);
        $search = request('search');

        $result = $this->getMediaImages($limit, $offset, $search);

        return response()->json($result);
    }

    /**
     * Upload ảnh đã crop từ TinyMCE editor
     */
    public function uploadCroppedImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:webp,jpg,jpeg,png,gif', 'max:5120'], // 5MB
            'original_filename' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $file = $request->file('image');
            $originalFilename = $request->input('original_filename', 'cropped-image');

            // Extract base name and extension
            $originalBaseName = pathinfo($originalFilename, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            // Get image dimensions
            $imageInfo = getimagesize($file->getRealPath());
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Build new filename: baseFilename-size-w-h.extension
            // If filename already contains -size-w-h, replace it; otherwise add it
            $newFilename = preg_replace(
                '/-size-\d+-\d+$/',
                '',
                $originalBaseName
            ).'-size-'.$width.'-'.$height.'.'.$extension;

            // Save to public/clients/assets/img/clothes/
            $destination = public_path('clients/assets/img/clothes');
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $newFilename);

            // Build full URL
            $baseUrl = $this->getSiteUrl();
            $fullUrl = rtrim($baseUrl, '/').'/clients/assets/img/clothes/'.$newFilename;

            return response()->json([
                'success' => true,
                'filename' => $newFilename,
                'url' => $fullUrl,
                'width' => $width,
                'height' => $height,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Không thể upload ảnh: '.$e->getMessage(),
            ], 500);
        }
    }

    private function getMediaImages(int $limit = 100, int $offset = 0, ?string $search = null): array
    {
        $root = public_path('clients/assets/img');
        $baseUrl = $this->getSiteUrl();

        // Lấy tất cả file ảnh (đệ quy) trong thư mục img
        $allFiles = [];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp', 'ico'];
        if (is_dir($root)) {
            foreach (File::allFiles($root) as $file) {
                $extension = strtolower($file->getExtension());
                // Chỉ lấy file ảnh
                if (!in_array($extension, $imageExtensions)) {
                    continue;
                }
                
                $filename = $file->getFilename(); // chỉ tên file
                $relative = str_replace(public_path(), '', $file->getRealPath());
                $relative = str_replace('\\', '/', $relative);
                $relative = ltrim($relative, '/');
                $fullUrl = rtrim($baseUrl, '/').'/'.$relative;

                $filePath = $file->getRealPath();
                $mimeType = 'image/jpeg';
                if (function_exists('mime_content_type') && file_exists($filePath)) {
                    try {
                        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
                    } catch (\Throwable $e) {
                        // Fallback to extension-based mime type
                        $mimeType = 'image/'.$extension;
                    }
                }
                
                $allFiles[$filename] = [
                    'name' => $filename,
                    'url' => $fullUrl, // URL đầy đủ
                    'path' => $relative, // đường dẫn tương đối
                    'title' => null,
                    'alt' => null,
                    'size' => $file->getSize(),
                    'mime_type' => $mimeType,
                ];
            }
        }

        // Load thông tin từ bảng images (title, alt)
        $images = Image::whereIn('url', array_keys($allFiles))->get()->keyBy('url');
        foreach ($images as $image) {
            if (isset($allFiles[$image->url])) {
                $allFiles[$image->url]['title'] = $image->title;
                $allFiles[$image->url]['alt'] = $image->alt;
            }
        }

        // Nếu có search, filter theo title và alt (tách từng từ)
        if ($search && ! empty(trim($search))) {
            $searchTerms = $this->parseSearchTerms($search);
            $filteredFiles = [];

            foreach ($allFiles as $filename => $file) {
                $title = strtolower($file['title'] ?? '');
                $alt = strtolower($file['alt'] ?? '');
                $name = strtolower($filename);

                // Kiểm tra xem có bất kỳ từ nào trong search terms khớp với title, alt hoặc tên file không
                $matches = false;
                foreach ($searchTerms as $term) {
                    if (str_contains($title, $term) || str_contains($alt, $term) || str_contains($name, $term)) {
                        $matches = true;
                        break;
                    }
                }

                if ($matches) {
                    $filteredFiles[$filename] = $file;
                }
            }

            $allFiles = $filteredFiles;
        }

        // Convert to array and sort by name
        $files = array_values($allFiles);
        usort($files, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $total = count($files);
        $files = array_slice($files, $offset, $limit);

        return [
            'data' => $files,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
        ];
    }

    /**
     * Parse search terms: tách từng từ ra để tìm rộng
     * Ví dụ: "áo polo nam" -> ["áo", "polo", "nam"]
     * Nếu chỉ có 1 từ thì vẫn trả về mảng có 1 phần tử
     */
    private function parseSearchTerms(string $search): array
    {
        // Loại bỏ khoảng trắng thừa và chuyển sang lowercase
        $search = trim($search);
        if (empty($search)) {
            return [];
        }

        $search = mb_strtolower($search, 'UTF-8');

        // Tách theo khoảng trắng và loại bỏ các từ rỗng
        $terms = preg_split('/\s+/u', $search);
        $terms = array_filter($terms, fn ($term) => ! empty(trim($term)) && mb_strlen(trim($term), 'UTF-8') > 0);

        $result = array_values($terms);

        // Nếu không tách được từ nào (chỉ có 1 từ), trả về chính từ đó
        if (empty($result)) {
            $result = [$search];
        }

        return $result;
    }

    private function getSiteUrl(): string
    {
        $siteUrl = Setting::where('key', 'site_url')->value('value') ?? config('app.url');
        if (! $siteUrl) {
            $siteUrl = config('app.url');
        }

        return rtrim($siteUrl, '/');
    }

    protected function handleEditingLock(Product $product, bool $acquireLock = true): ?RedirectResponse
    {
        $currentUser = auth('web')->user();
        $lockTtl = now()->subMinutes((int) config('app.editor_lock_minutes', 15));

        $product->loadMissing('lockedByUser');

        // Tự động release lock nếu đã hết hạn
        if ($product->locked_by && $product->locked_at) {
            if ($product->locked_at->lessThanOrEqualTo($lockTtl)) {
                $product->forceFill([
                    'locked_by' => null,
                    'locked_at' => null,
                ])->save();
                $product->refresh();
            }
        }

        // Nếu lock là của chính user hiện tại, LUÔN cho phép
        if ($product->locked_by && (int) $product->locked_by === (int) $currentUser->id) {
            if ($acquireLock) {
                $product->forceFill([
                    'locked_by' => $currentUser->id,
                    'locked_at' => now(),
                ])->save();
            }

            return null;
        }

        // Kiểm tra lock còn hiệu lực và không phải của user hiện tại
        if ($product->locked_by && (int) $product->locked_by !== (int) $currentUser->id) {
            $lockedAt = $product->locked_at;
            if ($lockedAt && $lockedAt->greaterThan($lockTtl)) {
                $lockedBy = optional($product->lockedByUser)->name ?? 'người dùng khác';

                return redirect()
                    ->route('admin.products.index')
                    ->with('error', "Sản phẩm đang được {$lockedBy} chỉnh sửa. Vui lòng thử lại sau vài phút.");
            }
        }

        // Tạo lock mới nếu cần (khi vào trang edit)
        if ($acquireLock) {
            $product->forceFill([
                'locked_by' => $currentUser->id,
                'locked_at' => now(),
            ])->save();
        }

        return null;
    }

    protected function releaseEditingLock(Product $product): void
    {
        if ($product->locked_by && $product->locked_by === auth('web')->id()) {
            $product->forceFill([
                'locked_by' => null,
                'locked_at' => null,
            ])->save();
        }
    }
}
