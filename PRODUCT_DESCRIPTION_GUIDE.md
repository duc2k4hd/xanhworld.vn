# Product Description JSON Structure - Hướng Dẫn Sử Dụng

## 概述 (Tổng Quan)

Cột `description` trong bảng `products` đã được thiết kế để lưu trữ dữ liệu dạng JSON có cấu trúc, thay vì text thuần.

Điều này cho phép:
- ✅ Quản lý nội dung sản phẩm theo section (phần)
- ✅ Hỗ trợ media (ảnh/video) cho mỗi section
- ✅ Mở rộng nội dung mà không cần thay đổi schema database
- ✅ Tương thích ngược với dữ liệu cũ qua migration service

---

## 📋 Cấu Trúc JSON Chuẩn

```json
{
  "sections": [
    {
      "key": "intro",
      "title": "Giới thiệu",
      "content": "<p>Giới thiệu chung về sản phẩm</p>",
      "media": {
        "type": "image",
        "url": "https://example.com/image.jpg"
      }
    },
    {
      "key": "feature",
      "title": "Đặc điểm",
      "content": "<p>Các đặc điểm nổi bật</p>",
      "media": {
        "type": "image",
        "url": "https://example.com/feature.jpg"
      }
    },
    {
      "key": "use",
      "title": "Công dụng",
      "content": "<p>Cách sử dụng sản phẩm</p>",
      "media": {
        "type": "video",
        "url": "https://example.com/video.mp4"
      }
    },
    {
      "key": "care",
      "title": "Chăm sóc",
      "content": "<p>Hướng dẫn chăm sóc và bảo quản</p>",
      "media": null
    }
  ]
}
```

---

## 🔑 Giải Thích Các Trường

| Trường | Kiểu | Bắt Buộc | Mô Tả |
|--------|------|---------|-------|
| `sections` | Array | ✅ | Mảng chứa tất cả sections, tối thiểu 1 phần tử |
| `key` | String | ✅ | Định danh duy nhất của section (lowercase, underscores), dùng để mapping frontend component |
| `title` | String | ✅ | Tiêu đề section, hiển thị cho người dùng |
| `content` | String | ✅ | Nội dung HTML của section |
| `media` | Object/Null | ❌ | Đối tượng media hoặc null nếu không có |
| `media.type` | String | ✅ (khi media có) | Loại media: `image` hoặc `video` |
| `media.url` | String | ✅ (khi media có) | URL của media (phải là URL hợp lệ) |

---

## 📚 Cách Sử Dụng Trong Code

### 1. **Lấy Description của Product**

```php
$product = Product::find($id);

// Lấy toàn bộ description (array)
$description = $product->description;

// Lấy tất cả sections
$sections = $product->getDescriptionSections();
```

### 2. **Lấy Section Cụ Thể**

```php
$product = Product::find($id);

// Lấy section "feature"
$featureSection = $product->getDescriptionSection('feature');

// Result:
// [
//   'key' => 'feature',
//   'title' => 'Đặc điểm',
//   'content' => '<p>...</p>',
//   'media' => [...]
// ]
```

### 3. **Export Description Thành HTML**

```php
$product = Product::find($id);

// Lấy HTML đầy đủ để hiển thị trong frontend
$html = $product->descriptionToHtml();

// Kết quả sẽ là HTML được format sẵn với các section
```

### 4. **Sử Dụng Service Class**

```php
use App\Services\ProductDescriptionService;

$service = app(ProductDescriptionService::class);

// Lấy section từ description array
$section = $service->getSection($description, 'feature');

// Cập nhật section
$updated = $service->updateSection($description, 'feature', [
    'title' => 'Đặc điểm mới',
    'content' => '<p>Nội dung mới</p>'
]);

// Xoá section
$cleaned = $service->removeSection($description, 'feature');

// Export to HTML
$html = $service->toHtml($description);
```

---

## 🗝️ Đặc Tính Hỗ Trợ migration Từ Text Cũ

```php
use App\Services\ProductDescriptionService;

// Convert text cũ thành JSON format
$oldText = "Đây là mô tả sản phẩm cũ dạng text";

$newDescription = ProductDescriptionService::migrateFromText($oldText);

// Result: 
// [
//   'sections' => [
//       [
//           'key' => 'legacy',
//           'title' => 'Mô tả sản phẩm',
//           'content' => 'Đây là mô tả sản phẩm cũ dạng text',
//           'media' => null
//       ]
//   ]
// ]
```

---

## 📝 Validation Rules (FormRequest)

Khi submit description qua API, sử dụng `StoreProductDescriptionRequest`:

```php
namespace App\Http\Requests;

class StoreProductDescriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => 'required|array',
            'description.sections' => 'required|array|min:1',
            'description.sections.*.key' => 'required|string|regex:/^[a-z_]+$/',
            'description.sections.*.title' => 'required|string|min:1|max:255',
            'description.sections.*.content' => 'required|string|min:1',
            'description.sections.*.media' => 'nullable|array',
            'description.sections.*.media.type' => 'required_with:description.sections.*.media|in:image,video',
            'description.sections.*.media.url' => 'required_with:description.sections.*.media|url|min:1',
        ];
    }
}
```

---

## 🔧 API Endpoint Example

### Store Product với Description

```php
// Controller
public function store(StoreProductDescriptionRequest $request)
{
    $product = Product::create([
        'name' => $request->input('name'),
        'description' => $request->input('description'), // Auto-casted by ProductDescriptionCast
        // ... other fields
    ]);

    return response()->json($product);
}
```

### Request Body Example

```json
{
  "name": "iPhone 15 Pro",
  "description": {
    "sections": [
      {
        "key": "intro",
        "title": "Giới thiệu",
        "content": "<p>iPhone 15 Pro là điện thoại thông minh mới nhất từ Apple</p>",
        "media": {
          "type": "image",
          "url": "https://cdn.example.com/iphone-15.jpg"
        }
      },
      {
        "key": "feature",
        "title": "Các tính năng nổi bật",
        "content": "<ul><li>Chip A17 Pro</li><li>Camera 48MP</li></ul>",
        "media": null
      }
    ]
  }
}
```

---

## ⚙️ Custom Cast Behavior

Custom cast `ProductDescriptionCast` tự động:

1. **Validation** - Kiểm tra cấu trúc JSON khi lưu
2. **Transformation** - Chuyển đổi giữa array (PHP) ↔ JSON string (DB)
3. **Error Handling** - Throw exception nếu JSON không hợp lệ

```php
// Cast sẽ throw InvalidArgumentException nếu:
// - Không có 'sections' key
// - Sections rỗng
// - Section thiếu required fields (key, title, content)
// - Media type không phải "image" hoặc "video"
// - URL không hợp lệ khi media có
```

---

## 🗂️ Recommended Section Keys

Các key được recommend để giữ consistency:

- `intro` - Giới thiệu sản phẩm
- `feature` - Đặc điểm, tính năng nổi bật
- `use` - Cách sử dụng
- `care` - Chăm sóc, bảo quản
- `meaning` - Ý nghĩa, lợi ích
- `specification` - Thông số kỹ thuật
- `warranty` - Bảo hành, chính sách

Tuy nhiên, bạn có thể tùy chỉnh key theo nhu cầu.

---

## 🚀 Migration Từ Text Sang JSON

Tạo migration command để batch convert dữ liệu cũ:

```php
// database/seeders/MigrateProductDescriptions.php

use App\Models\Product;
use App\Services\ProductDescriptionService;

// Chạy trong seeder hoặc migration
Product::whereNotNull('description')
    ->where('description', '!=', '')
    ->chunk(100, function ($products) {
        foreach ($products as $product) {
            // Check if already JSON
            if ($this->isJson($product->description)) {
                continue;
            }
            
            // Convert text to JSON
            $newDescription = ProductDescriptionService::migrateFromText($product->description);
            $product->update(['description' => $newDescription]);
        }
    });

private function isJson($string): bool
{
    if (! is_string($string)) return false;
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
```

---

## ✅ Testing

```php
// tests/Feature/ProductDescriptionTest.php

public function test_product_description_cast()
{
    $data = [
        'sections' => [
            [
                'key' => 'intro',
                'title' => 'Test',
                'content' => '<p>Test</p>',
                'media' => null
            ]
        ]
    ];

    $product = Product::create([
        'name' => 'Test Product',
        'description' => $data,
        // ... required fields
    ]);

    $this->assertIsArray($product->description);
    $this->assertEquals('intro', $product->description['sections'][0]['key']);
}
```

---

## 📌 Lưu Ý Quan Trọng

1. ✅ **Timestamps** - `created_at`, `updated_at` tự động được quản lý bởi Eloquent
2. ✅ **Backward Compatibility** - Dữ liệu cũ (text) vẫn có thể được convert sang JSON
3. ✅ **Performance** - JSON columns được indexed tốt trên các Database hiện đại
4. ✅ **Validation** - Custom Cast sẽ throw exception nếu dữ liệu không hợp lệ
5. ✅ **Null Safe** - Nếu description là null, sẽ không crash

---

## 🎯 Tóm Tắt

| Thành phần | Vị trí | Công dụng |
|-----------|--------|----------|
| `Migration` | `database/migrations/2026_02_13_000001_*` | Thay đổi schema (text → json) |
| `ProductDescriptionCast` | `app/Casts/ProductDescriptionCast.php` | Validate & transform JSON |
| `ProductDescriptionService` | `app/Services/ProductDescriptionService.php` | Business logic & helpers |
| `StoreProductDescriptionRequest` | `app/Http/Requests/StoreProductDescriptionRequest.php` | Validation rules |
| `Product Model` | `app/Models/Product.php` | Integration & helpers |

---

## 📞 Hỗ Trợ

Nếu có câu hỏi, kiểm tra:
- Output của `ProductDescriptionService::toHtml()` 
- Exception từ `ProductDescriptionCast`
- Validation errors từ `StoreProductDescriptionRequest`
