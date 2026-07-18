<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AiHelperController extends Controller
{
    public function index(): View
    {
        return view('admin.ai_helper.index', [
            'aiProvider' => $this->providerName(),
            'aiEnabled' => $this->hasApiKey(),
            'aiModel' => $this->modelName(),
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'template' => ['nullable', 'string', 'max:120'],
            'filters.report_month' => ['nullable', 'date_format:Y-m'],
            'filters.status' => ['nullable', 'string', 'max:40'],
            'filters.matric_no' => ['nullable', 'string', 'max:40'],
        ]);

        if (!$this->hasApiKey()) {
            return response()->json([
                'message' => 'AI API key is not configured on the server.',
            ], 422);
        }

        $prompt = $this->buildPrompt($validated);

        try {
            $answer = $this->providerName() === 'openai'
                ? $this->askOpenAi($prompt)
                : $this->askDeepSeek($prompt);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'AI service could not be reached. Check the API key, model, quota, or network connection.',
            ], 502);
        }

        return response()->json([
            'answer' => $answer,
            'provider' => $this->providerName(),
            'model' => $this->modelName(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function providerName(): string
    {
        if (config('services.openai.key')) {
            return 'openai';
        }

        return 'deepseek';
    }

    private function hasApiKey(): bool
    {
        return (bool) ($this->providerName() === 'openai'
            ? config('services.openai.key')
            : config('services.deepseek.key'));
    }

    private function modelName(): string
    {
        return (string) ($this->providerName() === 'openai'
            ? config('services.openai.model')
            : config('services.deepseek.model'));
    }

    private function buildPrompt(array $validated): string
    {
        $authUser = session('auth_user', []);
        $context = $this->adminContext($validated['filters'] ?? []);

        return implode("\n\n", [
            'You are StudentEdge Admin AI Helper for a Malaysian polytechnic student affairs system.',
            'Answer as an operations assistant. Be concise, factual, and action-oriented. Do not invent records. If data is missing, say what must be checked in the system.',
            'Current admin: ' . ($authUser['name'] ?? 'Admin') . ' / role: ' . ($authUser['admin_role'] ?? 'admin'),
            'Selected template: ' . ($validated['template'] ?? 'custom'),
            'Available system context JSON: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'Admin request: ' . $validated['message'],
        ]);
    }

    private function adminContext(array $filters): array
    {
        $matricNo = trim((string) ($filters['matric_no'] ?? ''));
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        $reportMonth = (string) ($filters['report_month'] ?? now()->format('Y-m'));
        [$year, $month] = array_pad(explode('-', $reportMonth), 2, null);

        $context = [
            'filters' => [
                'report_month' => $reportMonth,
                'status' => $status ?: 'all',
                'matric_no' => $matricNo ?: null,
            ],
            'counts' => [
                'students' => $this->countTable('students'),
                'scholarships' => $this->countTable('scholarships'),
                'offenses' => $this->countTable('offenses'),
                'pending_fine_applications' => $this->countTable('fine_payment_applications', fn ($query) => $query->where('status', 'pending')),
                'pending_vehicle_stickers' => $this->countTable('vehicle_sticker_applications', fn ($query) => $query->where('status', 'pending')),
            ],
            'monthly' => [
                'offenses' => $this->countTable('offenses', function ($query) use ($year, $month) {
                    if ($year && $month) {
                        $query->whereYear('offense_date', (int) $year)
                            ->whereMonth('offense_date', (int) $month);
                    }
                }),
            ],
            'recent' => [],
        ];

        if (Schema::hasTable('offenses')) {
            $query = DB::table('offenses')
                ->join('students', 'students.id', '=', 'offenses.student_id')
                ->select('students.full_name', 'students.matric_no', 'offenses.offense_date', 'offenses.place', 'offenses.status', 'offenses.fine_amount')
                ->orderByDesc('offenses.offense_date')
                ->limit(8);

            if ($matricNo !== '') {
                $query->where('students.matric_no', 'like', "%{$matricNo}%");
            }
            if (in_array($status, ['unpaid', 'applied', 'paid'], true)) {
                $query->where('offenses.status', $status);
            }

            $context['recent']['offenses'] = $query->get()->map(fn ($row) => (array) $row)->all();
        }

        if (Schema::hasTable('scholarships')) {
            $context['recent']['scholarships'] = DB::table('scholarships')
                ->join('students', 'students.id', '=', 'scholarships.student_id')
                ->select('students.full_name', 'students.matric_no', 'scholarships.type', 'scholarships.provider_name', 'scholarships.status', 'scholarships.amount')
                ->when($matricNo !== '', fn ($query) => $query->where('students.matric_no', 'like', "%{$matricNo}%"))
                ->orderByDesc('scholarships.created_at')
                ->limit(8)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return $context;
    }

    private function countTable(string $table, ?callable $scope = null): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);
        if ($scope) {
            $scope($query);
        }

        return (int) $query->count();
    }

    private function askDeepSeek(string $prompt): string
    {
        $response = Http::withToken((string) config('services.deepseek.key'))
            ->acceptJson()
            ->timeout(45)
            ->post((string) config('services.deepseek.url'), [
                'model' => $this->modelName(),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a careful admin operations assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ])
            ->throw()
            ->json();

        return trim((string) data_get($response, 'choices.0.message.content', ''));
    }

    private function askOpenAi(string $prompt): string
    {
        $response = Http::withToken((string) config('services.openai.key'))
            ->acceptJson()
            ->timeout(45)
            ->post((string) config('services.openai.url'), [
                'model' => $this->modelName(),
                'instructions' => 'You are a careful admin operations assistant.',
                'input' => $prompt,
            ])
            ->throw()
            ->json();

        $outputText = trim((string) data_get($response, 'output_text', ''));
        if ($outputText !== '') {
            return $outputText;
        }

        return trim(collect(data_get($response, 'output', []))
            ->flatMap(fn ($item) => data_get($item, 'content', []))
            ->pluck('text')
            ->filter()
            ->implode("\n"));
    }
}
