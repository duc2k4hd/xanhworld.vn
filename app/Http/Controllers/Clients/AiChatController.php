<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequest;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AiChatController extends Controller
{
    public function __construct(
        private AiAssistantService $aiAssistantService
    ) {}

    public function __invoke(AiChatRequest $request): JsonResponse
    {
        $ipKey = 'ai-chat:'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 8)) {
            $seconds = RateLimiter::availableIn($ipKey);

            return response()->json([
                'success' => false,
                'message' => "Bạn đang hỏi quá nhanh. Vui lòng thử lại sau {$seconds} giây.",
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);

        try {
            /** @var \App\Models\Account|null $account */
            $account = auth('web')->user();

            $result = $this->aiAssistantService->answer(
                trim((string) $request->validated('question')),
                $account,
                $request->sanitizedHistory()
            );

            return response()->json([
                'success' => true,
                'answer' => $result['answer'],
                'references' => $result['references'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AI chat failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Trợ lý đang quá tải. Bạn vui lòng thử lại sau ít phút.',
            ], 500);
        }
    }
}
