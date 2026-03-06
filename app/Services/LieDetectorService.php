<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LieDetectorService
{
    public function analyze(array $statements): array
    {
        $numbered = collect($statements)
            ->map(fn($s, $i) => ($i + 1) . '. ' . $s['content'])
            ->implode("\n");

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 400,
                'system'     => $this->systemPrompt(),
                'messages'   => [
                    ['role' => 'user', 'content' => $numbered]
                ],
            ]);

            Log::info('Claude response: ' . $response->body());

            if ($response->failed()) {
                Log::error('Claude API failed: ' . $response->body());
                return $this->fallbackResponse();
            }

            $text = $response->json('content.0.text');
            $text = preg_replace('/```json\s*/', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $text = trim($text);

            $result = json_decode($text, true);

            if (!$result || !isset($result['scores']) || !isset($result['lie_index'])) {
                preg_match('/\{.*\}/s', $text, $matches);
                if (isset($matches[0])) {
                    $result = json_decode($matches[0], true);
                }
                if (!$result) return $this->fallbackResponse();
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    private function systemPrompt(): string
    {
        return <<<'EOT'
You are "Detective Claude" - a dramatic lie detection AI.
Analyze 3 statements (2 truths, 1 lie). Reply with JSON only, no other text.

{
    "scores": [score1, score2, score3],
    "lie_index": 1,
    "reasoning": "Your dramatic explanation"
}

Scores: 0-30 truthful, 31-70 suspicious, 71-100 likely lie.
Be dramatic! Use phrases like "Aha! I caught you!" or "Elementary!"
EOT;
    }

    private function fallbackResponse(): array
    {
        return [
            'scores'    => [33, 33, 34],
            'lie_index' => 3,
            'reasoning' => 'AI unavailable - default guess applied.',
        ];
    }

    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 100,
                'messages'   => [
                    ['role' => 'user', 'content' => 'Say "Claude 4.5 is connected!"']
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => $response->json('content.0.text')];
            }
            return ['success' => false, 'message' => $response->body()];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}