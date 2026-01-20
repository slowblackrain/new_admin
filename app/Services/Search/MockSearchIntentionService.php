<?php

namespace App\Services\Search;

use App\Contracts\SearchIntentionInterface;

class MockSearchIntentionService implements SearchIntentionInterface
{
    public function analyze(string $query): array
    {
        $result = [
            'keywords' => [],
            'filters' => [],
            'sort' => null
        ];

        // 1. Simulate Tokenization (Simple Space Split)
        $tokens = explode(' ', $query);

        foreach ($tokens as $token) {
            // Simulate Entity Extraction
            if (strpos($token, '만원') !== false) {
                // Price Filter
                $price = (int) str_replace(['만원', '이하', '대'], '', $token);
                if ($price > 0) {
                    $result['filters']['price_max'] = $price * 10000;
                }
            } elseif (in_array($token, ['빨간색', '레드', 'red'])) {
                // Color Filter (Assuming DB has logic for this, or just keyword expansion)
                $result['keywords'][] = 'red'; // Add English keyword
                $result['keywords'][] = '빨강';
            } elseif (in_array($token, ['크리스마스', '성탄절'])) {
                // Synonym Expansion
                $result['keywords'][] = '크리스마스';
                $result['keywords'][] = '트리';
                $result['keywords'][] = '산타';
                $result['keywords'][] = 'X-MAS';
            } else {
                // General Keyword
                $result['keywords'][] = $token;
            }
        }

        // Default Sort Fallback
        if (strpos($query, '저렴') !== false || strpos($query, '싼') !== false) {
            $result['sort'] = 'price_asc';
        }

        return $result;
    }
}
