<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
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
            'filters.output_format' => ['nullable', 'in:auto,formal_report,executive_summary,table,csv,json'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($this->requestsImageGeneration($validated['message'])) {
            return response()->json([
                'message' => 'AI Helper generates written reports only. Image generation requests are not supported.',
            ], 422);
        }

        if (!$this->hasApiKey()) {
            return response()->json([
                'message' => 'AI API key is not configured on the server.',
            ], 422);
        }

        $attachment = $request->file('attachment');
        if ($attachment && $this->providerName() !== 'gemini') {
            return response()->json([
                'message' => 'Document and image report generation currently requires the Gemini provider.',
            ], 422);
        }

        $prompt = $this->buildPrompt($validated, $attachment);

        try {
            $answer = match ($this->providerName()) {
                'gemini' => $this->askGemini($prompt, $attachment),
                'openai' => $this->askOpenAi($prompt),
                default => $this->askDeepSeek($prompt),
            };
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
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);
    }

    private function providerName(): string
    {
        if (config('services.gemini.key')) {
            return 'gemini';
        }

        if (config('services.openai.key')) {
            return 'openai';
        }

        return 'deepseek';
    }

    private function hasApiKey(): bool
    {
        return (bool) config("services.{$this->providerName()}.key");
    }

    private function modelName(): string
    {
        return (string) config("services.{$this->providerName()}.model");
    }

    private function buildPrompt(array $validated, ?UploadedFile $attachment = null): string
    {
        $authUser = session('auth_user', []);
        $context = $this->adminContext($validated['filters'] ?? []);

        $outputFormat = data_get($validated, 'filters.output_format', 'auto');
        $formatInstruction = match ($outputFormat) {
            'formal_report' => 'Return a formal report with title, purpose, findings, analysis, recommendations, and conclusion.',
            'executive_summary' => 'Return a concise executive summary with key findings, risks, and recommended actions.',
            'table' => 'Return the report primarily as readable Markdown tables with a short findings summary.',
            'csv' => 'Return valid CSV only, including a header row. Do not wrap it in Markdown fences.',
            'json' => 'Return valid JSON only. Do not wrap it in Markdown fences.',
            default => 'Follow the exact written report format, language, headings, fields, and ordering requested by the administrator. If none is specified, use a clear formal report structure.',
        };

        return implode("\n\n", [
            'You are StudentEdge Admin AI Helper for a Malaysian polytechnic student affairs system.',
            'Generate written reports only. Never generate, design, or offer images, illustrations, posters, logos, or other visual assets. You may inspect an attached image strictly as evidence for a written report.',
            'Answer as an operations assistant. Be factual and action-oriented. Do not invent records. If data is missing, say what must be checked in the system.',
            'Output requirement: '.$formatInstruction,
            'Current admin: ' . ($authUser['name'] ?? 'Admin') . ' / role: ' . ($authUser['admin_role'] ?? 'admin'),
            'Selected template: ' . ($validated['template'] ?? 'custom'),
            'Available system context JSON: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $attachment
                ? 'Attached report source: '.$attachment->getClientOriginalName().'. Inspect it carefully, extract only visible or documented facts, and clearly distinguish attached-source facts from system-context facts.'
                : 'No report source file was attached.',
            'Admin request: ' . $validated['message'],
        ]);
    }

    private function requestsImageGeneration(string $message): bool
    {
        return preg_match(
            '/\b(?:generate|create|draw|design|make|produce)\s+(?:an?\s+|some\s+)?(?:image|picture|illustration|poster|logo|artwork|graphic)\b|\b(?:jana|hasilkan|cipta|lukis|reka)\s+(?:sebuah\s+)?(?:imej|gambar|ilustrasi|poster|logo|grafik)\b/iu',
            $message
        ) === 1;
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

    private function askGemini(string $prompt, ?UploadedFile $attachment = null): string
    {
        $baseUrl = rtrim((string) config('services.gemini.url'), '/');
        $model = $this->modelName();
        $url = "{$baseUrl}/models/{$model}:generateContent";

        $parts = [['text' => $prompt]];
        if ($attachment) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => (string) $attachment->getMimeType(),
                    'data' => base64_encode((string) file_get_contents($attachment->getRealPath())),
                ],
            ];
        }

        $response = Http::acceptJson()
            ->timeout(45)
            ->post($url . '?key=' . urlencode((string) config('services.gemini.key')), [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => 'You are a careful admin operations assistant.'],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => $parts,
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                ],
            ])
            ->throw()
            ->json();

        return trim(collect(data_get($response, 'candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n"));
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
