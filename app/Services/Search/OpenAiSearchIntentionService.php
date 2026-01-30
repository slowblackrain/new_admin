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
            $response = Http::withOptions(['verify' => false])
                ->withToken($apiKey)
                ->timeout(5)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a B2B shopping assistant for Dometopia. Analyze the user's search query and extract search intentions.
                            Return ONLY a JSON object with the following structure:
                            {
                                \"keywords\": [\"list\", \"of\", \"relevant\", \"keywords\", \"expanded\", \"synonyms\"],
                                \"filters\": {
                                    \"price_min\": integer or null (in KRW),
                                    \"price_max\": integer or null (in KRW)
                                },
                                \"sort\": \"price_asc\" | \"price_desc\" | \"popular\" | \"popular_sales\" | \"new\" | null
                            }
                            Rules:
                            1. 'keywords': Extract only product names or core terms. REMOVE price/sort terms (e.g. '1만원대', '저렴한').
                            2. 'price_min'/'price_max': extracting numeric ranges (e.g., '1만원대' -> 10000~19999, '5000원 이상' -> min 5000).
                            3. 'sort': Infer '인기', '베스트' -> 'popular'; '판매순', '많이팔린' -> 'popular_sales'; '신상', '최신' -> 'new'; '저렴한' -> 'price_asc'.
                            4. Example: '1만원대 텀블러' -> keywords:['텀블러'], price_min:10000, price_max:19999.
                            5. Do NOT allow markdown formatting in the response."
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
                // Fallback to Mock Service if API returns error (e.g. Quota Exceeded)
                return (new MockSearchIntentionService())->analyze($query);
            }

        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception: ' . $e->getMessage());
            // Fallback to Mock Service (Smart Rules) if API fails
            return (new MockSearchIntentionService())->analyze($query);
        }

        return $defaultResult;
    }
}
