<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageRecognitionService
{
    /**
     * Phân tích ảnh để trích xuất keywords
     *
     * @param  string  $imagePath  Đường dẫn đến file ảnh
     * @return array Mảng các keywords
     */
    public function analyzeImage(string $imagePath): array
    {
        // Ưu tiên sử dụng Gemini Vision API (đã có sẵn trong dự án)
        // Thử lấy API key từ AiAssistantService (đang hoạt động)
        try {
            $aiService = app(\App\Services\AiAssistantService::class);
            $apiKey = config('services.gemini.key');

            // Kiểm tra API key có hợp lệ không
            if (! empty($apiKey) && strlen($apiKey) > 20) {
                return $this->analyzeWithGeminiVision($imagePath, $apiKey);
            }
        } catch (\Exception $e) {
            Log::warning('Gemini Vision API failed, falling back to default keywords', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::warning('Gemini API key not configured or invalid, using default keywords', [
            'api_key_length' => strlen($apiKey ?? ''),
        ]);

        // Option 1: Sử dụng Google Vision API
        if (config('services.google_vision.enabled', false)) {
            return $this->analyzeWithGoogleVision($imagePath);
        }

        // Option 2: Sử dụng AWS Rekognition
        if (config('services.aws_rekognition.enabled', false)) {
            return $this->analyzeWithAWSRekognition($imagePath);
        }

        // Option 3: Sử dụng local AI model
        if (config('services.local_ai.enabled', false)) {
            return $this->analyzeWithLocalAI($imagePath);
        }

        // Fallback: Trả về keywords mặc định
        Log::warning('Image recognition service not configured, using default keywords');

        return $this->getDefaultKeywords();
    }

    /**
     * Phân tích ảnh với Gemini Vision API
     */
    protected function analyzeWithGeminiVision(string $imagePath, string $apiKey): array
    {
        try {
            // Đọc ảnh và encode base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1/models/%s:generateContent?key=%s',
                $model,
                $apiKey
            );

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => 'Bạn là chuyên gia về cây cảnh Việt Nam. Phân tích kỹ ảnh này và xác định CHÍNH XÁC tên loại cây cảnh.

                                QUAN TRỌNG - Trả về CHỈ tên cây cụ thể:
                                1. Tên cây CỤ THỂ nhất (ví dụ: hồng môn, cẩm tú mai, đinh lăng, trầu bà đế vương, kim tiền, lưỡi hổ, phát tài, cọ, chà là, cẩm tú cầu, đa búp đỏ, trầu bà, vạn lộc, v.v.)
                                2. Nếu có hoa, thêm màu hoa (ví dụ: hoa hồng, hoa vàng, hoa đỏ, hoa trắng)
                                3. Loại cây (ví dụ: cây phong thủy, cây nội thất, cây để bàn)

                                Trả về CHỈ các từ khóa tiếng Việt, mỗi từ khóa trên một dòng, KHÔNG giải thích.
                                Ưu tiên tên cây cụ thể trước.

                                Ví dụ nếu là cây hồng môn có hoa hồng:
                                hồng môn
                                cây hồng môn
                                hoa hồng
                                cây phong thủy

                                Ví dụ nếu là cây cẩm tú mai có hoa vàng:
                                cẩm tú mai
                                cây cẩm tú mai
                                hoa vàng
                                cây phong thủy',
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topK' => 20,
                    'topP' => 0.9,
                    'maxOutputTokens' => 200,
                ],
            ];

            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                Log::warning('Gemini Vision API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->getDefaultKeywords();
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                Log::warning('Gemini Vision API returned empty response');

                return $this->getDefaultKeywords();
            }

            // Trích xuất keywords từ response
            $keywords = $this->extractKeywordsFromText($text);

            if (empty($keywords)) {
                Log::warning('No keywords extracted from Gemini response', ['text' => $text]);

                return $this->getDefaultKeywords();
            }

            Log::info('Gemini Vision API extracted keywords', [
                'keywords' => $keywords,
                'original_text' => $text,
            ]);

            return $keywords;
        } catch (\Exception $e) {
            Log::error('Gemini Vision API error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->getDefaultKeywords();
        }
    }

    /**
     * Trích xuất keywords từ text response của Gemini
     */
    protected function extractKeywordsFromText(string $text): array
    {
        // Tách text thành các dòng và lọc
        $lines = preg_split('/[\r\n]+/', $text);
        $keywords = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Loại bỏ số thứ tự, dấu gạch đầu dòng, v.v.
            $line = preg_replace('/^[\d\.\-\*\:\s]+/', '', $line);
            $line = trim($line);

            if (empty($line) || mb_strlen($line) < 2) {
                continue;
            }

            // Loại bỏ các từ không liên quan
            $skipPatterns = [
                '/^(ví dụ|example|v\.v\.|etc|yêu cầu|mô tả|đặc điểm|hình dáng|loại cây|trả về|chỉ|không|và|hoặc|ưu tiên|sau đó|mới đến)$/iu',
                '/^(nếu|nếu là|đây là|trong ảnh|ảnh này|có thể|thường|thường là|quan trọng)$/iu',
            ];

            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            // Loại bỏ các câu giải thích dài
            if (mb_strlen($line) > 50) {
                continue;
            }

            // Lấy tất cả keywords, không chỉ những từ có "cây" (vì có thể là tên cây không có từ "cây")
            $keyword = mb_strtolower($line);
            // Loại bỏ các ký tự đặc biệt không cần thiết nhưng giữ lại dấu cách
            $keyword = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $keyword);
            $keyword = preg_replace('/\s+/', ' ', $keyword);
            $keyword = trim($keyword);

            // Chấp nhận keywords từ 2-50 ký tự
            if (mb_strlen($keyword) >= 2 && mb_strlen($keyword) <= 50) {
                // Ưu tiên keywords có chứa tên cây cụ thể hoặc từ liên quan đến cây
                $plantRelated = [
                    'cây', 'plant', 'tree', 'hoa', 'lá', 'thân', 'bụi', 'leo',
                    'trang trí', 'phong thủy', 'nội thất', 'để bàn', 'sân vườn',
                    'hồng môn', 'cẩm tú', 'đinh lăng', 'trầu bà', 'kim tiền',
                    'lưỡi hổ', 'phát tài', 'cọ', 'chà là', 'vạn lộc', 'đa búp',
                    'trúc', 'tùng', 'đế vương', 'cẩm tú cầu', 'cẩm tú mai',
                ];

                $isPlantRelated = false;
                foreach ($plantRelated as $term) {
                    if (str_contains($keyword, $term)) {
                        $isPlantRelated = true;
                        break;
                    }
                }

                // Chấp nhận nếu là từ liên quan đến cây HOẶC là keyword ngắn (có thể là tên cây cụ thể)
                if ($isPlantRelated || (mb_strlen($keyword) <= 25 && ! preg_match('/^(ví dụ|example|v\.v\.|etc|yêu cầu|mô tả|đặc điểm|hình dáng|loại cây|trả về|chỉ|không|và|hoặc|ưu tiên|sau đó|mới đến|nếu|nếu là|đây là|trong ảnh|ảnh này|có thể|thường|thường là|quan trọng)$/iu', $keyword))) {
                    $keywords[] = $keyword;
                }
            }
        }

        // Nếu không tìm thấy keywords từ pattern, thử tách từ
        if (empty($keywords)) {
            // Tìm các từ có chứa "cây"
            preg_match_all('/\b[\p{L}]*cây[\p{L}]*\b/ui', $text, $matches);
            if (! empty($matches[0])) {
                $keywords = array_map(function ($match) {
                    $match = mb_strtolower(trim($match));
                    $match = preg_replace('/[^\p{L}\p{N}\s]/u', '', $match);

                    return trim($match);
                }, array_unique($matches[0]));
                $keywords = array_filter($keywords, fn ($k) => mb_strlen($k) >= 3 && mb_strlen($k) <= 50);
            }
        }

        // Loại bỏ trùng lặp và sắp xếp theo độ dài (từ ngắn đến dài để ưu tiên tên cây cụ thể)
        $keywords = array_values(array_unique($keywords));
        usort($keywords, function ($a, $b) {
            $lenA = mb_strlen($a);
            $lenB = mb_strlen($b);
            if ($lenA === $lenB) {
                return 0;
            }

            return $lenA > $lenB ? 1 : -1;
        });

        // Giới hạn số lượng keywords (ưu tiên keywords ngắn hơn, cụ thể hơn)
        $keywords = array_slice($keywords, 0, 8);

        Log::info('Extracted keywords from Gemini', [
            'original_text' => $text,
            'keywords' => $keywords,
        ]);

        return ! empty($keywords) ? $keywords : $this->getDefaultKeywords();
    }

    /**
     * Phân tích ảnh với Google Vision API
     */
    protected function analyzeWithGoogleVision(string $imagePath): array
    {
        try {
            // Cần cài đặt: composer require google/cloud-vision
            // Và cấu hình GOOGLE_APPLICATION_CREDENTIALS trong .env

            // $vision = new \Google\Cloud\Vision\V1\ImageAnnotatorClient();
            // $image = file_get_contents($imagePath);
            // $response = $vision->labelDetection($image);
            // $labels = $response->getLabelAnnotations();

            // $keywords = [];
            // foreach ($labels as $label) {
            //     $keywords[] = $label->getDescription();
            // }

            // // Lọc keywords liên quan đến cây cảnh
            // return $this->filterPlantKeywords($keywords);

            Log::info('Google Vision API not implemented yet');

            return $this->getDefaultKeywords();
        } catch (\Exception $e) {
            Log::error('Google Vision API error: '.$e->getMessage());

            return $this->getDefaultKeywords();
        }
    }

    /**
     * Phân tích ảnh với AWS Rekognition
     */
    protected function analyzeWithAWSRekognition(string $imagePath): array
    {
        try {
            // Cần cài đặt: composer require aws/aws-sdk-php
            // Và cấu hình AWS credentials trong .env

            // $rekognition = new \Aws\Rekognition\RekognitionClient([
            //     'version' => 'latest',
            //     'region' => config('services.aws_rekognition.region'),
            // ]);

            // $image = file_get_contents($imagePath);
            // $result = $rekognition->detectLabels([
            //     'Image' => ['Bytes' => $image],
            //     'MaxLabels' => 10,
            //     'MinConfidence' => 70,
            // ]);

            // $keywords = [];
            // foreach ($result['Labels'] as $label) {
            //     $keywords[] = $label['Name'];
            // }

            // return $this->filterPlantKeywords($keywords);

            Log::info('AWS Rekognition not implemented yet');

            return $this->getDefaultKeywords();
        } catch (\Exception $e) {
            Log::error('AWS Rekognition error: '.$e->getMessage());

            return $this->getDefaultKeywords();
        }
    }

    /**
     * Phân tích ảnh với local AI model
     */
    protected function analyzeWithLocalAI(string $imagePath): array
    {
        try {
            // Có thể sử dụng các model như:
            // - TensorFlow Lite
            // - ONNX Runtime
            // - PyTorch Mobile
            // - Custom model trained for plant recognition

            Log::info('Local AI not implemented yet');

            return $this->getDefaultKeywords();
        } catch (\Exception $e) {
            Log::error('Local AI error: '.$e->getMessage());

            return $this->getDefaultKeywords();
        }
    }

    /**
     * Lọc keywords liên quan đến cây cảnh
     */
    protected function filterPlantKeywords(array $keywords): array
    {
        $plantKeywords = [
            'cây', 'cây cảnh', 'cây xanh', 'cây phong thủy', 'cây nội thất',
            'plant', 'tree', 'foliage', 'green', 'indoor plant', 'houseplant',
            'cây để bàn', 'cây trang trí', 'cây decor', 'chậu cây',
        ];

        $filtered = [];
        foreach ($keywords as $keyword) {
            $keywordLower = mb_strtolower($keyword);
            foreach ($plantKeywords as $plantKeyword) {
                if (str_contains($keywordLower, $plantKeyword) ||
                    str_contains($plantKeyword, $keywordLower)) {
                    $filtered[] = $keyword;
                    break;
                }
            }
        }

        return ! empty($filtered) ? $filtered : $this->getDefaultKeywords();
    }

    /**
     * Keywords mặc định khi không có AI service
     */
    protected function getDefaultKeywords(): array
    {
        Log::warning('Using default keywords - Gemini API key not configured or invalid. Please configure GEMINI_API_KEY in .env file.');

        // Trả về mảng rỗng để không tìm kiếm với keywords chung chung
        // Người dùng sẽ thấy thông báo lỗi rõ ràng hơn
        return [];
    }
}
