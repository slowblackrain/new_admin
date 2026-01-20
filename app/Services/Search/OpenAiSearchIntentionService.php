<?php

namespace App\Services\Search;

use App\Contracts\SearchIntentionInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiSearchIntentionService implements SearchIntentionInterface
{
    public function analyze(string $query): array
    {
        $defaultResult = [
            'keywords' => [$query], // Fallback to original query
            'filters' => [],
            'sort' => null
        ];

        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            Log::warning('OpenAI API Key is missing. Falling back to default search.');
            return $defaultResult;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(5)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a shopping assistant. Analyze the user's search query and extract search intentions.
                            Return ONLY a JSON object with the following structure:
                            {
                                \"keywords\": [\"list\", \"of\", \"relevant\", \"keywords\", \"expanded\", \"synonyms\"],
                                \"filters\": {
                                    \"price_max\": integer or null (in KRW),
                                    \"color\": \"string or null\"
                                },
                                \"sort\": \"price_asc\" | \"price_desc\" | \"regist_date\" | null
                            }
                            For 'keywords', include the original terms and relevant synonyms.
                            For 'price_max', convert terms like '3만원 이하' to integer 30000.
                            For 'sort', infer from terms like '싼거', '저렴한' -> 'price_asc'.
                            Do NOT allow markdown formatting in the response."
                        ],
                        [
                            'role' => 'user',
                            'content' => $query
                        ]
                    ],
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $data = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return array_merge($defaultResult, $data);
                } else {
                    Log::error('OpenAI JSON Parse Error: ' . json_last_error_msg());
                }
            } else {
                Log::error('OpenAI API Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception: ' . $e->getMessage());
        }

        return $defaultResult;
    }
}
