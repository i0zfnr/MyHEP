<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class AiProvider
{
    public function name(): string
    {
        if (config('services.gemini.key')) {
            return 'gemini';
        }

        if (config('services.openai.key')) {
            return 'openai';
        }

        return 'deepseek';
    }

    public function enabled(): bool
    {
        return (bool) config("services.{$this->name()}.key");
    }

    public function model(): string
    {
        return (string) config("services.{$this->name()}.model");
    }

    public function ask(string $prompt): string
    {
        return match ($this->name()) {
            'gemini' => $this->askGemini($prompt),
            'openai' => $this->askOpenAi($prompt),
            default => $this->askDeepSeek($prompt),
        };
    }

    public function askWithAttachments(string $prompt, array $attachments): string
    {
        if ($this->name() !== 'gemini' || $attachments === []) return $this->ask($prompt);

        return $this->askGemini($prompt, $attachments);
    }

    public function askJsonWithAttachments(string $prompt, array $attachments): string
    {
        if ($this->name() !== 'gemini' || $attachments === []) return $this->ask($prompt);

        return $this->askGemini($prompt, $attachments, true);
    }

    private function askGemini(string $prompt, array $attachments = [], bool $jsonResponse = false): string
    {
        $url = rtrim((string) config('services.gemini.url'), '/')
            .'/models/'.$this->model().':generateContent';
        $parts = [['text' => $prompt]];
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof UploadedFile) continue;
            $parts[] = ['inlineData' => ['mimeType' => (string) $attachment->getMimeType(), 'data' => base64_encode((string) file_get_contents($attachment->getRealPath()))]];
        }
        $request = Http::acceptJson()
            ->withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
            ->timeout(90)
            ->retry([1500], function (\Throwable $exception): bool {
                if (! $exception instanceof RequestException || ! $exception->response) {
                    return false;
                }

                return in_array($exception->response->status(), [429, 500, 502, 503, 504], true);
            });

        $caBundle = config('services.gemini.ca_bundle');
        if (is_string($caBundle) && $caBundle !== '') {
            if (! is_file($caBundle)) {
                throw new \RuntimeException("The configured Gemini CA bundle does not exist: {$caBundle}");
            }

            $request = $request->withOptions(['verify' => $caBundle]);
        }

        $response = $request->post($url, array_filter([
                'systemInstruction' => ['parts' => [['text' => 'You are a careful student support assistant.']]],
                'contents' => [['role' => 'user', 'parts' => $parts]],
                'generationConfig' => $jsonResponse ? ['responseMimeType' => 'application/json', 'temperature' => 0] : null,
            ]))->throw()->json();

        return trim(collect(data_get($response, 'candidates.0.content.parts', []))
            ->pluck('text')->filter()->implode("\n"));
    }

    private function askOpenAi(string $prompt): string
    {
        $response = Http::withToken((string) config('services.openai.key'))->acceptJson()->timeout(45)
            ->post((string) config('services.openai.url'), [
                'model' => $this->model(),
                'instructions' => 'You are a careful student support assistant.',
                'input' => $prompt,
            ])->throw()->json();

        return trim((string) data_get($response, 'output_text', ''));
    }

    private function askDeepSeek(string $prompt): string
    {
        $response = Http::withToken((string) config('services.deepseek.key'))->acceptJson()->timeout(45)
            ->post((string) config('services.deepseek.url'), [
                'model' => $this->model(),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a careful student support assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ])->throw()->json();

        return trim((string) data_get($response, 'choices.0.message.content', ''));
    }
}
