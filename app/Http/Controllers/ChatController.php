<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function webhook(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'sessionId' => ['required', 'uuid'],
            'timestamp' => ['nullable', 'date'],
            'context.url' => ['nullable', 'string', 'max:2048'],
        ]);

        $message = trim($validated['message']);
        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => 'Vui lòng nhập câu hỏi về sản phẩm hoặc dịch vụ của cửa hàng.',
            ]);
        }

        $response = DB::transaction(function () use ($request, $chatbot, $validated, $message): array {
            $conversation = ChatConversation::query()
                ->where('session_id', $validated['sessionId'])
                ->lockForUpdate()
                ->first();

            if ($conversation && $conversation->user_id !== null && $request->user()
                && (int) $conversation->user_id !== (int) $request->user()->id) {
                abort(409, 'Phiên chat này không thuộc tài khoản hiện tại.');
            }

            if (! $conversation) {
                $pageUrl = $validated['context']['url'] ?? null;
                if ($pageUrl && parse_url($pageUrl, PHP_URL_HOST) !== $request->getHost()) {
                    $pageUrl = null;
                }

                $conversation = ChatConversation::create([
                    'session_id' => $validated['sessionId'],
                    'user_id' => $request->user()?->id,
                    'page_url' => $pageUrl,
                    'last_message_at' => now(),
                ]);
            } elseif ($conversation->user_id === null && $request->user()) {
                $conversation->user_id = $request->user()->id;
            }

            $history = $conversation->messages()
                ->where('sender', 'user')
                ->latest('created_at')
                ->limit(5)
                ->pluck('content')
                ->reverse()
                ->values();

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'user',
                'content' => $message,
                'status' => 'sent',
                'created_at' => now(),
            ]);

            $answer = $chatbot->respond($message, $history);

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'assistant',
                'content' => $answer['response'],
                'status' => 'sent',
                'metadata' => [
                    'products' => $answer['products'] ?? [],
                    'suggestedQuestions' => $answer['suggestedQuestions'] ?? [],
                ],
                'created_at' => now(),
            ]);

            $conversation->last_message_at = now();
            $conversation->save();

            return $answer;
        });

        return response()->json($response);
    }
}
