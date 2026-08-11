<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiHelperController extends Controller
{
    public function index(AiProvider $ai): View
    {
        $studentId = (int) session('auth_user.id');

        return view('student.ai_helper.index', [
            'aiEnabled' => $ai->enabled(),
            'aiProvider' => $ai->name(),
            'aiModel' => $ai->model(),
            'aiConversations' => $this->conversationSummaries($studentId),
        ]);
    }

    public function ask(Request $request, AiProvider $ai): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1200'],
            'attachment' => ['prohibited'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        if ($this->requestsImageGeneration($validated['message'])) {
            return response()->json([
                'message' => __('Student AI Helper provides text guidance only. Image generation requests are not supported.'),
            ], 422);
        }
        if (! $ai->enabled()) {
            return response()->json(['message' => __('AI API key is not configured on the server.')], 422);
        }

        $studentId = (int) session('auth_user.id');
        $conversation = null;
        $history = [];
        if (! empty($validated['conversation_id'])) {
            $conversation = $this->ownedConversation((int) $validated['conversation_id'], $studentId);
            if (! $conversation) {
                return response()->json(['message' => __('Conversation not found.')], 404);
            }
            $history = DB::table('student_ai_messages')
                ->where('conversation_id', $conversation->id)
                ->orderByDesc('id')->limit(12)->get(['role', 'content'])
                ->reverse()->values()->map(fn ($row): array => (array) $row)->all();
        }
        $context = $this->studentContext($studentId);
        $prompt = implode("\n\n", [
            'You are StudentEdge Student AI Helper for a Malaysian polytechnic.',
            'Answer only about the signed-in student and general portal guidance. Provide text guidance only; never generate or offer images or other visual assets. Never infer missing records, reveal another person, or treat advice as an official decision. Be concise and use the language used in the question.',
            'Format the answer as clean, readable Markdown for a chat card. Use short headings and bullets only when they improve clarity. Write labels as **Label:** Value. Do not output raw HTML, decorative asterisks, or unnecessary report boilerplate.',
            'Signed-in student context JSON: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Recent conversation messages JSON: '.json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Student question: '.$validated['message'],
        ]);

        try {
            $answer = $ai->ask($prompt);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => __('AI service could not be reached. Check the API key, model, quota, or network connection.')], 502);
        }

        if (Schema::hasTable('student_ai_conversations') && Schema::hasTable('student_ai_messages')) {
            DB::transaction(function () use (&$conversation, $studentId, $validated, $answer, $ai): void {
                $now = now();
                if (! $conversation) {
                    $conversationId = DB::table('student_ai_conversations')->insertGetId([
                        'student_id' => $studentId,
                        'title' => Str::limit(trim($validated['message']), 72, '…'),
                        'last_message_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $conversation = DB::table('student_ai_conversations')->where('id', $conversationId)->first();
                } else {
                    DB::table('student_ai_conversations')->where('id', $conversation->id)->update([
                        'last_message_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('student_ai_messages')->insert([
                    [
                        'conversation_id' => $conversation->id,
                        'role' => 'user',
                        'content' => $validated['message'],
                        'provider' => null,
                        'model' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'conversation_id' => $conversation->id,
                        'role' => 'assistant',
                        'content' => $answer,
                        'provider' => $ai->name(),
                        'model' => $ai->model(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
            });
        }

        return response()->json([
            'answer' => $answer,
            'provider' => $ai->name(),
            'model' => $ai->model(),
            'conversation' => $conversation ? $this->conversationSummary($conversation->id, $studentId) : null,
        ]);
    }

    public function conversation(int $conversation): JsonResponse
    {
        $studentId = (int) session('auth_user.id');
        $owned = $this->ownedConversation($conversation, $studentId);
        if (! $owned) {
            return response()->json(['message' => __('Conversation not found.')], 404);
        }

        DB::table('student_ai_conversations')->where('id', $conversation)->update(['updated_at' => now()]);

        return response()->json([
            'conversation' => $this->conversationSummary($conversation, $studentId),
            'messages' => DB::table('student_ai_messages')->where('conversation_id', $conversation)
                ->orderBy('id')->get(['id', 'role', 'content', 'provider', 'model', 'created_at']),
        ]);
    }

    public function renameConversation(Request $request, int $conversation): JsonResponse
    {
        $studentId = (int) session('auth_user.id');
        if (! $this->ownedConversation($conversation, $studentId)) {
            return response()->json(['message' => __('Conversation not found.')], 404);
        }
        $validated = $request->validate(['title' => ['required', 'string', 'max:120']]);
        DB::table('student_ai_conversations')->where('id', $conversation)->update([
            'title' => trim($validated['title']),
            'updated_at' => now(),
        ]);

        return response()->json(['conversation' => $this->conversationSummary($conversation, $studentId)]);
    }

    public function deleteConversation(int $conversation): JsonResponse
    {
        $studentId = (int) session('auth_user.id');
        $deleted = DB::table('student_ai_conversations')->where('id', $conversation)
            ->where('student_id', $studentId)->delete();

        return $deleted
            ? response()->json(['deleted' => true])
            : response()->json(['message' => __('Conversation not found.')], 404);
    }

    public function deleteAllConversations(): JsonResponse
    {
        DB::table('student_ai_conversations')->where('student_id', (int) session('auth_user.id'))->delete();

        return response()->json(['deleted' => true]);
    }

    private function ownedConversation(int $conversationId, int $studentId): ?object
    {
        if (! Schema::hasTable('student_ai_conversations')) {
            return null;
        }

        return DB::table('student_ai_conversations')->where('id', $conversationId)
            ->where('student_id', $studentId)->first();
    }

    private function conversationSummaries(int $studentId): array
    {
        if (! Schema::hasTable('student_ai_conversations')) {
            return [];
        }

        return DB::table('student_ai_conversations')->where('student_id', $studentId)
            ->orderByDesc('last_message_at')->limit(40)
            ->get(['id', 'title', 'last_message_at', 'updated_at'])
            ->map(fn ($row): array => (array) $row)->all();
    }

    private function conversationSummary(int $conversationId, int $studentId): ?array
    {
        $row = DB::table('student_ai_conversations')->where('id', $conversationId)
            ->where('student_id', $studentId)->first(['id', 'title', 'last_message_at', 'updated_at']);

        return $row ? (array) $row : null;
    }

    private function requestsImageGeneration(string $message): bool
    {
        return preg_match(
            '/\b(?:generate|create|draw|design|make|produce)\s+(?:an?\s+|some\s+)?(?:image|picture|illustration|poster|logo|artwork|graphic)\b|\b(?:jana|hasilkan|cipta|lukis|reka)\s+(?:sebuah\s+)?(?:imej|gambar|ilustrasi|poster|logo|grafik)\b/iu',
            $message
        ) === 1;
    }

    private function studentContext(int $studentId): array
    {
        $context = ['profile' => [], 'scholarships' => [], 'offenses' => [], 'fine_payment_applications' => []];
        if (Schema::hasTable('students')) {
            $context['profile'] = (array) (DB::table('students')->where('id', $studentId)
                ->first(['full_name', 'matric_no', 'program', 'class_name', 'semester', 'academic_session']) ?? []);
        }
        if (Schema::hasTable('scholarships')) {
            $context['scholarships'] = DB::table('scholarships')->where('student_id', $studentId)
                ->latest('created_at')->limit(5)->get(['type', 'provider_name', 'amount', 'status', 'created_at'])
                ->map(fn ($row) => (array) $row)->all();
        }
        if (Schema::hasTable('offenses')) {
            $context['offenses'] = DB::table('offenses')->where('student_id', $studentId)
                ->latest('offense_date')->limit(10)->get(['id', 'offense_date', 'place', 'fine_amount', 'status'])
                ->map(fn ($row) => (array) $row)->all();
        }
        if (Schema::hasTable('fine_payment_applications')) {
            $context['fine_payment_applications'] = DB::table('fine_payment_applications')->where('student_id', $studentId)
                ->latest('created_at')->limit(10)->get(['offense_id', 'status', 'meeting_date', 'created_at'])
                ->map(fn ($row) => (array) $row)->all();
        }

        return $context;
    }
}
