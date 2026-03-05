<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LieDetectorService
{
    /**
     * Send statements to Claude 4.5 AI for analysis
     * 
     * @param array $statements Array of statements with content
     * @return array AI verdict with scores, guess, and reasoning
     */
    public function analyze(array $statements): array
    {
        // Format statements as numbered list
        $numbered = collect($statements)
            ->map(fn($s, $i) => ($i + 1) . '. ' . $s['content'])
            ->implode("\n");

        try {
            Log::info('Sending to Claude 4.5: ' . $numbered);

            // Call Claude 4.5 API with updated model and features
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                // Using Claude Sonnet 4.5 - best for games
                'model'      => 'claude-sonnet-4-5-20250929',
                
                // Token limit for responses
                'max_tokens' => 400,
                
                // System prompt defines AI behavior
                'system'     => $this->systemPrompt(),
                
                // User message with statements
                'messages'   => [
                    [
                        'role' => 'user',
                        'content' => $numbered
                    ]
                ],
                
                // New Claude 4.5 effort parameter
                'effort'     => 'medium', // 'low', 'medium', or 'high'
                
                // Temperature for creativity (0 = deterministic, 1 = creative)
                'temperature' => 0.7,
            ]);

            // Log response for debugging
            Log::info('Claude 4.5 response: ' . $response->body());

            // Check if API call failed
            if ($response->failed()) {
                Log::error('Claude 4.5 API failed: ' . $response->body());
                return $this->fallbackResponse();
            }

            // Extract text from response
            $text = $response->json('content.0.text');
            
            // Clean the response - remove markdown code blocks if present
            $text = $this->cleanJsonResponse($text);
            
            // Parse JSON from Claude's response
            $result = json_decode($text, true);
            
            // Validate response format
            if (!$result || !isset($result['scores']) || !isset($result['lie_index'])) {
                Log::error('Invalid Claude 4.5 response format', ['response' => $text]);
                
                // Try to extract JSON from text if wrapped in markdown
                $result = $this->extractJsonFromText($text);
                
                if (!$result) {
                    return $this->fallbackResponse();
                }
            }
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Claude 4.5 Service error: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    /**
     * System prompt optimized for Claude 4.5
     */
    private function systemPrompt(): string
    {
        return <<<EOT
You are "Detective Claude 4.5" - the world's most advanced lie detection AI. 
Your task is to analyze three statements where two are truths and one is a lie.

RESPONSE FORMAT:
You MUST respond with valid JSON only. No explanations before or after.
Use this exact format:
{
    "scores": [score1, score2, score3],
    "lie_index": 1, 2, or 3,
    "reasoning": "Your dramatic explanation here"
}

SCORING RULES:
- 0-30: Sounds truthful (consistent, specific details)
- 31-70: Suspicious (vague, evasive, too perfect)
- 71-100: Highly likely lie (contradictory, improbable, rehearsed)

PERSONALITY:
Be dramatic! You're a theatrical detective. Use phrases like:
- "Aha! I've caught you!"
- "Elementary, my dear player..."
- "The evidence is clear..."
- "My lie detector is going CRAZY!"

EXAMPLE:
Input:
1. I have visited Paris
2. I speak fluent French
3. I have a pet dragon

Output:
{
    "scores": [25, 45, 98],
    "lie_index": 3,
    "reasoning": "Aha! Statement 3 sets off my lie detector! While the first two have the ring of truth with their believable details, claiming to have a pet dragon is simply preposterous. Case closed!"
}

Now analyze these statements and return your verdict in JSON format:
EOT;
    }

    /**
     * Clean JSON response from markdown code blocks
     */
    private function cleanJsonResponse(string $text): string
    {
        // Remove ```json and ``` markers
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        
        // Trim whitespace
        return trim($text);
    }

    /**
     * Extract JSON from text if wrapped in other content
     */
    private function extractJsonFromText(string $text): ?array
    {
        // Try to find JSON pattern
        preg_match('/\{.*\}/s', $text, $matches);
        
        if (isset($matches[0])) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['scores']) && isset($json['lie_index'])) {
                return $json;
            }
        }
        
        return null;
    }

    /**
     * Fallback response if AI fails
     */
    private function fallbackResponse(): array
    {
        return [
            'scores' => [33, 33, 34],
            'lie_index' => 3,
            'reasoning' => '🕵️ My lie detector is malfunctioning! Based on probability, I\'ll guess statement 3 is the lie. Please try again!'
        ];
    }

    /**
     * Test function to verify Claude 4.5 is working
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 100,
                'system'     => 'You are a helpful assistant.',
                'messages'   => [
                    ['role' => 'user', 'content' => 'Say "Claude 4.5 is connected!" if you can read this.']
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('content.0.text')
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}