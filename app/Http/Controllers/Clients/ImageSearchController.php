<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ImageRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageSearchController extends Controller
{
    public function __construct(
        private ImageRecognitionService $imageRecognitionService
    ) {}

    /**
     * Xử lý tìm kiếm bằng hình ảnh
     */
    public function search(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // Max 5MB
        ]);

        try {
            // Lưu ảnh tạm thời
            $image = $request->file('image');
            $imagePath = $image->store('temp/image-search', 'public');
            $fullPath = Storage::disk('public')->path($imagePath);

            // Phân tích ảnh để tìm kiếm
            $keywords = $this->analyzeImage($fullPath);

            // Kiểm tra nếu không có keywords (API key chưa được cấu hình hoặc không tìm thấy keywords cụ thể)
            if (empty($keywords)) {
                Storage::disk('public')->delete($imagePath);

                return response()->json([
                    'success' => false,
                    'message' => 'Không thể phân tích hình ảnh. Vui lòng đảm bảo API key Gemini đã được cấu hình đúng hoặc thử lại với ảnh khác.',
                    'keywords' => [],
                    'products' => [],
                ], 400);
            }

            // Xóa ảnh tạm
            Storage::disk('public')->delete($imagePath);

            // Trả về keywords để redirect đến shop page
            // Không cần tìm kiếm products ở đây, sẽ search ở shop page
            return response()->json([
                'success' => true,
                'keywords' => $keywords,
                'products' => [], // Không cần trả về products, sẽ search ở shop page
                'message' => 'Đang chuyển đến trang kết quả tìm kiếm...',
            ]);
        } catch (\Exception $e) {
            Log::error('Image search error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại.',
            ], 500);
        }
    }

    /**
     * Phân tích ảnh để trích xuất keywords
     * Sử dụng ImageRecognitionService
     */
    protected function analyzeImage(string $imagePath): array
    {
        return $this->imageRecognitionService->analyzeImage($imagePath);
    }

    /**
     * Tìm kiếm sản phẩm dựa trên keywords
     */
    protected function searchProductsByKeywords(array $keywords): array
    {
        if (empty($keywords)) {
            return [];
        }

        Log::info('Image search keywords', ['keywords' => $keywords]);

        // Ưu tiên tìm kiếm theo tên cây cụ thể trước
        $generalTerms = [
            'cây cảnh', 'cây xanh', 'cây phong thủy', 'cây nội thất',
            'cây để bàn', 'cây trang trí', 'cây sân vườn', 'cây decor',
            'phong thủy', 'nội thất', 'để bàn', 'trang trí', 'sân vườn',
        ];

        $specificKeywords = array_filter($keywords, function ($keyword) use ($generalTerms) {
            $keywordLower = mb_strtolower(trim($keyword));

            // Loại bỏ các từ chung chung
            foreach ($generalTerms as $term) {
                if ($keywordLower === $term ||
                    $keywordLower === str_replace('cây ', '', $term) ||
                    $keywordLower === str_replace(' ', '', $term)) {
                    return false;
                }
            }

            // Loại bỏ các từ quá chung như "lá to", "thân cao"
            // NHƯNG giữ lại "hoa giấy", "hoa hồng", "hoa đào" (tên cây cụ thể)
            if (preg_match('/^(lá|thân|màu|đặc điểm|hình dáng|trang trí|phong thủy|nội thất|để bàn|sân vườn)(\s|$)/i', $keyword)) {
                return false;
            }

            // Loại bỏ "hoa" đơn lẻ hoặc "hoa [màu]" như "hoa hồng" (màu), "hoa vàng" (màu)
            // NHƯNG giữ lại "hoa giấy", "hoa đào", "hoa mai" (tên cây cụ thể)
            if (preg_match('/^hoa(\s|$)/i', $keyword)) {
                // Nếu là "hoa" đơn lẻ hoặc "hoa [màu]", loại bỏ
                $colorKeywords = ['hồng', 'vàng', 'đỏ', 'trắng', 'tím', 'xanh', 'cam', 'tím', 'nâu', 'đen'];
                $parts = explode(' ', $keywordLower);
                if (count($parts) === 1 || (count($parts) === 2 && in_array($parts[1], $colorKeywords))) {
                    return false;
                }
                // Nếu là "hoa [tên cây]" như "hoa giấy", "hoa đào", giữ lại
            }

            // Chỉ giữ lại keywords có vẻ là tên cây cụ thể (không quá dài, không quá ngắn)
            return mb_strlen($keyword) >= 3 && mb_strlen($keyword) <= 40;
        });

        // Nếu không có keywords cụ thể, thử tách tên cây từ keywords có "cây"
        if (empty($specificKeywords)) {
            foreach ($keywords as $keyword) {
                $keywordLower = mb_strtolower(trim($keyword));

                // Tìm keywords có dạng "cây [tên cụ thể]"
                if (preg_match('/^cây\s+([a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ\s]{3,30})$/iu', $keywordLower, $matches)) {
                    $plantName = trim($matches[1]);
                    // Kiểm tra tên cây không phải là từ chung
                    $isGeneral = false;
                    foreach ($generalTerms as $term) {
                        $termClean = str_replace('cây ', '', mb_strtolower($term));
                        if ($plantName === $termClean || str_contains($plantName, $termClean)) {
                            $isGeneral = true;
                            break;
                        }
                    }
                    if (! $isGeneral) {
                        $specificKeywords[] = $plantName; // Lấy tên cây không có "cây"
                    }
                } elseif (! preg_match('/^(cây|lá|thân|màu|đặc điểm|hình dáng|trang trí|phong thủy|nội thất|để bàn|sân vườn)(\s|$)/i', $keywordLower) &&
                          mb_strlen($keyword) >= 3 && mb_strlen($keyword) <= 25 &&
                          ! in_array($keywordLower, array_map('mb_strtolower', $generalTerms))) {
                    // Keywords ngắn có thể là tên cây cụ thể (không phải từ chung)
                    // Kiểm tra nếu là "hoa [màu]" thì loại bỏ, nếu là "hoa [tên cây]" thì giữ lại
                    if (preg_match('/^hoa\s+/i', $keywordLower)) {
                        $parts = explode(' ', $keywordLower);
                        $colorKeywords = ['hồng', 'vàng', 'đỏ', 'trắng', 'tím', 'xanh', 'cam', 'tím', 'nâu', 'đen'];
                        // Nếu không phải là màu, giữ lại (ví dụ: "hoa giấy", "hoa đào")
                        if (count($parts) === 2 && ! in_array($parts[1], $colorKeywords)) {
                            $specificKeywords[] = $keyword;
                        }
                    } else {
                        $specificKeywords[] = $keyword;
                    }
                }
            }
        }

        // Nếu vẫn không có keywords cụ thể, thử giữ lại keywords có "hoa" hoặc từ khác (trừ "cây" đơn lẻ)
        if (empty($specificKeywords)) {
            foreach ($keywords as $keyword) {
                $keywordLower = mb_strtolower(trim($keyword));
                // Giữ lại keywords không phải là "cây" đơn lẻ
                // Ưu tiên "hoa giấy", "hoa đào" hơn "cây"
                if ($keywordLower !== 'cây' && mb_strlen($keyword) >= 3) {
                    $specificKeywords[] = $keyword;
                }
            }
        }

        // Nếu vẫn không có keywords cụ thể, trả về mảng rỗng để không tìm kiếm với keywords chung chung
        $searchKeywords = ! empty($specificKeywords) ? array_values(array_unique($specificKeywords)) : [];

        Log::info('Filtered search keywords', ['searchKeywords' => $searchKeywords]);

        $query = Product::query()
            ->active()
            ->with('primaryCategory');

        // Tìm kiếm với logic ưu tiên: keyword đầu tiên PHẢI có trong tên sản phẩm (bắt buộc)
        if (! empty($searchKeywords)) {
            $firstKeyword = $searchKeywords[0];
            // Loại bỏ "cây " và "hoa " ở đầu
            $firstKeywordClean = preg_replace('/^(cây|hoa)\s+/i', '', $firstKeyword);

            // Tìm kiếm: keyword đầu tiên PHẢI có trong tên HOẶC mô tả (bắt buộc)
            // Sau đó sắp xếp theo độ liên quan: tên chứa keyword > mô tả chứa keyword
            $query->where(function ($q) use ($firstKeyword, $firstKeywordClean) {
                // Keyword đầu tiên phải có trong tên sản phẩm hoặc mô tả
                $q->where(function ($subQ) use ($firstKeyword, $firstKeywordClean) {
                    // Tìm trong tên (ưu tiên cao)
                    $subQ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($firstKeyword).'%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($firstKeywordClean).'%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['% '.mb_strtolower($firstKeywordClean).' %'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($firstKeywordClean).' %'])
                        // Hoặc trong mô tả (tựa nghĩa, ưu tiên thấp hơn)
                        ->orWhereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($firstKeyword).'%'])
                        ->orWhereRaw('LOWER(short_description) LIKE ?', ['%'.mb_strtolower($firstKeyword).'%'])
                        ->orWhereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($firstKeywordClean).'%'])
                        ->orWhereRaw('LOWER(short_description) LIKE ?', ['%'.mb_strtolower($firstKeywordClean).'%']);
                });
            });
        } else {
            // Fallback: tìm kiếm với tất cả keywords (ít chính xác hơn)
            $query->where(function ($q) use ($keywords) {
                foreach (array_slice($keywords, 0, 3) as $keyword) {
                    $keywordClean = preg_replace('/^(cây|hoa)\s+/i', '', $keyword);
                    $q->orWhere(function ($subQ) use ($keyword, $keywordClean) {
                        $subQ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($keyword).'%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($keywordClean).'%'])
                            ->orWhereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($keyword).'%'])
                            ->orWhereRaw('LOWER(short_description) LIKE ?', ['%'.mb_strtolower($keyword).'%']);
                    });
                }
            });
        }

        // Sắp xếp theo độ liên quan: ưu tiên sản phẩm đúng từ khóa trước, sau đó mới đến tựa nghĩa
        if (! empty($searchKeywords)) {
            $firstKeyword = $searchKeywords[0];
            $firstKeywordClean = preg_replace('/^(cây|hoa)\s+/i', '', $firstKeyword);

            // Chuẩn bị các điều kiện sắp xếp theo độ ưu tiên
            $orderConditions = [];
            $orderParams = [];

            // Priority 1: Tên sản phẩm bắt đầu bằng keyword chính xác (ví dụ: "Cọ đuôi chồn")
            $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 1';
            $orderParams[] = mb_strtolower($firstKeyword).'%';

            // Priority 2: Tên sản phẩm chứa keyword chính xác như một từ riêng biệt (ví dụ: "Cây cọ đuôi chồn")
            $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 2';
            $orderParams[] = '% '.mb_strtolower($firstKeyword).' %';

            // Priority 3: Tên sản phẩm chứa keyword chính xác (ví dụ: "Cây cọ đuôi chồn phong thủy")
            $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 3';
            $orderParams[] = '%'.mb_strtolower($firstKeyword).'%';

            // Priority 4-6: Tên sản phẩm chứa keyword không có prefix (nếu có)
            if ($firstKeywordClean !== $firstKeyword) {
                // Priority 4: Tên bắt đầu bằng keyword không có prefix
                $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 4';
                $orderParams[] = mb_strtolower($firstKeywordClean).'%';

                // Priority 5: Tên chứa keyword không có prefix như một từ riêng biệt
                $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 5';
                $orderParams[] = '% '.mb_strtolower($firstKeywordClean).' %';

                // Priority 6: Tên chứa keyword không có prefix
                $orderConditions[] = 'WHEN LOWER(name) LIKE LOWER(?) THEN 6';
                $orderParams[] = '%'.mb_strtolower($firstKeywordClean).'%';
            }

            // Priority 7-8: Mô tả ngắn chứa keyword (tựa nghĩa, ưu tiên thấp hơn)
            $orderConditions[] = 'WHEN LOWER(short_description) LIKE LOWER(?) THEN 7';
            $orderParams[] = '%'.mb_strtolower($firstKeyword).'%';

            if ($firstKeywordClean !== $firstKeyword) {
                $orderConditions[] = 'WHEN LOWER(short_description) LIKE LOWER(?) THEN 8';
                $orderParams[] = '%'.mb_strtolower($firstKeywordClean).'%';
            }

            // Priority 9-10: Mô tả dài chứa keyword (tựa nghĩa, ưu tiên thấp nhất)
            $orderConditions[] = 'WHEN LOWER(description) LIKE LOWER(?) THEN 9';
            $orderParams[] = '%'.mb_strtolower($firstKeyword).'%';

            if ($firstKeywordClean !== $firstKeyword) {
                $orderConditions[] = 'WHEN LOWER(description) LIKE LOWER(?) THEN 10';
                $orderParams[] = '%'.mb_strtolower($firstKeywordClean).'%';
            }

            // Priority cuối: Các sản phẩm khác
            $orderConditions[] = 'ELSE 99';

            $orderBySql = 'CASE '.implode(' ', $orderConditions).' END';

            $query->orderByRaw($orderBySql, $orderParams);
        }

        // Thêm sắp xếp phụ theo tên để có kết quả nhất quán
        $query->orderBy('name', 'asc');

        $products = $query->limit(20)->get();

        // Preload images để tránh N+1 query (primaryImage là accessor, không phải relationship)
        Product::preloadImages($products);

        // Format kết quả
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'image' => $product->primaryImage?->url
                    ? asset('clients/assets/img/clothes/'.$product->primaryImage->url)
                    : asset('clients/assets/img/clothes/no-image.webp'),
                'category' => $product->primaryCategory?->name,
                'url' => route('client.product.detail', $product->slug),
            ];
        })->toArray();
    }
}
